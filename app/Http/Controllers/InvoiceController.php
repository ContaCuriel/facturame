<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Client;
use App\Models\Invoice;
use App\Services\FacturamaService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use Barryvdh\DomPDF\Facade\Pdf;
use Throwable;
use DOMDocument;
use DOMXPath;

class InvoiceController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $request->validate(['company_id' => 'required|exists:companies,id']);
        $company = Company::findOrFail($request->query('company_id'));
        $this->authorize('view', $company);

        $invoices = Invoice::where('company_id', $company->id)->latest()->get();

        return view('invoices.index', compact('company', 'invoices'));
    }

    public function create(Request $request)
    {
        $request->validate(['company_id' => 'required|exists:companies,id']);
        $company = Company::findOrFail($request->query('company_id'));
        $this->authorize('view', $company);

        return view('invoices.create', [
            'company' => $company,
            'paymentForms' => config('sat.payment_forms'),
            'paymentMethods' => config('sat.payment_methods'),
            'cfdiUses' => config('sat.cfdi_uses'),
        ]);
    }

    public function store(Request $request, FacturamaService $facturama)
    {
        try {
            $validated = $request->validate([
                'company_id' => 'required|exists:companies,id',
                'client_id' => 'required|exists:clients,id',
                'items' => 'required|json',
                'totals' => 'required|json',
                'cfdi_use' => 'required|string',
                'payment_form' => 'required|string',
                'payment_method' => 'required|string',
                'observations' => 'nullable|string',
                'invoice_email' => 'nullable|email',
            ]);
            
            $company = Company::findOrFail($validated['company_id']);
            $client = Client::findOrFail($validated['client_id']);
            $this->authorize('update', $company);

            $items = json_decode($validated['items'], true);
            $totals = json_decode($validated['totals'], true);
            
            $nextFolio = $company->next_folio_number;

            $facturamaData = [
                'Folio' => (string)$nextFolio, 'Serie' => 'F', 'CfdiType' => 'I',
                'PaymentForm' => $validated['payment_form'], 'PaymentMethod' => $validated['payment_method'],
                'ExpeditionPlace' => $company->zip_code,
                'Issuer' => [ 'FiscalRegime' => $company->fiscal_regime, 'Rfc' => $company->rfc, 'Name' => $company->name, ],
                'Receiver' => [
                    'Rfc' => $client->rfc, 'Name' => $client->name, 'CfdiUse' => $validated['cfdi_use'],
                    'FiscalRegime' => $client->fiscal_regime, 'TaxZipCode' => $client->zip_code,
                ],
                'Items' => array_map(function ($item) {
                    $concept = [
                        'ProductCode' => $item['sat_product_key'], 'Description' => $item['description'],
                        'UnitCode' => $item['sat_unit_key'], 'Quantity' => $item['quantity'], 'UnitPrice' => $item['price'],
                        'Subtotal' => $item['total'], 'TaxObject' => $item['isTaxable'] ? '02' : '01',
                    ];
                    if (!empty($item['student'])) {
                        $concept['Complement'] = [
                            'EducationalInstitution' => [
                                'AutRvoe' => $item['student']['aut_rvoe'],
                                'Curp' => $item['student']['curp'],
                                'EducationLevel' => $item['student']['education_level'],
                                'StudentsName' => $item['student']['name'],
                            ]
                        ];
                    }
                    $itemTotal = $item['total'];
                    if ($item['isTaxable'] && !empty($item['taxes'])) {
                        $concept['Taxes'] = [];
                        foreach ($item['taxes'] as $tax) {
                            $taxAmount = round($item['total'] * $tax['rate'], 2);
                            $concept['Taxes'][] = [
                                'Total' => $taxAmount, 'Name' => $tax['name'], 'Base' => $item['total'],
                                'Rate' => $tax['rate'], 'IsRetention' => $tax['type'] === 'Retencion',
                            ];
                            if ($tax['type'] === 'Traslado') { $itemTotal += $taxAmount; } 
                            else { $itemTotal -= $taxAmount; }
                        }
                    }
                    $concept['Total'] = $itemTotal;
                    return $concept;
                }, $items),
            ];
            
            if ($company->logo_path) {
                // Genera la URL pública completa usando la APP_URL de tu .env
                $facturamaData['LogoUrl'] = url(Storage::url($company->logo_path));
            }


            if (!empty($validated['invoice_email'])) {
                $facturamaData['Receiver']['Email'] = $validated['invoice_email'];
            }
            if (!empty($validated['observations'])) {
                $facturamaData['Observations'] = $validated['observations'];
            }
            
            $response = $facturama->createInvoice($facturamaData);

            if ($response->failed()) {
                $error = $response->json();
                $errorMessage = 'Error de Facturama: ' . ($error['message'] ?? json_encode($error));
                return back()->with('error', $errorMessage)->withInput();
            }

            $facturaResult = $response->json();
            $invoiceUuid = data_get($facturaResult, 'Complement.TaxStamp.Uuid');
            $facturamaId = data_get($facturaResult, 'Id');

            if (!$invoiceUuid || !$facturamaId) {
                Log::error('Respuesta incompleta de Facturama', $facturamaResult);
                return back()->with('error', 'Factura timbrada, pero la respuesta de Facturama fue incompleta.');
            }

            $company->increment('next_folio_number');

            Invoice::create([
                'facturama_id' => $facturamaId, 'uuid' => $invoiceUuid,
                'company_id' => $company->id, 'client_id' => $client->id,
                'folio' => $nextFolio, 'series' => $facturamaData['Serie'],
                'subtotal' => $totals['subtotal'],
                'taxes' => $totals['total_traslados'] - $totals['total_retenciones'],
                'total' => $totals['total'], 'status' => 'issued', 'items' => $items,
            ]);

            return redirect()->route('invoices.index', ['company_id' => $company->id])
                             ->with('success', '¡Factura timbrada exitosamente! UUID: ' . $invoiceUuid);
        } catch (Throwable $e) {
            Log::error('Error fatal al facturar: ' . $e->getMessage());
            return back()->with('error', 'Error inesperado del servidor: ' . $e->getMessage())->withInput();
        }
    }
    public function downloadPdf(Invoice $invoice, FacturamaService $facturama)
{
    $this->authorize('view', $invoice->company);

    // 1. Pedimos directamente el 'pdf' nativo de Facturama
    $response = $facturama->getInvoiceFile($invoice->facturama_id, 'pdf');

    if ($response->failed()) {
        return back()->with('error', "No se pudo obtener el PDF de Facturama.");
    }

    $fileData = $response->json();
    $base64Content = data_get($fileData, 'Content');

    if (!$base64Content) {
        return back()->with('error', 'No se encontró el contenido del PDF en la respuesta.');
    }

    // 2. Decodificamos y enviamos al navegador con el nombre correcto
    return response(base64_decode($base64Content), 200, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'attachment; filename="' . $invoice->uuid . '.pdf"',
    ]);
}


