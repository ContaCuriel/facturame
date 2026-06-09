<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Services\FacturamaService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

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

        $totalPaid = $payments->sum('amount');
        $outstandingBalance = $invoice->total - $totalPaid;
        $nextInstallment = $payments->count() + 1;

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
        $totalPaid = $payments->sum('amount');
        $previousBalance = $invoice->total - $totalPaid;
        
        if ($validated['amount'] > $previousBalance) {
            return back()->with('error', 'El monto no puede ser mayor al saldo pendiente ($' . number_format($previousBalance, 2) . ')');
        }

        $outstandingBalance = $previousBalance - $validated['amount'];
        $installmentNumber = $payments->count() + 1;
        $amountPaid = round($validated['amount'], 2);
        
        $paymentDateFacturama = $validated['payment_date'] . 'T12:00:00';
        $paymentDateDB = $validated['payment_date'] . ' 12:00:00';

        $taxObject = '01'; 
        $taxesNode = null;

        if ($invoice->taxes > 0) {
            $taxObject = '02'; 
            $taxRate = 0.160000; 
            
            $basePaid = round($amountPaid / (1 + $taxRate), 2);
            $taxPaid = round($amountPaid - $basePaid, 2);

            $taxesNode = [
                [
                    'Name' => 'IVA',
                    'Rate' => $taxRate,
                    'Total' => $taxPaid,
                    'Base' => $basePaid,
                    'IsRetention' => false
                ]
            ];
        }

        // CORRECCIÓN 1: Ajuste de variables al Spanglish de Facturama
        $relatedDocument = [
            'Uuid' => $invoice->uuid,
            'Serie' => $invoice->series,
            'Folio' => $invoice->folio,
            'Currency' => 'MXN',
            'ExchangeRate' => 1,
            'PaymentMethod' => 'PPD',
            'PartialityNumber' => (string)$installmentNumber, // <-- Corregido
            'PreviousBalanceAmount' => round($previousBalance, 2),
            'AmountPaid' => $amountPaid,
            'ImpSaldoInsoluto' => round($outstandingBalance, 2), // <-- Corregido
            'TaxObject' => $taxObject,
        ];

        if ($taxesNode) {
            $relatedDocument['Taxes'] = $taxesNode;
        }

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
            // CORRECCIÓN 2: "Complemento" en lugar de "Complement"
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

        $response = $facturama->createInvoice($facturamaData);

        if ($response->failed()) {
            $error = $response->json();
            return back()->with('error', 'Error de Facturama: ' . ($error['message'] ?? json_encode($error)))->withInput();
        }

        $facturaResult = $response->json();
        // Facturama a veces devuelve "Complement" en la respuesta, así que nos aseguramos de cachar ambos
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
}