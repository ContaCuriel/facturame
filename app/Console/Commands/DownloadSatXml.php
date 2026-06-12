<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SatRequest;
use App\Models\Gasto;
use App\Models\Company;
use App\Services\SatScraperService;
use Illuminate\Support\Facades\Storage;
use Throwable;
use ZipArchive;

class DownloadSatXml extends Command
{
    protected $signature = 'sat:download-xml';
    protected $description = 'FASE 2: Descarga los ZIPs de XML, guarda físicos y enriquece la BD (Optimizado por RFC)';

    public function handle()
    {
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
                    $this->info("¡ZIP de XMLs descargado! Extrayendo archivos físicos y vinculando...");
                    
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
            // 🛠️ TRUCO MAESTRO: Limpiamos todos los prefijos raros del SAT con expresiones regulares
            $cleanXml = preg_replace('/(<\/?)(cfdi|tfd|nomina12|pago10|pago20):/i', '$1', $xmlContent);
            $xml = @simplexml_load_string($cleanXml);
            
            if (!$xml) {
                $this->warn("❌ Se omitió un XML porque su estructura estaba corrupta.");
                return;
            }

            $comprobante = $xml->attributes();
            
            // Extracción segura del UUID
            $uuid = null;
            if (isset($xml->Complemento->TimbreFiscalDigital)) {
                $timbre = $xml->Complemento->TimbreFiscalDigital->attributes();
                $uuid = strtoupper((string) $timbre['UUID']);
            }
            
            if (!$uuid) {
                $this->warn("❌ No se encontró el UUID dentro del XML.");
                return;
            }

            // Extracción del RFC Receptor para la UNIFICACIÓN
            $rfcReceptor = null;
            if (isset($xml->Receptor)) {
                $receptor = $xml->Receptor->attributes();
                $rfcReceptor = (string) $receptor['Rfc'];
            }

            // Si el XML no trae RFC Receptor, usamos el de la empresa del ticket
            if (!$rfcReceptor) {
                $empresa = Company::find($companyId);
                $rfcReceptor = $empresa->rfc;
            }

            // 1. Guardamos el archivo físico (usando la carpeta del RFC para ahorrar espacio)
            $xmlFileName = "gastos/{$rfcReceptor}/{$uuid}.xml";
            Storage::disk('local')->put($xmlFileName, $xmlContent);

            // 2. UNIFICACIÓN: Buscamos TODAS las empresas en la BD que tengan este RFC
            $companyIds = Company::where('rfc', $rfcReceptor)->pluck('id');

            $actualizados = 0;
            foreach ($companyIds as $cId) {
                // Buscamos el registro de metadatos (Fase 1) y lo actualizamos con la ruta del XML
                $gasto = Gasto::where('uuid', $uuid)->where('company_id', $cId)->first();
                if ($gasto) {
                    $gasto->update([
                        'xml_path' => $xmlFileName,
                        'metodo_pago' => (string) ($comprobante['MetodoPago'] ?? 'N/A'),
                        'forma_pago' => (string) ($comprobante['FormaPago'] ?? 'N/A'),
                    ]);
                    $actualizados++;
                }
            }

            if ($actualizados === 0) {
                $this->warn("⚠️ El UUID {$uuid} bajó del SAT, pero no estaba en la base de datos (Quizá es de un mes que no pedimos metadatos).");
            }

        } catch (Throwable $e) {
            $this->error("❌ Error grave procesando XML: " . $e->getMessage());
            return;
        }
    }
}