<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FacturamaService
{
    protected $apiUrl;
    protected $apiUser;
    protected $apiPassword;

    public function __construct()
    {
        // ✅ --- CAMBIO IMPORTANTE AQUÍ --- ✅
        // Apuntamos a la URL de Producción para generar facturas reales.
        $this->apiUrl = 'https://api.facturama.mx'; 
        $this->apiUser = env('FACTURAMA_API_KEY');
        $this->apiPassword = env('FACTURAMA_SECRET_KEY');
    }

    public function uploadCsd(string $rfc, string $cerContent, string $keyContent, string $password)
    {
        $endpoint = '/api-lite/csds';
        $payload = [
            'Rfc' => $rfc,
            'Certificate' => base64_encode($cerContent),
            'PrivateKey' => base64_encode($keyContent),
            'PrivateKeyPassword' => $password,
        ];
        return Http::withoutVerifying()
            ->withBasicAuth($this->apiUser, $this->apiPassword)
            ->post($this->apiUrl . $endpoint, $payload);
    }

    public function createInvoice(array $invoiceData)
    {
        $endpoint = '/api-lite/3/cfdis'; 
        return Http::timeout(40)
            ->withoutVerifying()
            ->withBasicAuth($this->apiUser, $this->apiPassword)
            ->post($this->apiUrl . $endpoint, $invoiceData);
    }

    public function getInvoiceFile(string $facturamaId, string $format)
    {
        $type = 'issuedLite';
        $endpoint = "/cfdi/{$format}/{$type}/{$facturamaId}";
        return Http::timeout(40)
            ->withoutVerifying()
            ->withBasicAuth($this->apiUser, $this->apiPassword)
            ->get($this->apiUrl . $endpoint);
    }

    /**
     * Envía una factura por correo electrónico, ahora con parámetros en la URL.
     */
    public function sendInvoiceByEmail(string $facturamaId, string $email, ?string $subject = null, ?string $comments = null)
    {
        $type = 'issuedLite';

        // Preparamos los parámetros base para la URL
        $queryParams = http_build_query([
            'CfdiType' => $type,
            'CfdiId' => $facturamaId,
            'Email' => $email,
        ]);

        // Construimos la URL completa
        $fullUrl = "{$this->apiUrl}/Cfdi?{$queryParams}";

        // Añadimos los parámetros opcionales si existen
        if ($subject) {
            $fullUrl .= "&Subject=" . urlencode($subject);
        }
        if ($comments) {
            $fullUrl .= "&Comments=" . urlencode($comments);
        }
        
        Log::info("Enviando factura por correo a la URL: {$fullUrl}");

        // La petición POST ahora se hace a la URL completa, sin cuerpo de datos.
        $response = Http::timeout(40)
            ->withoutVerifying()
            ->withBasicAuth($this->apiUser, $this->apiPassword)
            ->post($fullUrl);

        return $response;
    }

    public function cancelInvoice(string $uuid, string $motive, ?string $replacementUuid = null)
    {
        $endpoint = "/api-lite/cfdis/{$uuid}?motive={$motive}";
        if ($motive === '01' && $replacementUuid) {
            $endpoint .= "&uuidReplacement={$replacementUuid}";
        }
        return Http::timeout(40)
            ->withoutVerifying()
            ->withBasicAuth($this->apiUser, $this->apiPassword)
            ->delete($this->apiUrl . $endpoint);
    }

     public function uploadLogo(string $base64Image, string $imageType)
    {
        $endpoint = '/TaxEntity/UploadLogo';
        $payload = [
            'Image' => $base64Image,
            'Type' => $imageType,
        ];

        Log::info("Subiendo logo a Facturama.");

        $response = Http::withoutVerifying()
            ->withBasicAuth($this->apiUser, $this->apiPassword)
            ->put($this->apiUrl . $endpoint, $payload);

        return $response;
    }
}
