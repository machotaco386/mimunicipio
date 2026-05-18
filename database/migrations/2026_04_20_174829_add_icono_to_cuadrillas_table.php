<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cuadrillas', function (Blueprint $table) {
            // Añadimos el campo icono con uno por defecto (Grupo de personas)
            $table->string('icono')->default('ph-users-three')->after('nombre');
        });
    }

    public function down(): void
    {
        Schema::table('cuadrillas', function (Blueprint $table) {
            $table->dropColumn('icono');
        });
    }
};