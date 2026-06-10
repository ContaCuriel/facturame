<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            // Archivos y contraseña encriptada de la e.firma (FIEL)
            $table->string('fiel_cer_path')->nullable()->after('csd_password');
            $table->string('fiel_key_path')->nullable()->after('fiel_cer_path');
            $table->text('fiel_password')->nullable()->after('fiel_key_path');
            
            // Fechas de caducidad automáticas para el CSD y la FIEL
            $table->dateTime('csd_expires_at')->nullable()->after('fiel_password');
            $table->dateTime('fiel_expires_at')->nullable()->after('csd_expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'fiel_cer_path',
                'fiel_key_path',
                'fiel_password',
                'csd_expires_at',
                'fiel_expires_at'
            ]);
        });
    }
};