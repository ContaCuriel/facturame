<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'commercial_name',
        'logo_path',
        'rfc',
        'fiscal_regime',
        'zip_code',
        'csd_cer_path',
        'csd_key_path',
        'csd_password',
        // Nuevos campos para e.firma y caducidades
        'fiel_cer_path',
        'fiel_key_path',
        'fiel_password',
        'csd_expires_at',
        'fiel_expires_at',
        'fecha_inicio_descarga_gastos',
    ];

    // AQUÍ ESTÁN LOS CASTS NUEVOS
    protected $casts = [
        'csd_expires_at' => 'datetime',
        'fiel_expires_at' => 'datetime',
        'fecha_inicio_descarga_gastos' => 'date',
    ];

    // Encriptación automática de contraseñas al guardarse en la BD
    protected function setCsdPasswordAttribute($value)
    {
        $this->attributes['csd_password'] = $value ? Crypt::encryptString($value) : null;
    }

    protected function setFielPasswordAttribute($value)
    {
        $this->attributes['fiel_password'] = $value ? Crypt::encryptString($value) : null;
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }
}