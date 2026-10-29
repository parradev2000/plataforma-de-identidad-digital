<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const CONNECTION = 'pgsql';

    public function up(): void
    {
        $db = DB::connection(self::CONNECTION);

        // Needed for GIN indexes that accelerate the API's LIKE '%term%' searches.
        $db->statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');

        $indexes = [
            'CREATE INDEX IF NOT EXISTS idx_pid_users_usuario ON autenticacion.users (usuario)',
            'CREATE INDEX IF NOT EXISTS idx_pid_users_rol_estado ON autenticacion.users (rol_id, estado)',
            'CREATE INDEX IF NOT EXISTS idx_pid_users_estado_usuario_id ON autenticacion.users (estado DESC, usuario ASC, id DESC)',
            'CREATE INDEX IF NOT EXISTS idx_pid_users_usuario_trgm ON autenticacion.users USING gin (LOWER(usuario) gin_trgm_ops)',
            'CREATE INDEX IF NOT EXISTS idx_pid_roles_estado_id ON autenticacion.rols (estado, id DESC)',
            'CREATE INDEX IF NOT EXISTS idx_pid_roles_name_lower ON autenticacion.rols (LOWER(name))',
            'CREATE INDEX IF NOT EXISTS idx_pid_roles_name_trgm ON autenticacion.rols USING gin (LOWER(name) gin_trgm_ops)',
            'CREATE INDEX IF NOT EXISTS idx_pid_solicitudes_estado_id ON datos.solicitudes (estado_id, id DESC)',
            'CREATE INDEX IF NOT EXISTS idx_pid_solicitudes_servicio_id ON datos.solicitudes (servicio_id, id DESC)',
            'CREATE INDEX IF NOT EXISTS idx_pid_estados_nombre_trgm ON nomencladores.estados USING gin (LOWER(nombre) gin_trgm_ops)',
        ];

        foreach ($indexes as $statement) {
            $db->statement($statement);
        }

        // These tables are supplied by the shared PID database rather than created here.
        $optionalIndexes = [
            ['autenticacion', 'permissions', [
                'CREATE INDEX IF NOT EXISTS idx_pid_permissions_name_trgm ON autenticacion.permissions USING gin (LOWER(name) gin_trgm_ops)',
                'CREATE INDEX IF NOT EXISTS idx_pid_permissions_guard_name ON autenticacion.permissions (guard_name, id DESC)',
            ]],
            ['autenticacion', 'tokens', [
                'CREATE INDEX IF NOT EXISTS idx_pid_tokens_token ON autenticacion.tokens (token)',
                'CREATE INDEX IF NOT EXISTS idx_pid_tokens_user_dispositivo ON autenticacion.tokens (user_id, dispositivo)',
            ]],
            ['nomencladores', 'servicios', [
                'CREATE INDEX IF NOT EXISTS idx_pid_servicios_nombre_trgm ON nomencladores.servicios USING gin (LOWER(nombre) gin_trgm_ops)',
            ]],
            ['autenticacion', 'servicios', [
                'CREATE INDEX IF NOT EXISTS idx_pid_auth_servicios_nombre_trgm ON autenticacion.servicios USING gin (LOWER(nombre) gin_trgm_ops)',
            ]],
            ['nomencladores', 'persona', [
                'CREATE INDEX IF NOT EXISTS idx_pid_persona_ci_trgm ON nomencladores.persona USING gin (ci gin_trgm_ops)',
                'CREATE INDEX IF NOT EXISTS idx_pid_persona_nombres_trgm ON nomencladores.persona USING gin (LOWER(nombres) gin_trgm_ops)',
                'CREATE INDEX IF NOT EXISTS idx_pid_persona_apellidos_trgm ON nomencladores.persona USING gin (LOWER(apellidos) gin_trgm_ops)',
                'CREATE INDEX IF NOT EXISTS idx_pid_persona_nombres_apellidos ON nomencladores.persona (nombres, apellidos)',
            ]],
            ['nomencladores', 'personas', [
                'CREATE INDEX IF NOT EXISTS idx_pid_personas_ci_trgm ON nomencladores.personas USING gin (ci gin_trgm_ops)',
                'CREATE INDEX IF NOT EXISTS idx_pid_personas_nombres_trgm ON nomencladores.personas USING gin (LOWER(nombres) gin_trgm_ops)',
                'CREATE INDEX IF NOT EXISTS idx_pid_personas_apellidos_trgm ON nomencladores.personas USING gin (LOWER(apellidos) gin_trgm_ops)',
                'CREATE INDEX IF NOT EXISTS idx_pid_personas_nombres_apellidos ON nomencladores.personas (nombres, apellidos)',
            ]],
            ['auditoria', 'dat_auditorias', [
                'CREATE INDEX IF NOT EXISTS idx_pid_auditorias_created_at ON auditoria.dat_auditorias (created_at)',
                'CREATE INDEX IF NOT EXISTS idx_pid_auditorias_usuario_trgm ON auditoria.dat_auditorias USING gin (LOWER(usuario) gin_trgm_ops)',
                'CREATE INDEX IF NOT EXISTS idx_pid_auditorias_nombres_trgm ON auditoria.dat_auditorias USING gin (LOWER(nombres) gin_trgm_ops)',
                'CREATE INDEX IF NOT EXISTS idx_pid_auditorias_accion_trgm ON auditoria.dat_auditorias USING gin (LOWER(accion) gin_trgm_ops)',
            ]],
        ];

        foreach ($optionalIndexes as [$schema, $table, $statements]) {
            $exists = $db->selectOne(
                'SELECT 1 FROM information_schema.tables WHERE table_schema = ? AND table_name = ?',
                [$schema, $table],
            );

            if ($exists !== null) {
                foreach ($statements as $statement) {
                    $db->statement($statement);
                }
            }
        }
    }

    public function down(): void
    {
        $db = DB::connection(self::CONNECTION);

        $indexes = [
            'DROP INDEX IF EXISTS autenticacion.idx_pid_users_usuario',
            'DROP INDEX IF EXISTS autenticacion.idx_pid_users_rol_estado',
            'DROP INDEX IF EXISTS autenticacion.idx_pid_users_estado_usuario_id',
            'DROP INDEX IF EXISTS autenticacion.idx_pid_users_usuario_trgm',
            'DROP INDEX IF EXISTS autenticacion.idx_pid_roles_estado_id',
            'DROP INDEX IF EXISTS autenticacion.idx_pid_roles_name_lower',
            'DROP INDEX IF EXISTS autenticacion.idx_pid_roles_name_trgm',
            'DROP INDEX IF EXISTS datos.idx_pid_solicitudes_estado_id',
            'DROP INDEX IF EXISTS datos.idx_pid_solicitudes_servicio_id',
            'DROP INDEX IF EXISTS nomencladores.idx_pid_estados_nombre_trgm',
            'DROP INDEX IF EXISTS autenticacion.idx_pid_permissions_name_trgm',
            'DROP INDEX IF EXISTS autenticacion.idx_pid_permissions_guard_name',
            'DROP INDEX IF EXISTS autenticacion.idx_pid_tokens_token',
            'DROP INDEX IF EXISTS autenticacion.idx_pid_tokens_user_dispositivo',
            'DROP INDEX IF EXISTS nomencladores.idx_pid_servicios_nombre_trgm',
            'DROP INDEX IF EXISTS autenticacion.idx_pid_auth_servicios_nombre_trgm',
            'DROP INDEX IF EXISTS nomencladores.idx_pid_persona_ci_trgm',
            'DROP INDEX IF EXISTS nomencladores.idx_pid_persona_nombres_trgm',
            'DROP INDEX IF EXISTS nomencladores.idx_pid_persona_apellidos_trgm',
            'DROP INDEX IF EXISTS nomencladores.idx_pid_persona_nombres_apellidos',
            'DROP INDEX IF EXISTS nomencladores.idx_pid_personas_ci_trgm',
            'DROP INDEX IF EXISTS nomencladores.idx_pid_personas_nombres_trgm',
            'DROP INDEX IF EXISTS nomencladores.idx_pid_personas_apellidos_trgm',
            'DROP INDEX IF EXISTS nomencladores.idx_pid_personas_nombres_apellidos',
            'DROP INDEX IF EXISTS auditoria.idx_pid_auditorias_created_at',
            'DROP INDEX IF EXISTS auditoria.idx_pid_auditorias_usuario_trgm',
            'DROP INDEX IF EXISTS auditoria.idx_pid_auditorias_nombres_trgm',
            'DROP INDEX IF EXISTS auditoria.idx_pid_auditorias_accion_trgm',
        ];

        foreach ($indexes as $statement) {
            $db->statement($statement);
        }
    }
};