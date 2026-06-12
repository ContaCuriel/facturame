<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 🛠️ TRUCO MAESTRO: Crear carpetas de caché en el disco persistente de Render si no existen
        $carpetasNecesarias = [
            storage_path('framework/sessions'),
            storage_path('framework/views'),
            storage_path('framework/cache'),
            storage_path('app/private'),
            storage_path('app/public'),
        ];

        foreach ($carpetasNecesarias as $carpeta) {
            if (!file_exists($carpeta)) {
                // Creamos la carpeta con permisos de lectura y escritura
                mkdir($carpeta, 0775, true);
            }
        }

        // Configura la paginación para que use los estilos de Bootstrap 5
        Paginator::useBootstrapFive();

        /**
         * He eliminado las siguientes líneas porque los modelos y clases
         * a los que hacían referencia (Gasto, Placement, Recovery, PatronLogoComposer)
         * ya no existen en tu proyecto depurado.
         *
         * - View::composer(...)
         * - Gasto::observe(...)
         * - Placement::observe(...)
         * - Recovery::observe(...)
         */

        // Mantenemos esta lógica por si usas un túnel como ngrok.
        // Fuerza que todas las URLs se generen con HTTPS si la conexión es segura.
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            URL::forceScheme('https');
        }
    }
}