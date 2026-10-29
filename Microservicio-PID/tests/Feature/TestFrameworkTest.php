<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class TestFrameworkTest extends TestCase
{
    use DatabaseTransactions;

    private const PG = 'pgsql';
    private const PASSWORD = 'Secret123*';

    protected $token;
    private string $usuario = '';
    private string $suffix = '';

    protected function setUp(): void
    {
        parent::setUp();

        // Los eventos se envían al log: no se necesita servidor de websockets en pruebas.
        config(['broadcasting.default' => 'log']);

        $this->suffix = Str::lower(Str::random(10));
        $now = now()->format('Y-m-d H:i:s');

        $rolId = DB::connection(self::PG)->table('autenticacion.rols')->insertGetId([
            'name' => 'roltest'.$this->suffix,
            'guard_name' => 'web',
            'estado' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $personaId = DB::connection(self::PG)->table('nomencladores.personas')->insertGetId([
            'nombres' => 'Admin',
            'apellidos' => 'Test '.$this->suffix,
            'ci' => str_pad((string) abs(crc32(uniqid('ci', true))), 11, '0', STR_PAD_LEFT),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->usuario = 'admin'.$this->suffix;
        $this->seedPermissions(['roles.list', 'users.list', 'audits.list']);

        $userId = DB::connection(self::PG)->table('autenticacion.users')->insertGetId([
            'usuario' => $this->usuario,
            'password' => Hash::make(self::PASSWORD),
            'rol_id' => $rolId,
            'persona_id' => $personaId,
            'estado' => true,
            'valid' => true,
            'int' => 0,
            'password_expiry' => now()->addYear()->format('Y-m-d H:i:s'),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->assignPermissions($userId, ['roles.list', 'users.list', 'audits.list']);

        // Simulamos la obtención de un token con el usuario sembrado arriba.
        $response = $this->postJson('/api/v1/login', [
            'usuario' => $this->usuario,
            'password' => self::PASSWORD,
            'terminal' => 'pc',
        ])->assertStatus(200);

        $this->token = $response->json('data.token');
    }

    /**
     * Testeando la ruta get de los roles.
     */
    public function testRouteRoles(): void
    {
        $this->get('/api/v1/roles', [
            'Authorization' => $this->token,
        ])->assertStatus(200);
    }

    /**
     * Testeando la ruta get de los usuarios.
     */
    public function testRouteUsers(): void
    {
        $this->get('/api/v1/users', [
            'Authorization' => $this->token,
        ])->assertStatus(200);
    }

    public function testRouteAuditorias(): void
    {
        $this->get('/api/v1/auditoria', [
            'Authorization' => $this->token,
        ])->assertStatus(200);
    }

    private function seedPermissions(array $nombres): void
    {
        $now = now()->format('Y-m-d H:i:s');

        foreach ($nombres as $nombre) {
            DB::connection(self::PG)->table('autenticacion.permissions')->updateOrInsert(
                ['name' => $nombre, 'guard_name' => 'web'],
                ['created_at' => $now, 'updated_at' => $now]
            );
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function assignPermissions(int $userId, array $nombres): void
    {
        foreach ($nombres as $nombre) {
            $permissionId = DB::connection(self::PG)->table('autenticacion.permissions')
                ->where('name', $nombre)
                ->where('guard_name', 'web')
                ->value('id');

            DB::connection(self::PG)->table('autenticacion.model_has_permissions')->insertOrIgnore([
                'permission_id' => $permissionId,
                'model_type' => User::class,
                'model_id' => $userId,
            ]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
