<?php

// --------------------------------------------------------------------
// ARCHIVO 1: La nueva migración que acabas de crear
// RUTA: database/migrations/xxxx_xx_xx_xxxxxx_add_facturama_id_to_invoices_table.php
// --------------------------------------------------------------------

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
        Schema::table('invoices', function (Blueprint $table) {
            // Añadimos la columna para el ID interno de Facturama
            $table->string('facturama_id')->after('id')->comment('ID interno de Facturama');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('facturama_id');
        });
    }
};