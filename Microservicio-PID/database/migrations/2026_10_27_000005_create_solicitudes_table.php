<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const CONNECTION = 'pgsql';
    private const TABLE = 'datos.solicitudes';

    public function up(): void
    {
        Schema::connection(self::CONNECTION)->create(self::TABLE, function (Blueprint $table) {
            $table->id();
            $table->foreignId('estado_id')
                ->constrained('nomencladores.estados')
                ->onDelete('restrict');
            $table->foreignId('servicio_id')
                ->constrained('nomencladores.servicios')
                ->onDelete('restrict');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection(self::CONNECTION)->dropIfExists(self::TABLE);
    }
};