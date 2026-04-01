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
    Schema::create('invoices', function (Blueprint $table) {
        $table->id();
        $table->foreignId('company_id')->constrained()->onDelete('cascade');
        $table->foreignId('client_id')->constrained()->onDelete('cascade');
        $table->string('uuid')->unique()->comment('Folio Fiscal (UUID) devuelto por Facturama');
        $table->string('folio')->nullable()->comment('Folio interno de la factura (ej. F-001)');
        $table->string('series')->nullable()->comment('Serie interna (ej. F)');
        $table->decimal('subtotal', 10, 2);
        $table->decimal('taxes', 10, 2);
        $table->decimal('total', 10, 2);
        $table->string('status')->default('issued')->comment('issued, cancelled');
        $table->string('pdf_path')->nullable(); // Ruta al PDF guardado
        $table->string('xml_path')->nullable(); // Ruta al XML guardado
        $table->json('items')->comment('Un JSON con los productos/conceptos de la factura');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
