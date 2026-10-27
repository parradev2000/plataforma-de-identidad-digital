<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const CONNECTION = 'pgsql';
    private const ROLES = 'autenticacion.rols';

    public function up(): void
    {
        Schema::connection(self::CONNECTION)->table(self::ROLES, function (Blueprint $table) {
            $table->string('name')->nullable()->after('rol');
        });

        DB::connection(self::CONNECTION)->statement('UPDATE autenticacion.rols SET name = LOWER(rol) WHERE name IS NULL');
        DB::connection(self::CONNECTION)->statement('ALTER TABLE autenticacion.rols ALTER COLUMN name SET NOT NULL');
        DB::connection(self::CONNECTION)->statement('CREATE UNIQUE INDEX IF NOT EXISTS roles_name_guard_name_unique ON autenticacion.rols (name, guard_name)');
    }

    public function down(): void
    {
        DB::connection(self::CONNECTION)->statement('DROP INDEX IF EXISTS autenticacion.roles_name_guard_name_unique');
        Schema::connection(self::CONNECTION)->table(self::ROLES, function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }
};