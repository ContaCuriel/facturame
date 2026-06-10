<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Company;
use App\Models\User;
use App\Mail\CertificateExpirationAlert;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class CheckCertificatesExpiration extends Command
{
    // El nombre del comando que usaremos en la terminal
    protected $signature = 'certificates:check';
    
    // Descripción de lo que hace
    protected $description = 'Revisa las caducidades de CSD y FIEL y envía alertas de correo si están a 30 días o menos de vencer.';

    public function handle()
    {
        $this->info('Iniciando revisión de certificados...');
        
        $companies = Company::all();
        $now = Carbon::now();
        $emailsSent = 0;

        foreach ($companies as $company) {
            $alerts = [];

            // 1. Evaluar CSD
            if ($company->csd_expires_at) {
                $daysCsd = (int) $now->diffInDays(Carbon::parse($company->csd_expires_at), false);
                if ($daysCsd < 0) {
                    $alerts[] = '🔴 El Sello Digital (CSD) ha caducado.';
                } elseif ($daysCsd <= 30) {
                    $alerts[] = "🟠 El Sello Digital (CSD) caduca en {$daysCsd} días.";
                }
            }

            // 2. Evaluar e.firma
            if ($company->fiel_expires_at) {
                $daysFiel = (int) $now->diffInDays(Carbon::parse($company->fiel_expires_at), false);
                if ($daysFiel < 0) {
                    $alerts[] = '🔴 La e.firma (FIEL) ha caducado.';
                } elseif ($daysFiel <= 30) {
                    $alerts[] = "🟠 La e.firma (FIEL) caduca en {$daysFiel} días.";
                }
            }

            // 3. Si hay alertas, enviar el correo
            if (!empty($alerts)) {
                // Buscamos al usuario dueño de la empresa (usando la llave foránea user_id)
                $user = clone User::find($company->user_id); 
                
                if ($user && $user->email) {
                    Mail::to($user->email)->send(new CertificateExpirationAlert($company, $alerts));
                    $emailsSent++;
                    $this->info("✔️  Alerta enviada a {$user->email} (Empresa: {$company->rfc})");
                }
            }
        }

        $this->info("Revisión completada. Se enviaron {$emailsSent} correos en total.");
    }
}