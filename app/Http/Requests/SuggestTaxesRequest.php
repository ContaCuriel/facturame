<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SuggestTaxesRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para hacer esta petición.
     */
    public function authorize(): bool
    {
        // Permitimos que cualquiera que llegue aquí pueda hacer la petición,
        // ya que la autorización real se hace en el controlador.
        return true;
    }

    /**
     * Obtiene las reglas de validación que aplican a la petición.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'company_regime' => 'required|string',
            'client_regime' => 'required|string',
            'product_description' => 'nullable|string',
            'product_sat_key' => 'nullable|string',
        ];
    }
}