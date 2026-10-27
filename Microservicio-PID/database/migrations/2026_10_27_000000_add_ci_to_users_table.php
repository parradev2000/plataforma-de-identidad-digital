<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const CONNECTION = 'pgsql';
    private const USERS = 'autenticacion.users';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1) Columna opcional para poder rellenar los usuarios existentes
        Schema::connection(self::CONNECTION)->table(self::USERS, function (Blueprint $table) {
            $table->string('ci', 11)->nullable();
        });

        // 2) Cada usuario existente recibe un CI de 11 dígitos único
        DB::connection(self::CONNECTION)->statement(
            "UPDATE autenticacion.users SET ci = LPAD(id::text, 11, '0') WHERE ci IS NULL"
        );

        // 3) El CI pasa a ser obligatorio, único y de 11 dígitos exactos
        DB::connection(self::CONNECTION)->statement(
            'ALTER TABLE autenticacion.users ALTER COLUMN ci SET NOT NULL'
        );
        DB::connection(self::CONNECTION)->statement(
            'ALTER TABLE autenticacion.users ADD CONSTRAINT uq_users_ci UNIQUE (ci)'
        );
        DB::connection(self::CONNECTION)->statement(
            "ALTER TABLE autenticacion.users ADD CONSTRAINT ck_users_ci_digits CHECK (ci ~ '^[0-9]{11}$')"
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::connection(self::CONNECTION)->statement(
            'ALTER TABLE autenticacion.users DROP CONSTRAINT IF EXISTS ck_users_ci_digits'
        );
        DB::connection(self::CONNECTION)->statement(
            'ALTER TABLE autenticacion.users DROP CONSTRAINT IF EXISTS uq_users_ci'
        );
        DB::connection(self::CONNECTION)->statement(
            'ALTER TABLE autenticacion.users DROP COLUMN IF EXISTS ci'
        );
    }
};