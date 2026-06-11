<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Company;
use App\Models\SatRequest;
use App\Services\SatScraperService;
use DateTimeImmutable;
use Throwable;

class RequestSatGastos extends Command
{
    // Agregamos parámetros opcionales para pedir históricos
    protected $signature = 'sat:request-gastos {--inicio=} {--fin=} {--company_id=}';
    protected $description = 'Solicita al SAT los paquetes de gastos. Por defecto mes actual, o usa --inicio y --fin (Y-m-d)';

    public function handle()
    {
        // Traemos a las empresas que tienen FIEL
        $companies = Company::whereNotNull('fiel_cer_path')
                            ->whereNotNull('fiel_key_path')
                            ->get();

        $this->info("Iniciando solicitud de gastos para " . $companies->count() . " empresas...");

        foreach ($companies as $company) {
            $this->info("Analizando: {$company->name} (RFC: {$company->rfc})");

            // Revisamos si esta empresa ya ha hecho solicitudes antes
            $tieneSolicitudesPrevias = SatRequest::where('company_id', $company->id)
                                                 ->where('type', 'gastos')
                                                 ->exists();

            if (!$tieneSolicitudesPrevias && $company->fecha_inicio_descarga_gastos) {
                // PRIMERA VEZ: Usamos la fecha que el cliente puso en la interfaz
                $fechaInicioStr = $company->fecha_inicio_descarga_gastos->format('Y-m-d 00:00:00');
                $this->info("Primera descarga detectada. Solicitando histórico desde: {$fechaInicioStr}");
            } else {
                // MANTENIMIENTO DIARIO: Solo pedimos desde el inicio del mes actual
                $fechaInicioStr = now()->startOfMonth()->format('Y-m-d 00:00:00');
                $this->info("Actualización regular. Solicitando desde el inicio de mes: {$fechaInicioStr}");
            }

            $fechaFinStr = now()->format('Y-m-d 23:59:59');

            $fechaInicio = new DateTimeImmutable($fechaInicioStr);
            $fechaFin = new DateTimeImmutable($fechaFinStr);

            try {
                $satService = new SatScraperService($company);
                $requestId = $satService->solicitarDescargaDeGastos($fechaInicio, $fechaFin);

                SatRequest::create([
                    'company_id' => $company->id,
                    'request_id' => $requestId,
                    'type' => 'gastos',
                    'status' => 'pending',
                    'fecha_inicio' => $fechaInicio->format('Y-m-d H:i:s'),
                    'fecha_fin' => $fechaFin->format('Y-m-d H:i:s'),
                    'mensaje_sat' => 'Solicitud aceptada, esperando paquete.'
                ]);

                $this->info("✅ Éxito. Ticket recibido: {$requestId}");

            } catch (Throwable $e) {
                $this->error("❌ Error con {$company->rfc}: " . $e->getMessage());
            }
        }

        $this->info("Proceso de solicitud finalizado.");
    }
}