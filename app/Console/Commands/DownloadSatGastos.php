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

        // 🕵️‍♂️ TRUCO DETECTIVE: Imprimir los primeros 3 renglones del archivo y detenerse
        dd(array_slice($lines, 0, 3));
    }
}