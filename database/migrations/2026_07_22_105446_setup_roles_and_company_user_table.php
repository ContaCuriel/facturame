<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Agregamos los superpoderes y límites a la tabla de usuarios
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('owner')->after('password'); // Roles: superadmin, owner, auxiliar
            $table->string('plan')->nullable()->after('role');           // Ej. 'Instituto Pro'
            $table->integer('max_empresas')->default(1)->after('plan');  
            $table->integer('max_auxiliares')->default(0)->after('max_empresas');
            $table->timestamp('expires_at')->nullable()->after('max_auxiliares');
        });

        // 2. Creamos la tabla pivote para asignar empresas a múltiples usuarios
        Schema::create('company_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_user');
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'plan', 'max_empresas', 'max_auxiliares', 'expires_at']);
        });
    }
};