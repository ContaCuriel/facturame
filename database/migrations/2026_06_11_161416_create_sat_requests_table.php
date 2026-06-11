<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sat_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('request_id')->unique()->comment('El ticket que nos da el SAT');
            $table->string('type')->default('gastos'); // Para saber si pedimos gastos o ingresos
            $table->string('status')->default('pending'); // pending, accepted, delayed, downloaded, failed
            $table->dateTime('fecha_inicio');
            $table->dateTime('fecha_fin');
            $table->text('mensaje_sat')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sat_requests');
    }
};