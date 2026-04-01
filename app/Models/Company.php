<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

class Company extends Model
{
    use HasFactory;

    /**
     * Los atributos que se pueden asignar en masa.
     */
    protected $fillable = [
        'name',
        'logo_path',
        'rfc',
        'fiscal_regime',
        'zip_code', // <-- LA COMA FALTABA AQUÍ
        'csd_cer_path',
        'csd_key_path',
        'csd_password',
    ];

    /**
     * Encripta la contraseña del CSD antes de guardarla.
     */
    protected function setCsdPasswordAttribute($value)
    {
        $this->attributes['csd_password'] = Crypt::encryptString($value);
    }

    /**
     * Define la relación donde una Empresa puede tener muchos Clientes.
     */
    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    /**
     * Define la relación donde una Empresa puede tener muchos Productos.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }
}