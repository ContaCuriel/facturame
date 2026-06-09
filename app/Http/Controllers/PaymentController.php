<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PaymentController extends Controller
{
    use AuthorizesRequests;

    public function index(Invoice $invoice)
    {
        // 1. Verificamos que tengas permiso de ver esta empresa
        $this->authorize('view', $invoice->company);

        // 2. Bloqueo de seguridad: Solo las facturas PPD pueden tener pagos
        if ($invoice->payment_method !== 'PPD') {
            return redirect()->route('invoices.index', ['company_id' => $invoice->company_id])
                             ->with('error', 'Solo las facturas a crédito (PPD) admiten registro de pagos.');
        }

        // 3. Cargamos los pagos que ya existan para esta factura
        $payments = $invoice->payments()->latest()->get();

        // 4. Cálculos matemáticos automáticos
        $totalPaid = $payments->sum('amount');
        $outstandingBalance = $invoice->total - $totalPaid;
        
        // Número de parcialidad que sigue (si hay 2 pagos, el siguiente es el 3)
        $nextInstallment = $payments->count() + 1;

        return view('payments.index', compact('invoice', 'payments', 'totalPaid', 'outstandingBalance', 'nextInstallment'));
    }

    public function store(Request $request, Invoice $invoice)
    {
        // 1. Validación de seguridad
        $this->authorize('update', $invoice->company);

        $validated = $request->validate([
            'payment_date' => 'required|date',
            'payment_form' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
        ]);

        // 2. Cálculos matemáticos de los saldos
        $payments = $invoice->payments()->get();
        $totalPaid = $payments->sum('amount');
        $previousBalance = $invoice->total - $totalPaid;
        
        // Bloqueo: Evitar que paguen más de lo que deben
        if ($validated['amount'] > $previousBalance) {
            return back()->with('error', 'El monto no puede ser mayor al saldo pendiente ($' . number_format($previousBalance, 2) . ')');
        }

        $outstandingBalance = $previousBalance - $validated['amount'];
        $installmentNumber = $payments->count() + 1;

        // 3. Ajuste de fecha (Le agregamos las 12:00:00 hrs por defecto para cumplir con el SAT)
        $paymentDate = $validated['payment_date'] . ' 12:00:00';

        // 4. Guardar en la base de datos local
        Payment::create([
            'invoice_id' => $invoice->id,
            'payment_date' => $paymentDate,
            'payment_form' => $validated['payment_form'],
            'amount' => $validated['amount'],
            'installment_number' => $installmentNumber,
            'previous_balance' => $previousBalance,
            'outstanding_balance' => $outstandingBalance,
            'status' => 'pending', // Lo dejamos como pendiente hasta que programemos Facturama
        ]);

        return back()->with('success', 'Pago #' . $installmentNumber . ' registrado localmente. Saldo actualizado.');
    }
}