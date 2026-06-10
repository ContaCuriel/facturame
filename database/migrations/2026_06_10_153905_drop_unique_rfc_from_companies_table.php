<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            // Le decimos a la base de datos que quite la regla de "único" para el RFC
            // Laravel usa la sintaxis de arreglo para autodetectar el nombre del índice
            $table->dropUnique(['rfc']);
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            // Si nos arrepentimos, lo volvemos a hacer único
            $table->unique('rfc');
        });
    }
};