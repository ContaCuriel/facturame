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
    protected $signature = 'sat:request-gastos';
    protected $description = 'Solicita al SAT los paquetes de gastos del mes actual para todas las empresas';

    public function handle()
    {
        // Traemos solo a las empresas que sí tienen su FIEL subida
        $companies = Company::whereNotNull('fiel_cer_path')
                            ->whereNotNull('fiel_key_path')
                            ->get();

        $this->info("Iniciando solicitud de gastos para " . $companies->count() . " empresas...");

        // AQUÍ DEFINIMOS LAS FECHAS: Desde el día 1 del mes actual, hasta hoy.
        $fechaInicio = new DateTimeImmutable(now()->startOfMonth()->format('Y-m-d 00:00:00'));
        $fechaFin = new DateTimeImmutable(now()->format('Y-m-d 23:59:59'));

        foreach ($companies as $company) {
            $this->info("Solicitando para la empresa: {$company->name} (RFC: {$company->rfc})");

            try {
                $satService = new SatScraperService($company);
                $requestId = $satService->solicitarDescargaDeGastos($fechaInicio, $fechaFin);

                // Guardamos el ticket en nuestra cartera
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