/**
 * Extrae los datos de la factura del HTML de Facturama usando DOMXPath.
 *
 * @param string $html
 * @param \App\Models\Invoice $invoice
 * @return array
 */
private function extractDataFromHtml(string $html, Invoice $invoice): array
{
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
    libxml_clear_errors();
    $xpath = new DOMXPath($dom);
    $data = [];

    $cleanText = function ($nodeValue) {
        return trim(preg_replace('/\s+/', ' ', $nodeValue));
    };

    // --- Datos Generales ---
    $data['folio_fiscal'] = $cleanText($xpath->query("//p[contains(., 'Folio Fiscal:')]/text()[normalize-space()]")->item(0)->nodeValue ?? 'No encontrado');
    $data['uso_cfdi'] = $cleanText($xpath->query("//b[contains(text(), 'Uso del CFDI:')]/following-sibling::text()[1]")->item(0)->nodeValue ?? 'No encontrado');
    $data['forma_pago'] = $cleanText($xpath->query("//b[contains(text(), 'Forma de Pago:')]/following-sibling::text()[normalize-space()]")->item(0)->nodeValue ?? 'No encontrado');
    $data['metodo_pago'] = $cleanText($xpath->query("//b[contains(text(), 'Método de Pago:')]/following-sibling::text()[normalize-space()]")->item(0)->nodeValue ?? 'No encontrado');

    // --- Emisor y Receptor ---
    $emitterP = $xpath->query("//p[contains(., 'Emisor:')]")->item(0);
    if ($emitterP) {
        $emitterLines = explode('<br>', nl2br($dom->saveHTML($emitterP)));
        $data['emisor_nombre'] = trim(strip_tags($emitterLines[1] ?? ''));
        $data['emisor_rfc'] = trim(strip_tags($emitterLines[2] ?? ''));
    }
    $emitterDetailsP = $xpath->query("//p[contains(., 'Lugar de Expedición:')]")->item(0);
    if ($emitterDetailsP) {
        $data['lugar_expedicion'] = trim(str_replace('Lugar de Expedición:', '', $xpath->query(".//*[contains(text(), 'Lugar de Expedición:')]", $emitterDetailsP)->item(0)->nextSibling->nodeValue));
        $data['regimen_fiscal_emisor'] = trim(str_replace('Régimen Fiscal:', '', $xpath->query(".//*[contains(text(), 'Régimen Fiscal:')]", $emitterDetailsP)->item(0)->nextSibling->nodeValue));
    }
    $receptorP = $xpath->query("//p[b/span[contains(text(), 'Receptor:')]]")->item(0);
    if ($receptorP) {
        $receptorLines = explode('<br>', nl2br($dom->saveHTML($receptorP)));
        $data['receptor_nombre'] = trim(strip_tags($receptorLines[1] ?? ''));
        $data['receptor_rfc'] = trim(strip_tags($receptorLines[2] ?? ''));
        $data['receptor_cp'] = trim(str_replace('Código postal:', '', strip_tags($receptorLines[4] ?? '')));
    }
    
    // --- Conceptos ---
    $data['conceptos'] = [];
    $rows = $xpath->query("//table[.//b[contains(text(), 'Producto')]]/tr");
    foreach ($rows as $index => $row) {
        if ($index === 0) continue;
        $cols = $row->getElementsByTagName('td');
        if ($cols->length > 1 && !$cols->item(0)->getAttribute('colspan')) {
            // ✅ AJUSTE PARA CONSERVAR HTML EN LA COLUMNA 'CONCEPTO'
            $conceptoNode = $cols->item(3);
            $conceptoHtml = '';
            if($conceptoNode) {
                foreach ($conceptoNode->childNodes as $child) {
                    $conceptoHtml .= $dom->saveHTML($child);
                }
            }
            // Limpiamos la etiqueta <p> externa si existe
            $conceptoHtml = preg_replace('/^<p>|<\/p>$/i', '', trim($conceptoHtml));

            $data['conceptos'][] = [
                'producto' => $cleanText($cols->item(0)->nodeValue),
                'cantidad' => $cleanText($cols->item(1)->nodeValue),
                'unidad'   => $cleanText($cols->item(2)->nodeValue),
                'concepto' => $conceptoHtml, // Se guarda el HTML para conservar el <br>
                'precio_u' => $cleanText($cols->item(4)->nodeValue),
                'importe'  => $cleanText($cols->item(5)->nodeValue),
            ];
        }
    }

    // --- Complemento Educativo ---
    $complementoTd = $xpath->query("//td[@colspan='10' and contains(., 'Complemento educativo:')]")->item(0);
    if ($complementoTd) {
        $innerHtml = $dom->saveHTML($complementoTd);
        $normalizedHtml = preg_replace('/<br\s*\/?>/i', '|||', $innerHtml);
        $rawText = trim(strip_tags($normalizedHtml, '<strong>'));
        $lines = explode('|||', $rawText);
        $complementoData = [];
        foreach ($lines as $line) {
            $cleanLine = trim(strip_tags($line));
            if (str_contains($cleanLine, 'Nombre del alumno:')) $complementoData['nombre_alumno'] = trim(str_replace('Nombre del alumno:', '', $cleanLine));
            else if (str_contains($cleanLine, 'CURP:')) $complementoData['curp'] = trim(str_replace('CURP:', '', $cleanLine));
            else if (str_contains($cleanLine, 'Nivel Educativo:')) $complementoData['nivel'] = trim(str_replace('Nivel Educativo:', '', $cleanLine));
            else if (str_contains($cleanLine, 'Clave de Autorización:')) $complementoData['aut'] = trim(str_replace('Clave de Autorización:', '', $cleanLine));
        }
        if (!empty($complementoData)) $data['complemento_educativo'] = $complementoData;
    }

    // --- Totales y Sellos ---
    $data['subtotal'] = $cleanText($xpath->query("//td[contains(., 'Subtotal:')]/following-sibling::td[1]")->item(0)->nodeValue ?? '0.00');
    $data['total'] = $cleanText($xpath->query("//b[contains(., 'Total:')]/parent::td/following-sibling::td[1]//b")->item(0)->nodeValue ?? '0.00');
    $data['sello_cfdi'] = $cleanText($xpath->query("//b[contains(text(), 'Sello Digital del CFDI')]/following-sibling::p[1]")->item(0)->nodeValue ?? '');
    $data['sello_sat'] = $cleanText($xpath->query("//b[contains(text(), 'Sello Digital del SAT')]/following-sibling::p[1]")->item(0)->nodeValue ?? '');
    $data['cadena_original'] = $cleanText($xpath->query("//b[contains(text(), 'Cadena Original del complemento')]/following-sibling::p[1]")->item(0)->nodeValue ?? '');
    
    // --- Logo ---
    if ($invoice->company->logo_path && Storage::disk('public')->exists($invoice->company->logo_path)) {
        $logoPath = Storage::disk('public')->path($invoice->company->logo_path);
        $logoData = base64_encode(file_get_contents($logoPath));
        $data['logo_base64'] = 'data:' . mime_content_type($logoPath) . ';base64,' . $logoData;
    } else {
        $data['logo_base64'] = null;
    }
$qrNode = $xpath->query("//img[starts-with(@src, 'data:image/png;base64,')]")->item(0);
if ($qrNode) {
    // Si encontramos el nodo de la imagen del QR, guardamos su fuente completa
    $data['qr_code_base64'] = $qrNode->getAttribute('src');
} else {
    $data['qr_code_base64'] = null;
}

return $data;
}    /**
     * Descarga el archivo XML de una factura.
     */
    public function downloadXml(Invoice $invoice, FacturamaService $facturama)
    {
        $this->authorize('view', $invoice->company);
        
        $response = $facturama->getInvoiceFile($invoice->facturama_id, 'xml');

        if ($response->failed()) {
            $errorBody = $response->body();
            $statusCode = $response->status();
            return back()->with('error', "No se pudo descargar el XML. Status: {$statusCode}. Respuesta: {$errorBody}");
        }

        // ✅ --- CORRECCIÓN AQUÍ --- ✅
        // Decodificamos el contenido Base64 antes de enviarlo.
        $fileData = $response->json();
        $base64Content = data_get($fileData, 'Content');

        if (!$base64Content) {
            return back()->with('error', 'No se encontró el contenido del XML en la respuesta de Facturama.');
        }

        return response(base64_decode($base64Content), 200, [
            'Content-Type' => 'application/xml',
            'Content-Disposition' => 'attachment; filename="' . $invoice->uuid . '.xml"',
        ]);
    }

    public function cancel(Request $request, Invoice $invoice, FacturamaService $facturama)
{
    $this->authorize('update', $invoice->company);

    $validated = $request->validate([
        'motive' => 'required|in:01,02,03,04',
        'replacement_uuid' => 'required_if:motive,01|nullable|uuid',
    ]);

    // La llamada a la API no cambia
    $response = $facturama->cancelInvoice($invoice->facturama_id, $validated['motive'], $validated['replacement_uuid'] ?? null);

    // ✅ INICIO DEL BLOQUE DE MANEJO DE ERRORES MEJORADO
    if ($response->failed()) {
        // Obtenemos los detalles del error para mostrarlos
        $errorData = $response->json();
        $statusCode = $response->status();
        $rawBody = $response->body();

        // Construimos un mensaje de error descriptivo
        $errorMessage = "Código de estado: {$statusCode}.";

        // Priorizamos el mensaje de error estructurado de Facturama
        if (isset($errorData['Message'])) {
            $errorMessage .= " Mensaje: " . $errorData['Message'];
        } 
        // A veces el campo viene en minúsculas
        elseif (isset($errorData['message'])) {
            $errorMessage .= " Mensaje: " . $errorData['message'];
        } 
        // Si no hay JSON, mostramos el cuerpo de la respuesta en crudo
        elseif (!empty($rawBody)) {
            $errorMessage .= " Respuesta del servidor: " . $rawBody;
        }
        
        return back()->with('error', 'Error al cancelar en Facturama: ' . $errorMessage);
    }
    // ✅ FIN DEL BLOQUE DE MANEJO DE ERRORES

    $invoice->update(['status' => 'cancelled']);

    return redirect()->route('invoices.index', ['company_id' => $invoice->company_id])
                     ->with('success', 'Solicitud de cancelación enviada correctamente.');
}

    public function sendByEmail(Request $request, Invoice $invoice, FacturamaService $facturama)
{
    $this->authorize('view', $invoice->company);

    $validated = $request->validate([
        'email' => 'required|email',
    ]);

    $response = $facturama->sendInvoiceByEmail($invoice->facturama_id, $validated['email']);

    if ($response->failed() || data_get($response->json(), 'success') === false) {
        $error = $response->json();
        return back()->with('error', 'Error al enviar el correo: ' . ($error['message'] ?? $error['msj'] ?? 'Respuesta no exitosa.'));
    }

    return back()->with('success', '¡Correo enviado exitosamente a ' . $validated['email'] . '!');
}


}
