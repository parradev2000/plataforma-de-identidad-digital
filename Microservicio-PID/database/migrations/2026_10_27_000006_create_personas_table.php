<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const CONNECTION = 'pgsql';
    private const TABLE = 'nomencladores.persona';

    public function up(): void
    {
        Schema::connection(self::CONNECTION)->create(self::TABLE, function (Blueprint $table) {
            $table->id();
            $table->string('nombres');
            $table->string('apellidos');
            $table->string('ci', 11)->unique();
            $table->timestamps();
        });
        DB::connection(self::CONNECTION)->statement("ALTER TABLE nomencladores.persona ADD CONSTRAINT ck_persona_ci_digits CHECK (ci ~ '^[0-9]{11}$')");
    }

    public function down(): void
    {
        Schema::connection(self::CONNECTION)->dropIfExists(self::TABLE);
    }
};