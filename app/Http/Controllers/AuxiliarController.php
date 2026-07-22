<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class AuxiliarController
{
    public function index()
    {
        $owner = auth()->user();
        
        // Obtenemos todos los auxiliares que tienen acceso a las empresas de este dueño
        $auxiliares = User::whereHas('assignedCompanies', function ($query) use ($owner) {
            $query->whereIn('companies.id', $owner->companies->pluck('id'));
        })->where('role', 'auxiliar')->get();

        return view('auxiliares.index', compact('auxiliares', 'owner'));
    }

    public function create()
    {
        $owner = auth()->user();
        
        // Contamos cuántos auxiliares ya tiene registrados
        $currentAuxiliares = User::whereHas('assignedCompanies', function ($query) use ($owner) {
            $query->whereIn('companies.id', $owner->companies->pluck('id'));
        })->where('role', 'auxiliar')->count();

        // Validamos el límite de su licencia
        if ($currentAuxiliares >= $owner->max_auxiliares) {
            return redirect()->route('auxiliares.index')->with('error', 'Has alcanzado el límite de auxiliares permitidos en tu plan.');
        }

        // Le pasamos sus empresas para que elija a cuál darle acceso a la cajera
        $empresas = $owner->companies;
        return view('auxiliares.create', compact('empresas'));
    }

    public function store(Request $request)
    {
        $owner = auth()->user();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'company_id' => ['required', 'exists:companies,id'],
        ]);

        // Verificamos que la empresa seleccionada realmente pertenezca al dueño
        $company = Company::where('id', $request->company_id)->where('user_id', $owner->id)->firstOrFail();

        // 1. Creamos al usuario con el rol de auxiliar
        $auxiliar = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'auxiliar',
        ]);

        // 2. Lo conectamos a la empresa a través de la tabla pivote
        $auxiliar->assignedCompanies()->attach($company->id);

        return redirect()->route('auxiliares.index')->with('success', 'Auxiliar creado exitosamente y asignado a la empresa.');
    }
}