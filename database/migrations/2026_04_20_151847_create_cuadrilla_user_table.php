<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cuadrilla_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cuadrilla_id')->constrained('cuadrillas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('fecha_asignacion')->default(now());
            $table->timestamps();
            
            // Evita que un usuario se asigne dos veces a la misma cuadrilla el mismo día
            $table->unique(['cuadrilla_id', 'user_id', 'fecha_asignacion']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuadrilla_user');
    }
};