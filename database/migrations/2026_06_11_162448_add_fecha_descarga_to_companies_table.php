<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->date('fecha_inicio_descarga_gastos')->nullable()->comment('Fecha desde la cual se descargará el histórico del SAT');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('fecha_inicio_descarga_gastos');
        });
    }
};