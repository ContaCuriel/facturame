<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'facturama_id',
        'company_id', 
        'client_id', 
        'uuid', 
        'folio', 
        'series', 
        'subtotal', 
        'taxes', 
        'total', 
        'status', // draft, issued, cancelled
        'items',
        'payment_method',
    ];

    protected $casts = [
        'items' => 'array', 
        'subtotal' => 'decimal:2',
        'taxes' => 'decimal:2', 
        'total' => 'decimal:2',
    ];

    // 🧠 Traductor automático de estados para la interfaz
    public function getStatusEsAttribute(): string
    {
        return match($this->status) {
            'draft' => 'Borrador',
            'issued' => 'Timbrada',
            'cancelled' => 'Cancelada',
            default => 'Desconocido',
        };
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}