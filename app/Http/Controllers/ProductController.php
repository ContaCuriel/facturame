<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Company;
use App\Models\SatProductKey;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProductController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $request->validate(['company_id' => 'required|exists:companies,id']);
        $company = Company::findOrFail($request->query('company_id'));
        $this->authorize('view', $company);

        $products = $company->products;
        return view('products.index', compact('products', 'company'));
    }

    public function create(Request $request)
    {
        $request->validate(['company_id' => 'required|exists:companies,id']);
        $company = Company::findOrFail($request->query('company_id'));
        $this->authorize('view', $company);

        // ✅ Obtenemos la lista de alumnos para pasarla al formulario
        $students = $company->students()->orderBy('name')->get();

        return view('products.create', [
            'company' => $company,
            'satUnitKeys' => config('sat.unit_keys'),
            'students' => $students, // <-- Pasamos los alumnos
        ]);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'description' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100|unique:products,sku,NULL,id,company_id,'.$request->company_id,
            'price' => 'required|numeric|min:0',
            'sat_product_key' => 'required|string|exists:sat_product_keys,code',
            'sat_unit_key' => 'required|string|max:10',
            'taxes' => 'nullable|boolean',
            'student_id' => 'nullable|exists:students,id', // ✅ Validación para el nuevo campo
        ]);
        
        $company = Company::findOrFail($validatedData['company_id']);
        $this->authorize('update', $company);
        
        $validatedData['taxes'] = $request->has('taxes');
        $company->products()->create($validatedData);

        return redirect()->route('products.index', ['company_id' => $company->id])
                         ->with('success', 'Producto/Servicio creado exitosamente!');
    }

    public function edit(Product $product)
    {
        $this->authorize('update', $product->company);

        $satProductKeyName = SatProductKey::where('code', $product->sat_product_key)->value('name');
        
        // ✅ Obtenemos la lista de alumnos
        $students = $product->company->students()->orderBy('name')->get();

        return view('products.edit', [
            'product' => $product,
            'company' => $product->company,
            'satUnitKeys' => config('sat.unit_keys'),
            'satProductKeyName' => $satProductKeyName,
            'students' => $students, // <-- Pasamos los alumnos
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $this->authorize('update', $product->company);

        $validatedData = $request->validate([
            'description' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100|unique:products,sku,'.$product->id.',id,company_id,'.$product->company_id,
            'price' => 'required|numeric|min:0',
            'sat_product_key' => 'required|string|exists:sat_product_keys,code',
            'sat_unit_key' => 'required|string|max:10',
            'taxes' => 'nullable|boolean',
            'student_id' => 'nullable|exists:students,id', // ✅ Validación para el nuevo campo
        ]);

        $validatedData['taxes'] = $request->has('taxes');
        $product->update($validatedData);

        return redirect()->route('products.index', ['company_id' => $product->company_id])
                         ->with('success', 'Producto/Servicio actualizado exitosamente!');
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

        // ✅ --- CAMBIO AQUÍ: Usamos with('student') para cargar los datos del alumno --- ✅
        $products = Product::where('company_id', $company->id)
            ->with('student') // Carga la relación con el alumno
            ->where(function ($q) use ($query) {
                $q->where('description', 'LIKE', "%{$query}%")
                  ->orWhere('sku', 'LIKE', "%{$query}%");
            })
            ->limit(10)
            ->get();

        return response()->json($products);
    }
}
