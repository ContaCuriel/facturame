<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdminController
{
    public function index()
    {
        $clientes = User::where('role', '!=', 'superadmin')
                        ->orderBy('created_at', 'desc')
                        ->get();

        return view('admin.index', compact('clientes'));
    }

    // 1. Muestra el formulario para editar a un cliente
    public function edit(User $user)
    {
        return view('admin.edit', compact('user'));
    }

    // 2. Guarda los cambios en la base de datos
    public function update(Request $request, User $user)
    {
        $request->validate([
            'plan' => 'nullable|string|max:255',
            'max_empresas' => 'required|integer|min:1',
            'max_auxiliares' => 'required|integer|min:0',
            'expires_at' => 'nullable|date',
        ]);

        $user->update($request->only(['plan', 'max_empresas', 'max_auxiliares', 'expires_at']));

        return redirect()->route('admin.panel')->with('success', 'Licencia actualizada correctamente.');
    }
}