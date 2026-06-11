<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gastos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('uuid')->unique()->comment('Folio Fiscal del Gasto');
            $table->string('rfc_emisor');
            $table->string('nombre_emisor')->nullable();
            $table->dateTime('fecha_emision');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('impuestos_trasladados', 12, 2)->default(0);
            $table->decimal('impuestos_retenidos', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->string('uso_cfdi')->nullable();
            $table->string('metodo_pago')->nullable(); // PUE, PPD
            $table->string('forma_pago')->nullable();
            $table->string('estado')->default('Vigente'); // Vigente, Cancelado
            $table->string('xml_path')->nullable()->comment('Ruta donde guardaremos el XML físico');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gastos');
    }
};