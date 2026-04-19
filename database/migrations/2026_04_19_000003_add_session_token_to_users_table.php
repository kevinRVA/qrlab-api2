<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Columna para la sesión activa única por usuario.
     * Cuando un usuario inicia sesión en un nuevo dispositivo, se regenera
     * este token, invalidando todas las sesiones anteriores.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('session_token')->nullable()->after('remember_token');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('session_token');
        });
    }
};
