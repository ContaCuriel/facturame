<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdminController
{
    public function index()
    {
        // Traemos a todos los usuarios registrados, excepto a ti (el superadmin)
        $clientes = User::where('role', '!=', 'superadmin')
                        ->orderBy('created_at', 'desc')
                        ->get();

        return view('admin.index', compact('clientes'));
    }
}