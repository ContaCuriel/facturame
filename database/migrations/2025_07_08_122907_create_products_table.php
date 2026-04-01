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
    Schema::create('products', function (Blueprint $table) {
        $table->id();
        $table->foreignId('company_id')->constrained()->onDelete('cascade');
        $table->string('description'); // Descripción del producto/servicio
        $table->string('sku')->nullable()->comment('Número de parte o SKU interno');
        $table->decimal('price', 10, 2); // Precio unitario
        $table->string('sat_product_key'); // Clave de Producto/Servicio del SAT
        $table->string('sat_unit_key');    // Clave de Unidad del SAT (ej. E48, H87)
        $table->boolean('taxes')->default(true)->comment('Indica si el producto lleva impuestos');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
