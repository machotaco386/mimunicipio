<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reportes', function (Blueprint $table) {
            $table->foreignId('area_id')->nullable()->constrained('areas')->nullOnDelete();
            $table->foreignId('cuadrilla_id')->nullable()->constrained('cuadrillas')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('reportes', function (Blueprint $table) {
            $table->dropForeign(['area_id']);
            $table->dropForeign(['cuadrilla_id']);
            $table->dropColumn(['area_id', 'cuadrilla_id']);
        });
    }
};