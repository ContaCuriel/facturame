<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Gasto;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; // <-- 1. Importamos la clase

class GastoController extends Controller
{
    use AuthorizesRequests; // <-- 2. Le damos el superpoder de autorizar al controlador

    public function index(Request $request)
    {
        // Obtenemos la empresa actual
        $company_id = $request->input('company_id');
        $company = Company::findOrFail($company_id);

        // Verificamos que el usuario tenga permiso de ver esta empresa
        $this->authorize('view', $company);

        // Traemos los gastos ordenados de más reciente a más antiguo
        $gastos = Gasto::where('company_id', $company->id)
                       ->orderBy('fecha_emision', 'desc')
                       ->paginate(50); // Muestra 50 por página

        return view('gastos.index', compact('company', 'gastos'));
    }
}