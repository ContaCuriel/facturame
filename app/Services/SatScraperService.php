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
     * PASO A: Solicitar al SAT que prepare el paquete
     */
    public function solicitarDescargaDeGastos(DateTimeImmutable $fechaInicio, DateTimeImmutable $fechaFin, string $tipoArchivo = 'metadata')
    {
        $satService = $this->getSatService();

        // 🧠 LA MAGIA: Si pedimos XML, le exigimos al SAT que solo mande Vigentes para evadir su bloqueo.
        $requestType = $tipoArchivo === 'xml' ? RequestType::xml() : RequestType::metadata();
        $documentStatus = $tipoArchivo === 'xml' 
            ? \PhpCfdi\SatWsDescargaMasiva\Shared\DocumentStatus::active() 
            : \PhpCfdi\SatWsDescargaMasiva\Shared\DocumentStatus::undefined();

        $query = \PhpCfdi\SatWsDescargaMasiva\Services\Query\QueryParameters::create(
            DateTimePeriod::create(
                \PhpCfdi\SatWsDescargaMasiva\Shared\DateTime::create($fechaInicio->format('Y-m-d H:i:s')),
                \PhpCfdi\SatWsDescargaMasiva\Shared\DateTime::create($fechaFin->format('Y-m-d H:i:s'))
            ),
            DownloadType::received(),
            $requestType,
            \PhpCfdi\SatWsDescargaMasiva\Shared\ServiceType::cfdi(),
            $documentStatus
        );

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

        // 1. Verificamos cómo va la solicitud en el SAT
        $verifyResult = $satService->verify($requestId);

        if (!$verifyResult->getStatus()->isAccepted()) {
            throw new Exception("El ticket fue rechazado o no es válido.");
        }

        // Revisamos el código de respuesta del SAT
        $estadoSat = $verifyResult->getCodeRequest()->getValue();

        // 5004 significa que el rango de fechas no tiene ninguna factura
        if ($estadoSat === '5004') {
            return ['status' => 'no_data', 'message' => 'No se encontraron facturas en esas fechas.'];
        }

        // Si el SAT todavía no termina de empaquetar, avisamos que sigue pendiente
        if (!$verifyResult->getStatusRequest()->isFinished()) {
            return ['status' => 'pending', 'message' => 'El SAT aún está procesando el paquete (Estado: ' . $estadoSat . ').'];
        }

        // 2. Obtenemos los IDs usando el método exacto que descubrimos (getPackagesIds)
        $packageIds = $verifyResult->getPackagesIds();
        
        if (empty($packageIds)) {
            return ['status' => 'error', 'message' => 'El SAT dice que terminó pero no se encontraron IDs de paquetes disponibles.'];
        }

        $archivosDescargados = [];

        // 3. Descargamos el o los paquetes devueltos
        foreach ($packageIds as $packageId) {
            $downloadResult = $satService->download($packageId);
            
            if (!$downloadResult->getStatus()->isAccepted()) {
                continue;
            }

            // Guardamos el contenido en el disco local temporal
            $packageContent = $downloadResult->getPackageContent();
            $fileName = "sat_downloads/{$this->company->id}_{$packageId}.txt";
            
            // Aseguramos que la carpeta temporal exista
            if (!Storage::disk('local')->exists('sat_downloads')) {
                Storage::disk('local')->makeDirectory('sat_downloads');
            }
            
            Storage::disk('local')->put($fileName, $packageContent);
            
            $archivosDescargados[] = Storage::disk('local')->path($fileName);
        }

        return [
            'status' => 'downloaded', 
            'message' => 'Información descargada con éxito desde el SAT.',
            'files' => $archivosDescargados
        ];
    }
}