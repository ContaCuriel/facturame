<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'curp',
        'education_level',
        'aut_rvoe',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
