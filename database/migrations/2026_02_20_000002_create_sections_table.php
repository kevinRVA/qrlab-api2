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
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade'); // Enlaza con users
            $table->string('section_code'); // Ej. 01, 02
            $table->string('schedule'); // Ej. Lunes 08:00 - 10:00
            $table->timestamps();

            // Evitar que un profe dé la misma materia en la misma sección dos veces
            $table->unique(['subject_id', 'teacher_id', 'section_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};
