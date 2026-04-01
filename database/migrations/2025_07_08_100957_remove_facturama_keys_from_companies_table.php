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
    Schema::table('companies', function (Blueprint $table) {
        $table->dropColumn(['facturama_api_key', 'facturama_secret_key']);
    });
}

public function down(): void
{
    Schema::table('companies', function (Blueprint $table) {
        $table->text('facturama_api_key')->nullable();
        $table->text('facturama_secret_key')->nullable();
    });
}
};
