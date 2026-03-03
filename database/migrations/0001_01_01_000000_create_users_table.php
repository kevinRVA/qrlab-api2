<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            // --- NUEVOS CAMPOS PARA QRLAB ---
            $table->string('role')->default('student'); // Identifica si es 'admin', 'teacher' o 'student'
            $table->string('user_code')->unique()->nullable(); // Carné (ej. 2705632024) o Código (ej. DOC-7788)
            $table->string('career')->nullable(); // Ej. Ingeniería en Sistemas (solo estudiantes)
            // --------------------------------

            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // NOTA: Eliminamos la tabla 'sessions' de Laravel por defecto
        // para que no choque con nuestra tabla 'sessions' de las clases del laboratorio.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        // También quitamos el drop de sessions de aquí
    }
};