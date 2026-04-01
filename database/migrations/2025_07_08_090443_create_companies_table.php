<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('companies', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Enlaza con el usuario dueño
        $table->string('name');         // Razón Social
        $table->string('rfc', 13)->unique(); // RFC único de 13 caracteres
        $table->string('fiscal_regime'); // Clave del Régimen Fiscal del SAT
        $table->string('zip_code', 5);   // Código Postal del domicilio fiscal
        // Aquí guardaremos las credenciales de Facturama para esta empresa
        $table->text('facturama_api_key')->nullable();
        $table->text('facturama_secret_key')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
