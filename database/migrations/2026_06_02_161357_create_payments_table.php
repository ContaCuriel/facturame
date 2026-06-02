<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Creamos la tabla para llevar el control de los abonos
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            
            $table->dateTime('payment_date');
            $table->string('payment_form', 10);
            $table->decimal('amount', 12, 2);
            $table->integer('installment_number'); // Parcialidad 1, 2, 3...
            $table->decimal('previous_balance', 12, 2); // Saldo anterior
            $table->decimal('outstanding_balance', 12, 2); // Saldo restante
            
            $table->string('facturama_id')->nullable();
            $table->string('uuid')->nullable();
            $table->enum('status', ['issued', 'cancelled'])->default('issued');
            
            $table->timestamps();
        });

        // 2. Le agregamos el método de pago a la tabla de facturas que ya tenías
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('payment_method', 10)->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('payment_method');
        });
        Schema::dropIfExists('payments');
    }
};