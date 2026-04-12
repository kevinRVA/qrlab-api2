<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Agrega el campo qr_token a la tabla laboratories
     * para el sistema de asistencia voluntaria con QR estático.
     */
    public function up(): void
    {
        Schema::table('laboratories', function (Blueprint $table) {
            $table->string('qr_token')->unique()->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('laboratories', function (Blueprint $table) {
            $table->dropColumn('qr_token');
        });
    }
};
