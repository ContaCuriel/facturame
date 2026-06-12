<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SatRequest;
use App\Models\Gasto;
use App\Services\SatScraperService;
use ZipArchive;
use Illuminate\Support\Facades\Storage;
use Throwable;

class DownloadSatGastos extends Command
{
    protected $signature = 'sat:download-gastos';
    protected $description = 'Verifica el estado de las solicitudes al SAT, descarga los ZIPs y guarda los gastos en la BD';

    public function handle()
    {
        // 1. Buscamos todos los tickets que sigan pendientes
        $pendingRequests = SatRequest::where('status', 'pending')
                                     ->where('type', 'gastos')
                                     ->with('company')
                                     ->get();

        $this->info("Encontramos {$pendingRequests->count()} solicitudes pendientes en el SAT.");

        foreach ($pendingRequests as $request) {
            $company = $request->company;
            $this->info("Revisando ticket {$request->request_id} de {$company->name}...");

            try {
                $satService = new SatScraperService($company);
                $resultado = $satService->verificarYDescargar($request->request_id);

                if ($resultado['status'] === 'pending') {
                    $this->warn("El SAT aún no termina de armar el paquete. Intentaremos más tarde.");
                    continue;
                }

                if ($resultado['status'] === 'no_data') {
                    $this->info("El SAT reporta que no hubo gastos en estas fechas.");
                    $request->update(['status' => 'no_data', 'mensaje_sat' => $resultado['message']]);
                    continue;
                }

                if ($resultado['status'] === 'downloaded') {
                    $this->info("¡ZIP Descargado! Procesando XMLs...");
                    
                    // Procesamos cada ZIP descargado
                    foreach ($resultado['files'] as $zipPath) {
                        $this->procesarZip($zipPath, $company->id);
                    }

                    // Marcamos el ticket como terminado
                    $request->update(['status' => 'downloaded', 'mensaje_sat' => 'Gastos importados exitosamente.']);
                    $this->info("✅ Ticket {$request->request_id} completado.");
                }

            } catch (Throwable $e) {
                $this->error("Error al procesar el ticket {$request->request_id}: " . $e->getMessage());
                $request->update(['status' => 'failed', 'mensaje_sat' => $e->getMessage()]);
            }
        }
    }

    /**
     * Extrae el ZIP y lee los XML
     */
    private function procesarZip(string $zipPath, int $companyId)
    {
        $zip = new ZipArchive;
        if ($zip->open($zipPath) === TRUE) {
            
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $xmlName = $zip->getNameIndex($i);
                
                // Solo nos interesan los archivos XML
                if (pathinfo($xmlName, PATHINFO_EXTENSION) === 'xml') {
                    $xmlContent = $zip->getFromIndex($i);
                    $this->guardarGastoDesdeXml($xmlContent, $companyId);
                }
            }
            $zip->close();
            
            // Borramos el ZIP temporal después de extraer todo
            unlink($zipPath);
        }
    }

    /**
     * Extrae los datos vitales del XML y los guarda en la base de datos y en el disco
     */
    private function guardarGastoDesdeXml(string $xmlContent, int $companyId)
    {
        try {
            // Quitamos prefijos raros de los nodos del SAT para facilitar la lectura
            $cleanXml = str_replace(['cfdi:', 'tfd:'], '', $xmlContent);
            $xml = simplexml_load_string($cleanXml);

            if (!$xml) return;

            $comprobante = $xml->attributes();
            $emisor = $xml->Emisor->attributes();
            $timbre = $xml->Complemento->TimbreFiscalDigital->attributes();

            $uuid = (string) $timbre['UUID'];
            $total = (float) $comprobante['Total'];
            $subtotal = (float) $comprobante['SubTotal'];
            $rfcEmisor = (string) $emisor['Rfc'];
            $nombreEmisor = (string) $emisor['Nombre'];
            
            $tipoComprobante = (string) $comprobante['TipoDeComprobante'];
            if (!in_array($tipoComprobante, ['I', 'E'])) return;

            // ✅ GUARDAMOS EL ARCHIVO FÍSICO EN EL SERVIDOR
            $xmlFileName = "gastos/{$companyId}/{$uuid}.xml";
            Storage::disk('local')->put($xmlFileName, $xmlContent);

            // Guardamos en la base de datos
            Gasto::updateOrCreate(
                [
                    'company_id' => $companyId,
                    'uuid' => $uuid,
                ],
                [
                    'rfc_emisor' => $rfcEmisor,
                    'nombre_emisor' => $nombreEmisor,
                    'fecha_emision' => (string) $comprobante['Fecha'],
                    'subtotal' => $subtotal,
                    'total' => $tipoComprobante === 'E' ? -$total : $total,
                    'metodo_pago' => (string) ($comprobante['MetodoPago'] ?? 'N/A'),
                    'forma_pago' => (string) ($comprobante['FormaPago'] ?? 'N/A'),
                    'estado' => 'Vigente',
                    'xml_path' => $xmlFileName, // Vinculamos el archivo físico a la BD
                ]
            );

        } catch (Throwable $e) {
            return; // Si falla un XML, lo ignoramos y seguimos con el siguiente
        }
    }
}