<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const CONNECTION = 'pgsql';
    private const USERS = 'autenticacion.users';
    private const PERSONA = 'nomencladores.persona';

    public function up(): void
    {
        // Crear personas para usuarios existentes con su ci
        DB::connection(self::CONNECTION)->statement("
            INSERT INTO nomencladores.persona (nombres, apellidos, ci, created_at, updated_at)
            SELECT u.nombres, COALESCE(NULLIF(split_part(u.nombres, ' ', 2), ''), u.usuario) as apellidos, u.ci, NOW(), NOW()
            FROM autenticacion.users u
            WHERE u.ci IS NOT NULL AND NOT EXISTS (SELECT 1 FROM nomencladores.persona p WHERE p.ci = u.ci)
        ");

        Schema::connection(self::CONNECTION)->table(self::USERS, function (Blueprint $table) {
            $table->foreignId('persona_id')->nullable()->after('id');
        });

        DB::connection(self::CONNECTION)->statement("
            UPDATE autenticacion.users u SET persona_id = p.id
            FROM nomencladores.persona p WHERE p.ci = u.ci
        ");

        DB::connection(self::CONNECTION)->statement("
            ALTER TABLE autenticacion.users ADD CONSTRAINT fk_users_persona FOREIGN KEY (persona_id) REFERENCES nomencladores.persona(id) ON DELETE RESTRICT
        ");

        DB::connection(self::CONNECTION)->statement('ALTER TABLE autenticacion.users ALTER COLUMN persona_id SET NOT NULL');
        DB::connection(self::CONNECTION)->statement('CREATE UNIQUE INDEX IF NOT EXISTS uq_users_persona_id ON autenticacion.users (persona_id)');

        DB::connection(self::CONNECTION)->statement('ALTER TABLE autenticacion.users DROP CONSTRAINT IF EXISTS uq_users_ci');
        DB::connection(self::CONNECTION)->statement('ALTER TABLE autenticacion.users DROP CONSTRAINT IF EXISTS ck_users_ci_digits');
        Schema::connection(self::CONNECTION)->table(self::USERS, function (Blueprint $table) {
            $table->dropColumn('ci');
        });
    }

    public function down(): void
    {
        Schema::connection(self::CONNECTION)->table(self::USERS, function (Blueprint $table) {
            $table->string('ci', 11)->nullable();
        });
        DB::connection(self::CONNECTION)->statement("
            UPDATE autenticacion.users u SET ci = p.ci
            FROM nomencladores.persona p WHERE p.id = u.persona_id
        ");
        DB::connection(self::CONNECTION)->statement('ALTER TABLE autenticacion.users ALTER COLUMN ci SET NOT NULL');
        DB::connection(self::CONNECTION)->statement("ALTER TABLE autenticacion.users ADD CONSTRAINT ck_users_ci_digits CHECK (ci ~ '^[0-9]{11}$')");
        DB::connection(self::CONNECTION)->statement('ALTER TABLE autenticacion.users ADD CONSTRAINT uq_users_ci UNIQUE (ci)');

        DB::connection(self::CONNECTION)->statement('DROP INDEX IF EXISTS autenticacion.uq_users_persona_id');
        DB::connection(self::CONNECTION)->statement('ALTER TABLE autenticacion.users DROP CONSTRAINT IF EXISTS fk_users_persona');
        Schema::connection(self::CONNECTION)->table(self::USERS, function (Blueprint $table) {
            $table->dropColumn('persona_id');
        });
    }
};