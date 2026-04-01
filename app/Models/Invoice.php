<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'facturama_id', // <-- Añadido
        'company_id', 'client_id', 'uuid', 'folio', 'series', 
        'subtotal', 'taxes', 'total', 'status', 'items',
    ];

    protected $casts = [
        'items' => 'array', 'subtotal' => 'decimal:2',
        'taxes' => 'decimal:2', 'total' => 'decimal:2',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
