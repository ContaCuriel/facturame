<?php

namespace App\Services;

use App\Models\Company;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use PhpCfdi\Credentials\Credential;
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

        // Leemos los archivos de la FIEL desde el disco
        $cerContents = Storage::disk('local')->get($this->company->fiel_cer_path);
        $keyContents = Storage::disk('local')->get($this->company->fiel_key_path);
        
        // Desencriptamos la contraseña que guardamos de forma segura
        $password = Crypt::decryptString($this->company->fiel_password);

        // Creamos la firma electrónica
        $fiel = Fiel::create($cerContents, $keyContents, $password);

        // Preparamos el cliente web
        $webClient = new GuzzleWebClient();
        $requestBuilder = new FielRequestBuilder($fiel);

        // Retornamos el servicio listo para hacer peticiones
        return new Service($requestBuilder, $webClient);
    }

    /**
     * PASO A: Solicitar al SAT que prepare el paquete de facturas recibidas (Gastos)
     */
    public function solicitarDescargaDeGastos(DateTimeImmutable $fechaInicio, DateTimeImmutable $fechaFin)
    {
        $satService = $this->getSatService();

        // Buscamos facturas RECIBIDAS (Gastos)
        $query = \PhpCfdi\SatWsDescargaMasiva\Shared\QueryParameters::create(
            DateTimePeriod::create($fechaInicio, $fechaFin),
            DownloadType::received(),
            RequestType::xml()
        );

        // Enviamos la petición al SAT
        $requestResult = $satService->query($query);

        if (!$requestResult->getStatus()->isAccepted()) {
            throw new Exception('El SAT rechazó la solicitud: ' . $requestResult->getStatus()->getMessage());
        }

        // El SAT nos devuelve un ID de solicitud. Con este ID luego iremos a preguntar si ya está listo.
        return $requestResult->getRequestId();
    }
}