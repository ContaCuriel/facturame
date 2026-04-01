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