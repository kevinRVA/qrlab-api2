<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Tabla pivot: coordinador ↔ laboratorios asignados.
     */
    public function up(): void
    {
        Schema::create('coordinator_labs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('laboratory_id')->constrained()->onDelete('cascade');
            $table->unique(['user_id', 'laboratory_id']); // Evita duplicados
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coordinator_labs');
    }
};
