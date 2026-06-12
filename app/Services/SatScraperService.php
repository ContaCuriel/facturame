<?php

namespace App\Services;

use App\Models\Company;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\FielRequestBuilder\Fiel;
use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\FielRequestBuilder\FielRequestBuilder;
use PhpCfdi\SatWsDescargaMasiva\Service;
use PhpCfdi\SatWsDescargaMasiva\WebClient\GuzzleWebClient;
use PhpCfdi\SatWsDescargaMasiva\Shared\DateTimePeriod;
use PhpCfdi\SatWsDescargaMasiva\Shared\DownloadType;
use PhpCfdi\SatWsDescargaMasiva\Shared\RequestType;
use DateTimeImmutable;

class SatScraperService
{
    protected $company;

    public function __construct(Company $company)
    {
        $this->company = $company;
    }

    /**
     * Inicializa la conexión con el SAT usando la FIEL de la empresa
     */
    protected function getSatService(): Service
    {
        if (!$this->company->fiel_cer_path || !$this->company->fiel_key_path || !$this->company->fiel_password) {
            throw new Exception('La empresa no tiene la FIEL configurada o está incompleta.');
        }

        // Buscamos primero en el disco 'private' (disco persistente)
        $cerContents = Storage::disk('private')->get($this->company->fiel_cer_path) 
                    ?? Storage::disk('local')->get($this->company->fiel_cer_path);
                    
        $keyContents = Storage::disk('private')->get($this->company->fiel_key_path) 
                    ?? Storage::disk('local')->get($this->company->fiel_key_path);

        if (!$cerContents || !$keyContents) {
            throw new Exception("No se encontraron los archivos físicos de la FIEL en el servidor.");
        }
        
        // Desencriptamos la contraseña
        $password = Crypt::decryptString($this->company->fiel_password);

        // Creamos la firma electrónica
        $fiel = Fiel::create($cerContents, $keyContents, $password);

        // Preparamos el cliente web
        $webClient = new GuzzleWebClient();
        $requestBuilder = new FielRequestBuilder($fiel);

        return new Service($requestBuilder, $webClient);
    }

    /**
     * PASO A: Solicitar al SAT que prepare el paquete de facturas recibidas (Gastos)
     */
    public function solicitarDescargaDeGastos(DateTimeImmutable $fechaInicio, DateTimeImmutable $fechaFin)
    {
        $satService = $this->getSatService();

        // Buscamos facturas RECIBIDAS (Gastos) solicitando METADATA para evitar bloqueos por facturas canceladas
        $query = \PhpCfdi\SatWsDescargaMasiva\Services\Query\QueryParameters::create(
            DateTimePeriod::create(
                \PhpCfdi\SatWsDescargaMasiva\Shared\DateTime::create($fechaInicio->format('Y-m-d H:i:s')),
                \PhpCfdi\SatWsDescargaMasiva\Shared\DateTime::create($fechaFin->format('Y-m-d H:i:s'))
            ),
            DownloadType::received(),
            RequestType::metadata(), // <-- CAMBIAMOS DE xml() A metadata()
            \PhpCfdi\SatWsDescargaMasiva\Shared\ServiceType::cfdi(),
            \PhpCfdi\SatWsDescargaMasiva\Shared\DocumentStatus::undefined()
        );

        // Enviamos la petición al SAT
        $requestResult = $satService->query($query);

        if (!$requestResult->getStatus()->isAccepted()) {
            throw new Exception('El SAT rechazó la solicitud: ' . $requestResult->getStatus()->getMessage());
        }

        return $requestResult->getRequestId();
    }

    /**
     * PASO B: Preguntar al SAT el estado de un ticket y descargar el archivo si está listo.
     */
    public function verificarYDescargar(string $requestId)
    {
        $satService = $this->getSatService();
        $verifyResult = $satService->verify($requestId);

        // 🕵️‍♂️ TRUCO DETECTIVE: Imprimir la lista exacta de funciones y detener el programa
        dd(get_class_methods($verifyResult));
    }
}