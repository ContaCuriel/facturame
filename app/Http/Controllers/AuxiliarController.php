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
        
        // Obtenemos a TODOS los usuarios invitados a las empresas de este dueño
        // (Sin importar si son dueños de sus propios despachos)
        $auxiliares = User::whereHas('assignedCompanies', function ($query) use ($owner) {
            $query->whereIn('companies.id', $owner->companies->pluck('id'));
        })->where('id', '!=', $owner->id)->get();

        return view('auxiliares.index', compact('auxiliares', 'owner'));
    }

    public function create()
    {
        $owner = auth()->user();
        
        // Contamos cuántos invitados ya tiene registrados
        $currentAuxiliares = User::whereHas('assignedCompanies', function ($query) use ($owner) {
            $query->whereIn('companies.id', $owner->companies->pluck('id'));
        })->where('id', '!=', $owner->id)->count();

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

        // 1. Primero, solo validamos el correo y las empresas
        $request->validate([
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'companies' => ['required', 'array', 'min:1'],
            'companies.*' => ['exists:companies,id'],
        ]);

        // Verificamos que las empresas seleccionadas realmente pertenezcan al dueño que está invitando
        $validCompanies = \App\Models\Company::whereIn('id', $request->companies)
                                 ->where('user_id', $owner->id)
                                 ->pluck('id');

        if ($validCompanies->count() !== count($request->companies)) {
            return back()->withErrors(['companies' => 'Una o más empresas seleccionadas no son válidas.'])->withInput();
        }

        // 2. Buscamos si el usuario ya está registrado en el ERP
        $auxiliar = User::where('email', $request->email)->first();

        if ($auxiliar) {
            // EL USUARIO YA EXISTE (Puede ser dueño de otro despacho o auxiliar de alguien más)
            // Usamos syncWithoutDetaching para agregarle estas nuevas empresas sin borrarle las que ya tenía.
            $auxiliar->assignedCompanies()->syncWithoutDetaching($validCompanies);

            return redirect()->route('auxiliares.index')
                             ->with('success', 'El usuario ya tenía una cuenta en el sistema y ha sido vinculado exitosamente a tus empresas.');
        } 
        
        // 3. EL USUARIO NO EXISTE EN EL SISTEMA
        // Ahora sí validamos que haya escrito nombre y contraseña para crearlo desde cero
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
        ]);

        // Creamos su cuenta
        $nuevoAuxiliar = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
            'role' => 'auxiliar',
        ]);

        // Lo conectamos a las empresas
        $nuevoAuxiliar->assignedCompanies()->attach($validCompanies);

        return redirect()->route('auxiliares.index')
                         ->with('success', 'Auxiliar creado exitosamente y asignado a las empresas.');
    }
}