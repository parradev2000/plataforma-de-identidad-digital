<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const CONNECTION = 'pgsql';
    private const TABLE = 'nomencladores.estados';

    public function up(): void
    {
        Schema::connection(self::CONNECTION)->create(self::TABLE, function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection(self::CONNECTION)->dropIfExists(self::TABLE);
    }
};