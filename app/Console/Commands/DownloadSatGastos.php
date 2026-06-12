<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SatRequest;
use App\Models\Gasto;
use App\Services\SatScraperService;
use Illuminate\Support\Facades\Storage;
use Throwable;

class DownloadSatGastos extends Command
{
    protected $signature = 'sat:download-gastos';
    protected $description = 'Verifica el estado de las solicitudes al SAT, descarga los Metadatos y guarda los gastos en la BD';

    public function handle()
    {
        // 1. Buscamos tickets que estén en 'pending' o 'failed' (para reintentar el de Flora)
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
                    $request->update(['status' => 'pending']); // Lo regresamos a pendiente si el SAT sigue procesando
                    continue;
                }

                if ($resultado['status'] === 'no_data') {
                    $this->info("El SAT reporta que no hubo gastos en estas fechas.");
                    $request->update(['status' => 'no_data', 'mensaje_sat' => $resultado['message']]);
                    continue;
                }

                if ($resultado['status'] === 'downloaded') {
                    $this->info("¡Metadatos descargados con éxito! Procesando registros...");
                    
                    // Procesamos cada archivo de texto plano descargado
                    foreach ($resultado['files'] as $filePath) {
                        $this->procesarMetadatosTxt($filePath, $company->id);
                    }

                    // Marcamos el ticket como terminado exitosamente
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
     * Procesa el archivo de Metadatos del SAT (.txt) renglón por renglón
     */
    private function procesarMetadatosTxt(string $filePath, int $companyId)
    {
        if (!file_exists($filePath)) return;

        $content = file_get_contents($filePath);
        $lines = explode("\n", $content);

        foreach ($lines as $line) {
            // 🛠️ EL SECRETO DEL SAT: Los metadatos vienen separados por tilde (~) no por pipe (|)
            $data = explode("~", $line);

            // Saltamos si no tiene las columnas completas o si es el renglón de encabezados
            if (count($data) < 11 || strpos($data[0], 'Uuid') !== false) continue;

            try {
                // Mapeo exacto de las columnas del SAT para Metadatos
                $uuid = strtoupper(trim($data[0]));
                $rfcEmisor = trim($data[1]);
                $nombreEmisor = trim($data[2]);
                $rfcReceptor = trim($data[3]);
                $fechaEmision = trim($data[6]);
                $total = (float) trim($data[8]); // Columna 8 es el Monto
                $tipoComprobante = trim($data[9]); // Columna 9 es I (Ingreso), E (Egreso), etc.
                
                // Columna 10 es el Estatus (1=Vigente, 0=Cancelado)
                $estadoDocumento = trim($data[10]) == '1' ? 'Vigente' : 'Cancelado';

                // Filtros vitales: Solo queremos facturas Vigentes y que sean de tipo Ingreso o Egreso
                if ($estadoDocumento !== 'Vigente') continue;
                if (!in_array($tipoComprobante, ['I', 'E'])) continue;

                $totalFinal = $tipoComprobante === 'E' ? -$total : $total;

                // Guardamos en la base de datos de forma limpia
                Gasto::updateOrCreate(
                    [
                        'company_id' => $companyId,
                        'uuid' => $uuid,
                    ],
                    [
                        'rfc_emisor' => $rfcEmisor,
                        'nombre_emisor' => $nombreEmisor,
                        'fecha_emision' => $fechaEmision,
                        'subtotal' => $total, // Metadatos solo dan Total, lo usamos como base
                        'total' => $totalFinal,
                        'metodo_pago' => 'N/A (Metadato)',
                        'forma_pago' => 'N/A (Metadato)',
                        'estado' => $estadoDocumento,
                        'xml_path' => null 
                    ]
                );

            } catch (Throwable $e) {
                continue; // Si un renglón viene dañado, lo saltamos silenciosamente
            }
        }

        // Borramos el archivo temporal una vez leído
        unlink($filePath);
    }
}