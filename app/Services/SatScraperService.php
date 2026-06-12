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

        // CORRECCIÓN: Buscamos primero en el disco 'private' (donde realmente se guardaron)
        $cerContents = Storage::disk('private')->get($this->company->fiel_cer_path) 
                    ?? Storage::disk('local')->get($this->company->fiel_cer_path);
                    
        $keyContents = Storage::disk('private')->get($this->company->fiel_key_path) 
                    ?? Storage::disk('local')->get($this->company->fiel_key_path);

        if (!$cerContents || !$keyContents) {
            throw new Exception("No se encontraron los archivos físicos de la FIEL en el servidor.");
        }
        
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

    /**
     * PASO B: Preguntar al SAT el estado de un ticket y descargar el ZIP si está listo.
     */
    public function verificarYDescargar(string $requestId)
    {
        $satService = $this->getSatService();

        // 1. Verificamos cómo va la solicitud
        $verifyResult = $satService->verify($requestId);

        if (!$verifyResult->getStatus()->isAccepted()) {
            throw new Exception("El ticket fue rechazado o no es válido.");
        }

        $estadoSat = $verifyResult->getCodeRequest()->getValue();

        // 5000 = Solicitud recibida, 5002 = Agotó intentos, 5003 = Tope superado, 5004 = No hay información
        if ($estadoSat === '5004') {
            return ['status' => 'no_data', 'message' => 'No se encontraron facturas en esas fechas.'];
        }

        if (!$verifyResult->getStatusRequest()->isFinished()) {
            return ['status' => 'pending', 'message' => 'El SAT aún está procesando el paquete (Estado: ' . $estadoSat . ').'];
        }

        // 2. Si ya está terminado, obtenemos los IDs de los paquetes ZIP
        $packageIds = $verifyResult->getPackageIds();
        
        if (empty($packageIds)) {
            return ['status' => 'error', 'message' => 'El SAT dice que terminó pero no dio IDs de paquetes.'];
        }

        $archivosDescargados = [];

        // 3. Descargamos cada paquete ZIP
        foreach ($packageIds as $packageId) {
            $downloadResult = $satService->download($packageId);
            
            if (!$downloadResult->getStatus()->isAccepted()) {
                continue; // Si falla uno, intentamos con el siguiente
            }

            // Guardamos el ZIP temporalmente en el disco local
            $zipContent = $downloadResult->getPackageContent();
            $fileName = "sat_downloads/{$this->company->id}_{$packageId}.zip";
            Storage::disk('local')->put($fileName, $zipContent);
            
            $archivosDescargados[] = Storage::disk('local')->path($fileName);
        }

        return [
            'status' => 'downloaded', 
            'message' => 'Paquetes descargados con éxito.',
            'files' => $archivosDescargados
        ];
    }
}