<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Se asume que el campo 'rol' ya existe en tu tabla original. 
            // Si es un enum y da problemas en SQLite, lo dejamos sin modificar o lo tratamos como string.
            // Añadimos la relación de área.
            $table->foreignId('area_id')->nullable()->constrained('areas')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['area_id']);
            $table->dropColumn('area_id');
        });
    }
};