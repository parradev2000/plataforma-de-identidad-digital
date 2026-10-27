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
        DB::connection(self::CONNECTION)->statement('UPDATE autenticacion.rols SET name = LOWER(COALESCE(name, rol)) WHERE name IS NULL OR name = \'\'');
        Schema::connection(self::CONNECTION)->table(self::ROLES, function (Blueprint $table) {
            $table->dropColumn('rol');
        });
    }

    public function down(): void
    {
        Schema::connection(self::CONNECTION)->table(self::ROLES, function (Blueprint $table) {
            $table->string('rol')->nullable()->after('id');
        });
        DB::connection(self::CONNECTION)->statement('UPDATE autenticacion.rols SET rol = LOWER(name)');
        DB::connection(self::CONNECTION)->statement('ALTER TABLE autenticacion.rols ALTER COLUMN rol SET NOT NULL');
    }
};