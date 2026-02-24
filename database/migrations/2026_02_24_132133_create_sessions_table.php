<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('sessions', function (Blueprint $table) {
            $table->id();
            $table->string('teacher_name');
            $table->string('teacher_code');
            $table->string('subject');
            $table->string('section');
            $table->uuid('qr_token')->unique(); // El código único que se convertirá en QR
            $table->boolean('is_active')->default(true); // Controla si la clase sigue abierta
            $table->timestamps(); // Guarda created_at (Hora de entrada) y updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
    }
};
