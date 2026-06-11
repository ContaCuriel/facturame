<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SatRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id', 'request_id', 'type', 'status', 
        'fecha_inicio', 'fecha_fin', 'mensaje_sat'
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}