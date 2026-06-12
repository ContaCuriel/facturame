<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Company;
use App\Models\SatRequest;
use App\Services\SatScraperService;
use DateTimeImmutable;
use Throwable;

class RequestSatXml extends Command
{
    protected $signature = 'sat:request-xml';
    protected $description = 'FASE 2: Solicita al SAT los paquetes de archivos XML físicos';

    public function handle()
    {
        $empresas = Company::all();
        $this->info("Iniciando solicitud de XMLs (Fase 2) para {$empresas->count()} empresas...");

        foreach ($empresas as $empresa) {
            $this->info("Analizando: {$empresa->name} (RFC: {$empresa->rfc})");

            try {
                // Pedimos el histórico del año
                $fechaInicio = new DateTimeImmutable('2026-01-01 00:00:00');
                $fechaFin = new DateTimeImmutable(); 

                $satService = new SatScraperService($empresa);
                
                // Le pasamos el parámetro 'xml' para activar el filtro de Vigentes
                $requestId = $satService->solicitarDescargaDeGastos($fechaInicio, $fechaFin, 'xml');

                // Guardamos el ticket con un tipo diferente ('xml_gastos')
                SatRequest::updateOrCreate(
                    ['company_id' => $empresa->id, 'type' => 'xml_gastos'],
                    [
                        'request_id' => $requestId,
                        'status' => 'pending',
                        'mensaje_sat' => 'Ticket de XML recibido. Esperando a que el SAT prepare el paquete ZIP.'
                    ]
                );

                $this->info("✅ Éxito. Ticket XML recibido: {$requestId}");

            } catch (Throwable $e) {
                $this->error("❌ Error con {$empresa->rfc}: " . $e->getMessage());
            }
        }
        $this->info("Proceso de solicitud de XMLs finalizado.");
    }
}