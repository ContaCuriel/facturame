<?php

namespace App\View\Composers;

use Illuminate\View\View;
use App\Models\Patron; // Asegúrate de importar tu modelo Patron

class PatronLogoComposer
{
    /**
     * Enlaza datos a la vista.
     */
    public function compose(View $view): void
    {
        $logo = null; 
        $patronPrincipal = Patron::whereNotNull('logo_path')->first(); 

        if ($patronPrincipal && $patronPrincipal->logo_path) {
            $logo = $patronPrincipal->logo_path;
        }

        $view->with('logo', $logo); 
    }
}