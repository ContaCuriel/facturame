<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Services\FacturamaService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    use AuthorizesRequests;

    public function index(Invoice $invoice)
    {
        $this->authorize('view', $invoice->company);

        if ($invoice->payment_method !== 'PPD') {
            return redirect()->route('invoices.index', ['company_id' => $invoice->company_id])
                             ->with('error', 'Solo las facturas a crédito (PPD) admiten registro de pagos.');
        }

        $payments = $invoice->payments()->latest()->get();

        // MATEMÁTICAS: Solo sumamos los pagos que NO estén cancelados
        $totalPaid = $payments->where('status', '!=', 'cancelled')->sum('amount');
        $outstandingBalance = $invoice->total - $totalPaid;
        
        $nextInstallment = $payments->where('status', '!=', 'cancelled')->count() + 1;

        return view('payments.index', compact('invoice', 'payments', 'totalPaid', 'outstandingBalance', 'nextInstallment'));
    }

    public function store(Request $request, Invoice $invoice, FacturamaService $facturama)
    {
        $this->authorize('update', $invoice->company);

        $validated = $request->validate([
            'payment_date' => 'required|date',
            'payment_form' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $company = $invoice->company;
        $client = $invoice->client;

        $payments = $invoice->payments()->get();
        $totalPaid = $payments->where('status', '!=', 'cancelled')->sum('amount');
        $previousBalance = $invoice->total - $totalPaid;
        
        if ($validated['amount'] > $previousBalance) {
            return back()->with('error', 'El monto no puede ser mayor al saldo pendiente ($' . number_format($previousBalance, 2) . ')');
        }

        $outstandingBalance = $previousBalance - $validated['amount'];
        $installmentNumber = $payments->where('status', '!=', 'cancelled')->count() + 1;
        $amountPaid = round($validated['amount'], 2);
        
        $paymentDateFacturama = $validated['payment_date'] . 'T12:00:00';
        $paymentDateDB = $validated['payment_date'] . ' 12:00:00';

        $taxObject = '01'; 
        $taxesNode = [];

        // 🧮 REGRESAMOS A LA MATEMÁTICA ESTABLE A PRUEBA DE CENTAVOS
        if ($invoice->taxes > 0) {
            $taxObject = '02'; 
            $taxRate = 0.160000; 
            
            $originalIsr = $invoice->isr_retention ?? $invoice->isr ?? 0;

            if ($originalIsr > 0) {
                // Cálculo exacto forzado para cuando hay ISR
                $proportion = $amountPaid / $invoice->total;
                $taxPaid = round($invoice->taxes * $proportion, 2);
                $isrPaid = round($originalIsr * $proportion, 2);
                
                // Truco maestro: forzar la base para que cuadre matemáticamente
                $basePaid = round($amountPaid - $taxPaid + $isrPaid, 2);
                $isrRate = round($originalIsr / $invoice->subtotal, 6);

                $taxesNode[] = [
                    'Name' => 'IVA',
                    'Rate' => $taxRate,
                    'Total' => $taxPaid,
                    'Base' => $basePaid,
                    'IsRetention' => false
                ];

                $taxesNode[] = [
                    'Name' => 'ISR',
                    'Rate' => $isrRate,
                    'Total' => $isrPaid,
                    'Base' => $basePaid,
                    'IsRetention' => true
                ];

            } else {
                // El código original que te funcionó perfecto para el IVA normal
                $taxPaid = round($amountPaid - ($amountPaid / (1 + $taxRate)), 2);
                $basePaid = round($amountPaid - $taxPaid, 2);

                $taxesNode[] = [
                    'Name' => 'IVA',
                    'Rate' => $taxRate,
                    'Total' => $taxPaid,
                    'Base' => $basePaid,
                    'IsRetention' => false
                ];
            }
        }

        $relatedDocument = [
            'Uuid' => $invoice->uuid,
            'Serie' => $invoice->series,
            'Folio' => $invoice->folio,
            'Currency' => 'MXN',
            'ExchangeRate' => 1,
            'PaymentMethod' => 'PPD',
            'PartialityNumber' => (string)$installmentNumber,
            'PreviousBalanceAmount' => round($previousBalance, 2),
            'AmountPaid' => $amountPaid,
            'ImpSaldoInsoluto' => round($outstandingBalance, 2),
            'TaxObject' => $taxObject,
        ];

        if (!empty($taxesNode)) {
            $relatedDocument['Taxes'] = $taxesNode;
        }

        // Estructura original estable de Facturama
        $facturamaData = [
            'Folio' => (string)$installmentNumber,
            'Serie' => 'REP',
            'CfdiType' => 'P',
            'ExpeditionPlace' => $company->zip_code,
            'Issuer' => [
                'FiscalRegime' => $company->fiscal_regime,
                'Rfc' => $company->rfc,
                'Name' => $company->name,
            ],
            'Receiver' => [
                'Rfc' => $client->rfc,
                'Name' => $client->name,
                'CfdiUse' => 'CP01',
                'FiscalRegime' => $client->fiscal_regime,
                'TaxZipCode' => $client->zip_code,
            ],
            'Complemento' => [
                'Payments' => [
                    [
                        'Date' => $paymentDateFacturama,
                        'PaymentForm' => $validated['payment_form'],
                        'Amount' => $amountPaid,
                        'Currency' => 'MXN',
                        'RelatedDocuments' => [ $relatedDocument ]
                    ]
                ]
            ]
        ];

        if ($company->logo_path) {
            $facturamaData['LogoUrl'] = url(Storage::url($company->logo_path));
        }

        $response = $facturama->createInvoice($facturamaData);

        if ($response->failed()) {
            $error = $response->json();
            return back()->with('error', 'Error de Facturama: ' . ($error['message'] ?? $error['Message'] ?? json_encode($error)))->withInput();
        }

        $facturaResult = $response->json();
        $repUuid = data_get($facturaResult, 'Complement.TaxStamp.Uuid') ?? data_get($facturaResult, 'Complemento.TaxStamp.Uuid');
        $facturamaId = data_get($facturaResult, 'Id');

        Payment::create([
            'invoice_id' => $invoice->id,
            'payment_date' => $paymentDateDB,
            'payment_form' => $validated['payment_form'],
            'amount' => $amountPaid,
            'installment_number' => $installmentNumber,
            'previous_balance' => $previousBalance,
            'outstanding_balance' => $outstandingBalance,
            'status' => 'issued',
            'uuid' => $repUuid,
            'facturama_id' => $facturamaId,
        ]);

        return back()->with('success', '¡Pago Timbrado con Éxito! UUID: ' . $repUuid);
    }

    public function downloadPdf(Payment $payment, FacturamaService $facturama)
    {
        $this->authorize('view', $payment->invoice->company);
        try {
            $pdfBase64 = $facturama->getInvoicePdf($payment->facturama_id);
            $pdfContent = base64_decode($pdfBase64);
            return response($pdfContent, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="REP-' . $payment->id . '.pdf"');
        } catch (\Throwable $e) {
            return back()->with('error', 'No se pudo descargar el PDF: ' . $e->getMessage());
        }
    }

    public function downloadXml(Payment $payment, FacturamaService $facturama)
    {
        $this->authorize('view', $payment->invoice->company);
        try {
            $xmlString = $facturama->getInvoiceXml($payment->facturama_id);
            return response($xmlString, 200)
                ->header('Content-Type', 'text/xml')
                ->header('Content-Disposition', 'attachment; filename="REP-' . $payment->id . '.xml"');
        } catch (\Throwable $e) {
            return back()->with('error', 'No se pudo descargar el XML: ' . $e->getMessage());
        }
    }

    public function sendEmail(Payment $payment, FacturamaService $facturama)
    {
        $this->authorize('view', $payment->invoice->company);
        $email = $payment->invoice->client->email;
        if (!$email) return back()->with('error', 'El cliente no tiene un correo electrónico registrado.');

        try {
            $subject = "Comprobante de Pago REP - " . $payment->invoice->company->name;
            $comments = "Adjunto enviamos su comprobante de pago correspondiente a la factura " . $payment->invoice->series . "-" . $payment->invoice->folio;
            $response = $facturama->sendInvoiceByEmail($payment->facturama_id, $email, $subject, $comments);

            if ($response->failed()) throw new \Exception('Facturama rechazó el envío: ' . $response->body());
            return back()->with('success', '¡Comprobante enviado exitosamente a ' . $email . '!');
        } catch (\Throwable $e) {
            return back()->with('error', 'No se pudo enviar el correo: ' . $e->getMessage());
        }
    }

    public function cancel(Request $request, Payment $payment, FacturamaService $facturama)
    {
        $this->authorize('update', $payment->invoice->company);
        $motive = '02'; 

        $response = $facturama->cancelInvoice($payment->facturama_id, $motive);

        if ($response->failed()) {
            $errorData = $response->json();
            $errorMessage = $errorData['Message'] ?? $errorData['message'] ?? $response->body();
            return back()->with('error', 'Error al cancelar en el SAT: ' . $errorMessage);
        }

        $payment->update(['status' => 'cancelled']);
        return back()->with('success', '¡Pago cancelado exitosamente en el SAT! El saldo de la factura ha sido restaurado.');
    }
}