<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SatRequest;
use App\Models\Gasto;
use App\Services\SatScraperService;
use Illuminate\Support\Facades\Storage;
use Throwable;
use ZipArchive;

class DownloadSatXml extends Command
{
    protected $signature = 'sat:download-xml';
    protected $description = 'FASE 2: Descarga los ZIPs de XML, guarda físicos y enriquece la BD';

    public function handle()
    {
        // Buscamos solo los tickets de XML
        $pendingRequests = SatRequest::whereIn('status', ['pending', 'failed'])
                                     ->where('type', 'xml_gastos')
                                     ->with('company')
                                     ->get();

        $this->info("Encontramos {$pendingRequests->count()} solicitudes XML listas en el SAT.");

        foreach ($pendingRequests as $request) {
            $company = $request->company;
            $this->info("Revisando ticket XML {$request->request_id} de {$company->name}...");

            try {
                $satService = new SatScraperService($company);
                $resultado = $satService->verificarYDescargar($request->request_id);

                if ($resultado['status'] === 'pending') {
                    $this->warn("El SAT sigue empaquetando los XMLs. Intentaremos más tarde.");
                    $request->update(['status' => 'pending']);
                    continue;
                }

                if ($resultado['status'] === 'no_data') {
                    $this->info("El SAT no encontró XMLs vigentes.");
                    $request->update(['status' => 'no_data', 'mensaje_sat' => $resultado['message']]);
                    continue;
                }

                if ($resultado['status'] === 'downloaded') {
                    $this->info("¡ZIP de XMLs descargado! Extrayendo archivos físicos...");
                    
                    foreach ($resultado['files'] as $zipPath) {
                        $this->procesarZipXml($zipPath, $company->id);
                    }

                    $request->update(['status' => 'downloaded', 'mensaje_sat' => 'Archivos XML vinculados exitosamente.']);
                    $this->info("✅ Ticket XML {$request->request_id} completado.");
                }

            } catch (Throwable $e) {
                $this->error("Error al procesar el ticket {$request->request_id}: " . $e->getMessage());
                $request->update(['status' => 'failed', 'mensaje_sat' => $e->getMessage()]);
            }
        }
    }

    private function procesarZipXml(string $zipPath, int $companyId)
    {
        if (!file_exists($zipPath)) return;

        $zip = new ZipArchive;
        if ($zip->open($zipPath) === TRUE) {
            
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $xmlName = $zip->getNameIndex($i);
                
                // Filtramos para leer solo archivos XML
                if (strtolower(pathinfo($xmlName, PATHINFO_EXTENSION)) === 'xml') {
                    $xmlContent = $zip->getFromIndex($i);
                    $this->vincularXmlConBaseDeDatos($xmlContent, $companyId);
                }
            }
            $zip->close();
        }
        unlink($zipPath);
    }

    private function vincularXmlConBaseDeDatos(string $xmlContent, int $companyId)
    {
        try {
            $cleanXml = str_replace(['cfdi:', 'tfd:'], '', $xmlContent);
            $xml = @simplexml_load_string($cleanXml);
            if (!$xml) return;

            $comprobante = $xml->attributes();
            $timbre = $xml->Complemento->TimbreFiscalDigital->attributes();
            $uuid = strtoupper((string) $timbre['UUID']);

            // 1. Guardamos el archivo físico XML en el disco persistente
            $xmlFileName = "gastos/{$companyId}/{$uuid}.xml";
            Storage::disk('local')->put($xmlFileName, $xmlContent);

            // 2. Buscamos el registro que la Fase 1 (Metadatos) ya había creado y lo actualizamos
            Gasto::where('uuid', $uuid)->where('company_id', $companyId)->update([
                'xml_path' => $xmlFileName, // Esto habilitará los conceptos en tu página web
                'metodo_pago' => (string) ($comprobante['MetodoPago'] ?? 'N/A'),
                'forma_pago' => (string) ($comprobante['FormaPago'] ?? 'N/A'),
            ]);

        } catch (Throwable $e) {
            return; // Si el XML está corrupto, seguimos con el siguiente
        }
    }
}