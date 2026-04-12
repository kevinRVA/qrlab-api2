<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Tabla de visitas voluntarias a laboratorios.
     * Cada fila representa una visita de un estudiante a un lab en un día.
     */
    public function up(): void
    {
        Schema::create('lab_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laboratory_id')->constrained('laboratories')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('entry_time');                          // Primer escaneo
            $table->timestamp('exit_time')->nullable();               // Segundo escaneo (null si no marcó salida)
            $table->boolean('auto_closed')->default(false);           // Cerrado por el cron
            $table->boolean('no_exit_warning')->default(false);       // Flag: no marcó salida
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_visits');
    }
};
