<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Invoice; // Importar el modelo Invoice
use App\Services\FacturamaService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon; // Importar Carbon para manejar fechas

class CompanyController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $companies = auth()->user()->companies;
        return view('companies.index', compact('companies'));
    }

    public function create()
    {
        $fiscalRegimes = config('sat.fiscal_regimes');
        return view('companies.create', compact('fiscalRegimes'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'rfc' => 'required|string|size:13|unique:companies,rfc',
            'fiscal_regime' => 'required|string|max:10',
            'zip_code' => 'required|string|digits:5',
        ]);
        auth()->user()->companies()->create($validatedData);
        return redirect()->route('companies.index')->with('success', '¡Empresa creada exitosamente!');
    }

    /**
     * Muestra el panel de control (dashboard) de una empresa específica.
     */
    public function show(Company $company)
    {
        $this->authorize('view', $company);

        $now = Carbon::now();
        
        // Calcular ingresos del mes actual
        $monthlyRevenue = Invoice::where('company_id', $company->id)
            ->where('status', 'issued')
            ->whereYear('created_at', $now->year)
            ->whereMonth('created_at', $now->month)
            ->sum('total');

        // Contar facturas del mes
        $invoiceCount = Invoice::where('company_id', $company->id)
            ->whereYear('created_at', $now->year)
            ->whereMonth('created_at', $now->month)
            ->count();
            
        // Contar clientes
        $totalClients = $company->clients()->count();

        // ✅ --- LÓGICA AÑADIDA --- ✅
        // Obtener las 5 facturas más recientes
        $recentInvoices = Invoice::where('company_id', $company->id)
            ->latest()
            ->take(5)
            ->get();

        return view('company.dashboard', compact('company', 'monthlyRevenue', 'invoiceCount', 'totalClients', 'recentInvoices'));
    }

    public function showCsdForm(Company $company)
    {
        $this->authorize('update', $company);
        return view('companies.csd', compact('company'));
    }

    public function storeCsd(Request $request, Company $company, FacturamaService $facturama)
    {
        $this->authorize('update', $company);

        $request->validate([
            'csd_cer' => 'required|file|extensions:cer',
            'csd_key' => 'required|file|extensions:key',
            'csd_password' => 'required|string',
        ]);

        $folder = "csd/{$company->id}";
        $cerPath = $request->file('csd_cer')->store($folder, 'private');
        $keyPath = $request->file('csd_key')->store($folder, 'private');
        $csdPassword = $request->csd_password;

        $company->update([
            'csd_cer_path' => $cerPath,
            'csd_key_path' => $keyPath,
            'csd_password' => $csdPassword,
        ]);

        $cerContent = Storage::disk('private')->get($cerPath);
        $keyContent = Storage::disk('private')->get($keyPath);

        $response = $facturama->uploadCsd($company->rfc, $cerContent, $keyContent, $csdPassword);

        if ($response->failed()) {
            $errorBody = $response->body();
            $statusCode = $response->status();
            $detailedError = "Status: {$statusCode}. Respuesta: {$errorBody}";
            return back()->with('error', 'Falló la subida a Facturama. ' . $detailedError);
        }

        return redirect()->back()->with('success', '¡Certificados guardados y subidos a Facturama correctamente!');
    }

    public function showLogoForm(Company $company)
    {
        $this->authorize('update', $company);
        return view('companies.logo', compact('company'));
    }

    public function storeLogo(Request $request, Company $company)
{
    $this->authorize('update', $company);

    $request->validate([
        'logo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
    ]);

    // Guarda el logo en storage/app/public/logos
    $path = $request->file('logo')->store('logos', 'public');
    $company->update(['logo_path' => $path]);

    // ¡ELIMINAMOS LA LLAMADA AL FACTURAMASERVICE AQUÍ!

    return redirect()->back()->with('success', '¡Logo guardado exitosamente para tus facturas!');
}
}