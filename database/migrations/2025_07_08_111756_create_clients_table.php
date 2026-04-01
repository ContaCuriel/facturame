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
    Schema::create('clients', function (Blueprint $table) {
        $table->id();
        $table->foreignId('company_id')->constrained()->onDelete('cascade');

        // --- Campos Obligatorios ---
        $table->string('name');             // Razón Social
        $table->string('rfc', 13);
        $table->string('fiscal_regime');
        $table->string('zip_code', 5);

        // --- Campos Opcionales ---
        $table->string('commercial_name')->nullable();
        $table->string('address')->nullable();
        $table->boolean('print_address')->default(false);
        $table->string('payment_method')->nullable();      // PUE, PPD
        $table->string('payment_form')->nullable();        // 01, 03, 99...
        $table->string('cfdi_use')->nullable();            // G01, G03, S01...
        $table->string('email')->nullable();
        $table->string('email_cc')->nullable();
        $table->boolean('is_foreign')->default(false);
        $table->string('tax_residence')->nullable();       // País de residencia
        $table->string('tax_id_registration')->nullable(); // ID de registro tributario
        $table->timestamps();

        // El RFC debe ser único por cada empresa
        $table->unique(['company_id', 'rfc']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
