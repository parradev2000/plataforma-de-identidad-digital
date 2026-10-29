<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const CONNECTION = 'pgsql';
    private const TABLE = 'nomencladores.personas';

    public function up(): void
    {
        Schema::connection(self::CONNECTION)->table(self::TABLE, function (Blueprint $table) {
            $table->string('id_digital')->unique()->nullable();
            $table->string('usuario')->nullable();
            $table->string('contrasena')->nullable();
        });
    }

    public function down(): void
    {
        Schema::connection(self::CONNECTION)->table(self::TABLE, function (Blueprint $table) {
            $table->dropUnique(['id_digital']);
            $table->dropColumn(['id_digital', 'usuario', 'contrasena']);
        });
    }
};
