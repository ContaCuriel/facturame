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
        // Aquí programaremos el timbrado del REP más adelante. 
        // Por ahora lo dejamos preparado.
    }
}