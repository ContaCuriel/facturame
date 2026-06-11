<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Gasto extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id', 'uuid', 'rfc_emisor', 'nombre_emisor',
        'fecha_emision', 'subtotal', 'impuestos_trasladados',
        'impuestos_retenidos', 'total', 'uso_cfdi',
        'metodo_pago', 'forma_pago', 'estado', 'xml_path'
    ];

    protected $casts = [
        'fecha_emision' => 'datetime',
        'subtotal' => 'decimal:2',
        'impuestos_trasladados' => 'decimal:2',
        'impuestos_retenidos' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}