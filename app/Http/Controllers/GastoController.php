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
    public function show(Gasto $gasto)
    {
        $company = $gasto->company;
        $this->authorize('view', $company);

        $conceptos = [];

        // Si el archivo XML existe, lo abrimos y extraemos los conceptos en tiempo real
        if ($gasto->xml_path && \Illuminate\Support\Facades\Storage::disk('local')->exists($gasto->xml_path)) {
            $xmlContent = \Illuminate\Support\Facades\Storage::disk('local')->get($gasto->xml_path);
            $cleanXml = str_replace(['cfdi:', 'tfd:'], '', $xmlContent);
            $xml = simplexml_load_string($cleanXml);

            if ($xml && isset($xml->Conceptos->Concepto)) {
                foreach ($xml->Conceptos->Concepto as $concepto) {
                    $conceptos[] = [
                        'cantidad' => (string) $concepto['Cantidad'],
                        'unidad' => (string) $concepto['ClaveUnidad'],
                        'descripcion' => (string) $concepto['Descripcion'],
                        'precio_unitario' => (float) $concepto['ValorUnitario'],
                        'importe' => (float) $concepto['Importe'],
                    ];
                }
            }
        }

        return view('gastos.show', compact('company', 'gasto', 'conceptos'));
    }