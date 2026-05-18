<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reportes', function (Blueprint $table) {
            $table->id();
            $table->string('folio', 20)->unique();
            $table->foreignId('municipio_id')->constrained('municipios')->cascadeOnDelete();
            $table->decimal('latitud', 10, 8);
            $table->decimal('longitud', 11, 8);
            $table->text('descripcion');
            $table->enum('categoria', ['Bache', 'Luz', 'Basura', 'Fuga de agua', 'Drenaje']);
            $table->string('ruta_foto')->nullable();
            $table->enum('estado', ['Pendiente', 'En progreso', 'Resuelto'])->default('Pendiente');
            $table->string('telefono_contacto', 20)->nullable();
            $table->timestamps();
            
            // Índices de optimización
            $table->index('folio');
            $table->index(['municipio_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reportes');
    }
};