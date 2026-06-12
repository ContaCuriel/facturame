<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Gasto;
use Illuminate\Http\Request;

class GastoController extends Controller
{
    public function index(Request $request)
    {
        // Obtenemos la empresa actual
        $company_id = $request->input('company_id');
        $company = Company::findOrFail($company_id);

        $this->authorize('view', $company);

        // Traemos los gastos ordenados de más reciente a más antiguo
        $gastos = Gasto::where('company_id', $company->id)
                       ->orderBy('fecha_emision', 'desc')
                       ->paginate(50); // Muestra 50 por página

        return view('gastos.index', compact('company', 'gastos'));
    }

    // Aquí después agregaremos la función show() si queremos ver el detalle de un gasto
}