<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id', 'name', 'rfc', 'commercial_name', 'email', 'email_cc', 'fiscal_regime', 'zip_code', 'address', 'print_address', 'payment_method', 'payment_form', 'cfdi_use', 'is_foreign', 'tax_residence', 'tax_id_registration',
    ];

    protected $casts = [
        'is_foreign' => 'boolean',
        'print_address' => 'boolean',
    ];

public function company()
{
    return $this->belongsTo(Company::class);
}


}