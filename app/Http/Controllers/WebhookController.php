<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        // Facturama envía un JSON. Lo logueamos para debuguear al principio.
        Log::info('Webhook de Facturama recibido:', $request->all());

        // Aquí buscas la factura por su ID de Facturama y actualizas el estado
        // $invoice = Invoice::where('facturama_id', $request->Id)->first();
        
        return response()->json(['status' => 'success'], 200);
    }
}