<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id', 'description', 'sku', 'price', 'sat_product_key', 'sat_unit_key', 'taxes',
        'student_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'taxes' => 'boolean',
    ];

    /**
     * Define la relación inversa: un Producto pertenece a una Empresa.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

     public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}

