<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const CONNECTION = 'pgsql';
    private const TABLE = 'datos.solicitudes';

    public function up(): void
    {
        Schema::connection(self::CONNECTION)->table(self::TABLE, function (Blueprint $table) {
            $table->foreignId('persona_id')->nullable()->after('id');
        });
        DB::connection(self::CONNECTION)->statement("
            ALTER TABLE datos.solicitudes ADD CONSTRAINT fk_solicitudes_persona FOREIGN KEY (persona_id) REFERENCES nomencladores.personas(id) ON DELETE RESTRICT
        ");
        DB::connection(self::CONNECTION)->statement('CREATE UNIQUE INDEX IF NOT EXISTS uq_solicitudes_persona_id ON datos.solicitudes (persona_id)');
    }

    public function down(): void
    {
        DB::connection(self::CONNECTION)->statement('DROP INDEX IF EXISTS datos.uq_solicitudes_persona_id');
        DB::connection(self::CONNECTION)->statement('ALTER TABLE datos.solicitudes DROP CONSTRAINT IF EXISTS fk_solicitudes_persona');
        Schema::connection(self::CONNECTION)->table(self::TABLE, function (Blueprint $table) {
            $table->dropColumn('persona_id');
        });
    }
};