<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SatRequest;
use App\Models\Gasto;
use App\Services\SatScraperService;
use Illuminate\Support\Facades\Storage;
use Throwable;
use ZipArchive; // <-- IMPORTANTE: Agregamos el manejador de ZIPs

class DownloadSatGastos extends Command
{
    protected $signature = 'sat:download-gastos';
    protected $description = 'Verifica el estado de las solicitudes al SAT, descarga los Metadatos y guarda los gastos en la BD';

    public function handle()
    {
        $pendingRequests = SatRequest::whereIn('status', ['pending', 'failed'])
                                     ->where('type', 'gastos')
                                     ->with('company')
                                     ->get();

        $this->info("Encontramos {$pendingRequests->count()} solicitudes listas para revisar en el SAT.");

        foreach ($pendingRequests as $request) {
            $company = $request->company;
            $this->info("Revisando ticket {$request->request_id} de {$company->name}...");

            try {
                $satService = new SatScraperService($company);
                $resultado = $satService->verificarYDescargar($request->request_id);

                if ($resultado['status'] === 'pending') {
                    $this->warn("El SAT aún no termina de armar el paquete. Intentaremos más tarde.");
                    $request->update(['status' => 'pending']);
                    continue;
                }

                if ($resultado['status'] === 'no_data') {
                    $this->info("El SAT reporta que no hubo gastos en estas fechas.");
                    $request->update(['status' => 'no_data', 'mensaje_sat' => $resultado['message']]);
                    continue;
                }

                if ($resultado['status'] === 'downloaded') {
                    $this->info("¡Metadatos descargados con éxito! Extrayendo ZIP y procesando registros...");
                    
                    foreach ($resultado['files'] as $filePath) {
                        $this->procesarMetadatosZip($filePath, $company->id);
                    }

                    $request->update(['status' => 'downloaded', 'mensaje_sat' => 'Metadatos de gastos importados exitosamente.']);
                    $this->info("✅ Ticket {$request->request_id} completado.");
                }

            } catch (Throwable $e) {
                $this->error("Error al procesar el ticket {$request->request_id}: " . $e->getMessage());
                $request->update(['status' => 'failed', 'mensaje_sat' => $e->getMessage()]);
            }
        }
    }

    /**
     * Abre el ZIP del SAT, lee el archivo .txt de adentro y procesa los renglones
     */
    private function procesarMetadatosZip(string $zipPath, int $companyId)
    {
        if (!file_exists($zipPath)) return;

        $zip = new ZipArchive;
        if ($zip->open($zipPath) === TRUE) {
            
            // Recorremos los archivos dentro del ZIP (normalmente solo viene 1 archivo .txt)
            for ($i = 0; $i < $zip->numFiles; $i++) {
                
                // Extraemos el texto crudo directamente desde el ZIP a la memoria
                $content = $zip->getFromIndex($i);
                if (empty($content)) continue;

                $lines = explode("\n", $content);

                foreach ($lines as $line) {
                    // Cortamos con la tilde mágica del SAT
                    $data = explode("~", $line);

                    if (count($data) < 11 || strpos($data[0], 'Uuid') !== false) continue;

                    try {
                        $uuid = strtoupper(trim($data[0]));
                        $rfcEmisor = trim($data[1]);
                        $nombreEmisor = trim($data[2]);
                        $rfcReceptor = trim($data[3]);
                        $fechaEmision = trim($data[6]);
                        $total = (float) trim($data[8]); 
                        $tipoComprobante = trim($data[9]); 
                        $estadoDocumento = trim($data[10]) == '1' ? 'Vigente' : 'Cancelado';

                        if ($estadoDocumento !== 'Vigente') continue;
                        if (!in_array($tipoComprobante, ['I', 'E'])) continue;

                        $totalFinal = $tipoComprobante === 'E' ? -$total : $total;

                        Gasto::updateOrCreate(
                            [
                                'company_id' => $companyId,
                                'uuid' => $uuid,
                            ],
                            [
                                'rfc_emisor' => $rfcEmisor,
                                'nombre_emisor' => $nombreEmisor,
                                'fecha_emision' => $fechaEmision,
                                'subtotal' => $total, 
                                'total' => $totalFinal,
                                'metodo_pago' => 'N/A (Metadato)',
                                'forma_pago' => 'N/A (Metadato)',
                                'estado' => $estadoDocumento,
                                'xml_path' => null 
                            ]
                        );

                    } catch (Throwable $e) {
                        continue; 
                    }
                }
            }
            $zip->close();
        }

        // Borramos el ZIP temporal del servidor
        unlink($zipPath);
    }
}