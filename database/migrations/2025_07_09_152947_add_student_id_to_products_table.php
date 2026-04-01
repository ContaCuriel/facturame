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
        Schema::table('products', function (Blueprint $table) {
            // Añadimos la columna para el ID del alumno, que puede ser nula.
            // Se enlaza a la tabla 'students' y se borra en cascada.
            $table->foreignId('student_id')->nullable()->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Esto es para poder revertir la migración si es necesario.
            $table->dropForeign(['student_id']);
            $table->dropColumn('student_id');
        });
    }
};