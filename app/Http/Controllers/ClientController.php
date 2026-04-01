<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ClientController extends Controller
{
    use AuthorizesRequests;

    /**
     * Muestra la lista de clientes de una empresa específica.
     */
    public function index(Request $request)
    {
        $request->validate(['company_id' => 'required|exists:companies,id']);
        $company = Company::findOrFail($request->query('company_id'));
        $this->authorize('view', $company);
        $clients = $company->clients;
        return view('clients.index', compact('clients', 'company'));
    }

    /**
     * Muestra el formulario para crear un nuevo cliente.
     */
    public function create(Request $request)
    {
        $request->validate(['company_id' => 'required|exists:companies,id']);
        $company = Company::findOrFail($request->query('company_id'));
        $this->authorize('view', $company);
        return view('clients.create', [
            'company' => $company,
            'fiscalRegimes' => config('sat.fiscal_regimes'),
            'cfdiUses' => config('sat.cfdi_uses'),
            'paymentForms' => config('sat.payment_forms'),
            'paymentMethods' => config('sat.payment_methods'),
            'countries' => config('sat.countries'),
        ]);
    }

    /**
     * Guarda el nuevo cliente en la base de datos.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'name' => 'required|string|max:255',
            'rfc' => 'required|string|size:13',
            'fiscal_regime' => 'required|string',
            'zip_code' => 'required|string|digits:5',
            'commercial_name' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'print_address' => 'nullable|boolean',
            'payment_method' => 'nullable|string',
            'payment_form' => 'nullable|string',
            'cfdi_use' => 'nullable|string',
            'email' => 'nullable|email',
            'email_cc' => 'nullable|email',
            'is_foreign' => 'nullable|boolean',
            'tax_residence' => 'required_if:is_foreign,1|nullable|string',
            'tax_id_registration' => 'required_if:is_foreign,1|nullable|string',
        ]);
        $company = Company::findOrFail($validatedData['company_id']);
        $this->authorize('update', $company);
        $company->clients()->create($validatedData);
        return redirect()->route('clients.index', ['company_id' => $company->id])
                         ->with('success', 'Cliente creado exitosamente!');
    }

    /**
     * ✅ MÉTODO QUE FALTABA ✅
     * Muestra el formulario para editar un cliente existente.
     */
    public function edit(Client $client)
    {
        // Usamos la policy para verificar que el usuario sea el dueño de la empresa del cliente
        $company = $client->company;
        $this->authorize('update', $company);
        
        return view('clients.edit', [
            'client' => $client,
            'company' => $company,
            'fiscalRegimes' => config('sat.fiscal_regimes'),
            'cfdiUses' => config('sat.cfdi_uses'),
            'paymentForms' => config('sat.payment_forms'),
            'paymentMethods' => config('sat.payment_methods'),
            'countries' => config('sat.countries'),
        ]);
    }

    /**
     * ✅ MÉTODO QUE FALTABA ✅
     * Actualiza un cliente existente en la base de datos.
     */
    public function update(Request $request, Client $client)
    {
        $company = $client->company;
        $this->authorize('update', $company);

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'rfc' => 'required|string|size:13|unique:clients,rfc,' . $client->id,
            'fiscal_regime' => 'required|string',
            'zip_code' => 'required|string|digits:5',
            'commercial_name' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'print_address' => 'nullable|boolean',
            'payment_method' => 'nullable|string',
            'payment_form' => 'nullable|string',
            'cfdi_use' => 'nullable|string',
            'email' => 'nullable|email',
            'email_cc' => 'nullable|email',
            'is_foreign' => 'nullable|boolean',
            'tax_residence' => 'required_if:is_foreign,1|nullable|string',
            'tax_id_registration' => 'required_if:is_foreign,1|nullable|string',
        ]);
        
        $validatedData['print_address'] = $request->has('print_address');
        $validatedData['is_foreign'] = $request->has('is_foreign');

        $client->update($validatedData);

        return redirect()->route('clients.index', ['company_id' => $client->company_id])
                         ->with('success', '¡Cliente actualizado exitosamente!');
    }

    public function search(Request $request)
{
    $request->validate([
        'company_id' => 'required|exists:companies,id',
        'query' => 'required|string|min:2',
    ]);

    $company = Company::findOrFail($request->company_id);
    $this->authorize('view', $company);

    $query = $request->input('query');

    $clients = Client::where('company_id', $company->id)
        ->where(function ($q) use ($query) {
            $q->where('name', 'LIKE', "%{$query}%")
              ->orWhere('rfc', 'LIKE', "%{$query}%")
              ->orWhere('commercial_name', 'LIKE', "%{$query}%");
        })
        ->limit(10)
        ->get();

    return response()->json($clients);
}
}