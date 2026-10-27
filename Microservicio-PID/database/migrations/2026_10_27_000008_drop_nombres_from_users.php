<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const CONNECTION = 'pgsql';
    private const USERS = 'autenticacion.users';

    public function up(): void
    {
        Schema::connection(self::CONNECTION)->table(self::USERS, function (Blueprint $table) {
            $table->dropColumn('nombres');
        });
    }

    public function down(): void
    {
        Schema::connection(self::CONNECTION)->table(self::USERS, function (Blueprint $table) {
            $table->string('nombres')->nullable()->after('id');
        });
    }
};