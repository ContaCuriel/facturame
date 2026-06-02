<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    // Los campos que permitimos guardar masivamente
    protected $fillable = [
        'invoice_id', 'payment_date', 'payment_form', 'amount',
        'installment_number', 'previous_balance', 'outstanding_balance',
        'facturama_id', 'uuid', 'status'
    ];

    // Aseguramos que las fechas y decimales se traten correctamente para hacer sumas matemáticas
    protected $casts = [
        'payment_date' => 'datetime',
        'amount' => 'decimal:2',
        'previous_balance' => 'decimal:2',
        'outstanding_balance' => 'decimal:2',
    ];

    /**
     * Relación: Un pago pertenece a una factura específica.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}