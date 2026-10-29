<?php

namespace Tests\Feature;

use App\Http\Controllers\AuthController;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class EndpointsTest extends TestCase
{
    use DatabaseTransactions;

    private const PG = 'pgsql';
    private const PASSWORD = 'Secret123*';

    /** Permisos que exige el PermissionMiddleware de cada ruta. */
    private const PERMISSIONS = [
        'users.list', 'users.create', 'users.edit', 'users.delete', 'users.change_password',
        'roles.list', 'roles.create', 'roles.edit', 'roles.delete',
        'audits.list',
        'services.list', 'services.create', 'services.edit', 'services.delete',
        'states.list', 'states.create', 'states.edit', 'states.delete',
        'solicitudes.list', 'solicitudes.create', 'solicitudes.edit', 'solicitudes.delete',
        'persons.list', 'persons.create', 'persons.edit', 'persons.delete',
        'permissions.list', 'permissions.create', 'permissions.edit', 'permissions.delete',
    ];

    private string $token = '';
    private string $usuario = '';
    private int $userId = 0;
    private int $rolId = 0;
    private int $personaId = 0;
    private int $estadoId = 0;
    private int $servicioId = 0;
    private string $suffix = '';

    protected function setUp(): void
    {
        parent::setUp();

        // Sin dependencias externas: los broadcasts usan el logger y no se hace HTTP real.
        config(['broadcasting.default' => 'log']);
        Http::fake();

        $this->suffix = Str::lower(Str::random(12));
        $now = now()->format('Y-m-d H:i:s');

        $this->rolId = DB::connection(self::PG)->table('autenticacion.rols')->insertGetId([
            'name' => 'roltest'.$this->suffix,
            'guard_name' => 'web',
            'estado' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->estadoId = $this->ensureEstado('creada');
        $this->personaId = $this->newPersona();
        $this->servicioId = $this->newServicio();

        $this->usuario = 'test'.$this->suffix;
        $this->userId = DB::connection(self::PG)->table('autenticacion.users')->insertGetId([
            'usuario' => $this->usuario,
            'password' => \Illuminate\Support\Facades\Hash::make(self::PASSWORD),
            'rol_id' => $this->rolId,
            'persona_id' => $this->personaId,
            'estado' => true,
            'valid' => true,
            'int' => 0,
            'password_expiry' => now()->addYear()->format('Y-m-d H:i:s'),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->seedPermissions();

        $login = $this->postJson('/api/v1/login', [
            'usuario' => $this->usuario,
            'password' => self::PASSWORD,
            'terminal' => 'phpunit',
        ]);
        $login->assertStatus(200);
        $this->token = (string) $login->json('data.token');
    }

    // ---------------------------------------------------------------
    // Auth
    // ---------------------------------------------------------------

    public function testLoginDevuelveToken(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'usuario' => $this->usuario,
            'password' => self::PASSWORD,
            'terminal' => 'phpunit',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.usuario', $this->usuario);
        $this->assertNotEmpty($response->json('data.token'));
    }

    public function testLoginConPasswordIncorrectaDevuelve401(): void
    {
        $this->postJson('/api/v1/login', [
            'usuario' => $this->usuario,
            'password' => 'otra-clave',
            'terminal' => 'phpunit',
        ])->assertStatus(401);
    }

    public function testLoginConUsuarioInexistenteDevuelve403(): void
    {
        $this->postJson('/api/v1/login', [
            'usuario' => 'no-existe-'.$this->suffix,
            'password' => self::PASSWORD,
            'terminal' => 'phpunit',
        ])->assertStatus(403);
    }

    public function testAuthMeDevuelveUsuario(): void
    {
        $response = $this->auth()->getJson('/api/v1/auth/me');

        $response->assertStatus(200);
        $response->assertJsonPath('data.usuario', $this->usuario);
    }

    public function testLogoutCierraSesion(): void
    {
        $this->auth()->deleteJson('/api/v1/logout')->assertStatus(200);

        // El token deja de servir para llamadas protegidas.
        $this->auth()->getJson('/api/v1/users')->assertStatus(409);
    }

    public function testWsoGetTokenUsaElIdentityServer(): void
    {
        Http::fake([
            'https://10.10.3.100/*' => Http::response(['access_token' => 'x'], 200),
        ]);

        $this->getJson('/api/v1/v1/wso/get_token?code=abc&client_id=c&redirect_uri=http://localhost')->assertStatus(200);
    }

    public function testWsoVerificarTokenSinHeaderDevuelve409(): void
    {
        $this->getJson('/api/v1/v2/wso/verificar_token')->assertStatus(409);
    }

    public function testWsoVerificarTokenMalformadoDevuelve400(): void
    {
        $this->withHeaders(['X-Authorization' => 'sin-formato-jwt'])
            ->getJson('/api/v1/v2/wso/verificar_token')
            ->assertStatus(400);
    }

    public function testWsoRevocarTokenSinHeaderDevuelve409(): void
    {
        $this->postJson('/api/v1/v1/wso/revocar_token')->assertStatus(409);
    }

    public function testEndpointProtegidoSinTokenDevuelve408(): void
    {
        $this->getJson('/api/v1/users')->assertStatus(408);
    }

    public function testTokenFirmadoPeroInexistenteDevuelve409(): void
    {
        $token = app(AuthController::class)->conformarToken(
            'sha256', 'JWT', 'cdasi', 999999, 'phpunit',
            time(), time(), time() + 3600, 999999,
        );

        $this->withHeaders(['Authorization' => $token])
            ->getJson('/api/v1/users')
            ->assertStatus(409);
    }

    public function testRecursoInexistenteDevuelve404(): void
    {
        $this->auth()->getJson('/api/v1/users/999999999')->assertStatus(404);
    }

    // ---------------------------------------------------------------
    // Usuarios
    // ---------------------------------------------------------------

    public function testListarUsuarios(): void
    {
        $response = $this->auth()->getJson('/api/v1/users');

        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => [['id', 'usuario', 'rol_id', 'estado']], 'total', 'message']);
    }

    public function testListarUsuariosFiltradoPorUsuario(): void
    {
        $response = $this->auth()->getJson('/api/v1/users?usuario='.$this->usuario);

        $response->assertStatus(200);
        $response->assertJsonPath('data.0.usuario', $this->usuario);
    }

    public function testListarUsuariosPaginado(): void
    {
        $this->auth()->getJson('/api/v1/users?paginate=1')->assertStatus(200);
    }

    public function testMostrarUsuario(): void
    {
        $response = $this->auth()->getJson('/api/v1/users/'.$this->userId);

        $response->assertStatus(200);
        $response->assertJsonPath('data.id', $this->userId);
        $response->assertJsonPath('data.usuario', $this->usuario);
    }

    public function testRegistrarUsuario(): void
    {
        $personaId = $this->newPersona();

        $response = $this->auth()->postJson('/api/v1/users', [
            'usuario' => 'nuevo'.$this->suffix,
            'persona_id' => $personaId,
            'rol_id' => $this->rolId,
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.usuario', 'nuevo'.$this->suffix);
    }

    public function testActualizarUsuario(): void
    {
        $response = $this->auth()->putJson('/api/v1/users/'.$this->userId, [
            'estado' => true,
            'rol_id' => $this->rolId,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.id', $this->userId);
    }

    public function testCambiarPasswordYVolverAEntrar(): void
    {
        $this->auth()->putJson('/api/v1/users/change_password/'.$this->userId, [
            'password' => self::PASSWORD,
            'new_password' => 'NuevaClave123*',
        ])->assertStatus(200);

        $this->postJson('/api/v1/login', [
            'usuario' => $this->usuario,
            'password' => 'NuevaClave123*',
            'terminal' => 'phpunit',
        ])->assertStatus(200);
    }

    public function testCambiarPasswordConClaveAnteriorIncorrectaDevuelve400(): void
    {
        $this->auth()->putJson('/api/v1/users/change_password/'.$this->userId, [
            'password' => 'clave-mala',
            'new_password' => 'NuevaClave123*',
        ])->assertStatus(400);
    }

    public function testResetearPassword(): void
    {
        $this->auth()->putJson('/api/v1/users/reset_password/'.$this->userId)->assertStatus(200);

        // Tras el reseteo el usuario debe cambiar la contraseña antes de entrar.
        $this->postJson('/api/v1/login', [
            'usuario' => $this->usuario,
            'password' => self::PASSWORD,
            'terminal' => 'phpunit',
        ])->assertStatus(402);
    }

    public function testActualizarUsuariosMasivo(): void
    {
        $response = $this->auth()->putJson('/api/v1/users/update_masivo', [
            'items' => [$this->userId],
            'estado' => false,
        ]);

        $response->assertStatus(200);
        $this->assertFalse((bool) DB::connection(self::PG)->table('autenticacion.users')->where('id', $this->userId)->value('estado'));
    }

    public function testEliminarUsuario(): void
    {
        $victimId = $this->newUser('victim');

        $this->auth()->deleteJson('/api/v1/users/'.$victimId)->assertStatus(200);

        $this->assertNull(DB::connection(self::PG)->table('autenticacion.users')->find($victimId));
    }

    public function testEliminarUsuariosMasivo(): void
    {
        $victimId = $this->newUser('victim');

        $response = $this->auth()->postJson('/api/v1/users/delete_masivo', [
            'items' => [$victimId],
        ]);

        $response->assertStatus(200);
        $this->assertNull(DB::connection(self::PG)->table('autenticacion.users')->find($victimId));
    }

    // ---------------------------------------------------------------
    // Roles
    // ---------------------------------------------------------------

    public function testListarRoles(): void
    {
        $response = $this->auth()->getJson('/api/v1/roles');

        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => [['id', 'rol', 'estado']], 'total', 'message']);
    }

    public function testListarRolesFiltrado(): void
    {
        $this->auth()->getJson('/api/v1/roles?name='.$this->suffix)->assertStatus(200);
    }

    public function testMostrarRol(): void
    {
        $response = $this->auth()->getJson('/api/v1/roles/'.$this->rolId);

        $response->assertStatus(200);
        $response->assertJsonPath('data.id', $this->rolId);
    }

    public function testCrearRol(): void
    {
        $nombre = 'rol nuevo '.$this->suffix;

        $response = $this->auth()->postJson('/api/v1/roles', [
            'rol' => $nombre,
            'guard_name' => 'web',
            'estado' => true,
        ]);

        $response->assertStatus(200);
        // El recurso expone el rol con la primera letra de cada palabra en mayúscula.
        $response->assertJsonPath('data.rol', ucwords($nombre));
    }

    public function testCrearRolesMasivo(): void
    {
        $response = $this->auth()->postJson('/api/v1/roles/create_masivo', [
            'items' => [
                ['rol' => 'masivo uno '.$this->suffix],
                ['rol' => 'masivo dos '.$this->suffix],
            ],
        ]);

        $response->assertStatus(200);
        $this->assertEquals(2, $response->json('cant'));
    }

    public function testActualizarRol(): void
    {
        $response = $this->auth()->putJson('/api/v1/roles/'.$this->rolId, ['estado' => true]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.id', $this->rolId);
    }

    public function testActualizarRolesMasivo(): void
    {
        $response = $this->auth()->putJson('/api/v1/roles/update_masivo', [
            'items' => [$this->rolId],
            'estado' => false,
        ]);

        $response->assertStatus(200);
        $this->assertFalse((bool) DB::connection(self::PG)->table('autenticacion.rols')->find($this->rolId)->estado);
    }

    public function testEliminarRol(): void
    {
        $rolId = $this->newRol();

        $this->auth()->deleteJson('/api/v1/roles/'.$rolId)->assertStatus(200);

        $this->assertNull(DB::connection(self::PG)->table('autenticacion.rols')->find($rolId));
    }

    public function testEliminarRolesMasivo(): void
    {
        $rolId = $this->newRol();

        $response = $this->auth()->postJson('/api/v1/roles/delete_masivo', ['items' => [$rolId]]);

        $response->assertStatus(200);
        $this->assertNull(DB::connection(self::PG)->table('autenticacion.rols')->find($rolId));
    }

    // ---------------------------------------------------------------
    // Auditoría
    // ---------------------------------------------------------------

    public function testListarAuditorias(): void
    {
        $response = $this->auth()->getJson('/api/v1/auditoria');

        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => [['identificador', 'usuario', 'accion']], 'total', 'message']);
    }

    public function testListarAuditoriasFiltrada(): void
    {
        $this->auth()->getJson('/api/v1/auditoria?usuario='.$this->usuario)->assertStatus(200);
    }

    public function testListarAuditoriasPaginada(): void
    {
        $this->auth()->getJson('/api/v1/auditoria?paginate=2')->assertStatus(200);
    }

    // ---------------------------------------------------------------
    // Servicios
    // ---------------------------------------------------------------

    public function testListarServicios(): void
    {
        $response = $this->auth()->getJson('/api/v1/services');

        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => [['id', 'nombre']], 'total', 'message']);
    }

    public function testMostrarServicio(): void
    {
        $response = $this->auth()->getJson('/api/v1/services/'.$this->servicioId);

        $response->assertStatus(200);
        $response->assertJsonPath('data.id', $this->servicioId);
    }

    public function testCrearServicio(): void
    {
        $nombre = 'Servicio '.$this->suffix;

        $response = $this->auth()->postJson('/api/v1/services', ['nombre' => $nombre]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.nombre', $nombre);
    }

    public function testActualizarServicio(): void
    {
        $nombre = 'Servicio editado '.$this->suffix;

        $response = $this->auth()->putJson('/api/v1/services/'.$this->servicioId, ['nombre' => $nombre]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.nombre', $nombre);
    }

    public function testEliminarServicio(): void
    {
        $id = $this->newServicio();

        $this->auth()->deleteJson('/api/v1/services/'.$id)->assertStatus(200);

        $this->assertNull(DB::connection(self::PG)->table('nomencladores.servicios')->find($id));
    }

    // ---------------------------------------------------------------
    // Estados
    // ---------------------------------------------------------------

    public function testListarEstados(): void
    {
        $response = $this->auth()->getJson('/api/v1/states');

        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => [['id', 'nombre']], 'total', 'message']);
    }

    public function testMostrarEstado(): void
    {
        $response = $this->auth()->getJson('/api/v1/states/'.$this->estadoId);

        $response->assertStatus(200);
        $response->assertJsonPath('data.id', $this->estadoId);
    }

    public function testCrearEstado(): void
    {
        $nombre = 'estado '.$this->suffix;

        $response = $this->auth()->postJson('/api/v1/states', ['nombre' => $nombre]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.nombre', $nombre);
    }

    public function testActualizarEstado(): void
    {
        $nombre = 'estado editado '.$this->suffix;

        $response = $this->auth()->putJson('/api/v1/states/'.$this->estadoId, ['nombre' => $nombre]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.nombre', $nombre);
    }

    public function testEliminarEstado(): void
    {
        $id = $this->ensureEstado('borrable '.$this->suffix);

        $this->auth()->deleteJson('/api/v1/states/'.$id)->assertStatus(200);

        $this->assertNull(DB::connection(self::PG)->table('nomencladores.estados')->find($id));
    }

    // ---------------------------------------------------------------
    // Personas
    // ---------------------------------------------------------------

    public function testListarPersonas(): void
    {
        $response = $this->auth()->getJson('/api/v1/persons');

        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => [['id', 'nombres', 'apellidos', 'ci']], 'total', 'message']);
    }

    public function testListarPersonasFiltrada(): void
    {
        $response = $this->auth()->getJson('/api/v1/persons?nombres=Jugador');

        $response->assertStatus(200);
        $this->assertIsArray($response->json('data'));
    }

    public function testMostrarPersona(): void
    {
        $response = $this->auth()->getJson('/api/v1/persons/'.$this->personaId);

        $response->assertStatus(200);
        $response->assertJsonPath('data.id', $this->personaId);
    }

    public function testCrearPersona(): void
    {
        $response = $this->auth()->postJson('/api/v1/persons', [
            'nombres' => 'Persona',
            'apellidos' => 'Nueva '.$this->suffix,
            'ci' => $this->uniqueCi(),
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.apellidos', 'Nueva '.$this->suffix);
    }

    public function testCrearPersonaConCiInvalidaDevuelve422(): void
    {
        $this->auth()->postJson('/api/v1/persons', [
            'nombres' => 'Persona',
            'apellidos' => 'Invalida',
            'ci' => '123',
        ])->assertStatus(422);
    }

    public function testActualizarPersona(): void
    {
        $response = $this->auth()->putJson('/api/v1/persons/'.$this->personaId, [
            'nombres' => 'Actualizada',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.nombres', 'Actualizada');
    }

    public function testEliminarPersona(): void
    {
        $id = $this->newPersona();

        $this->auth()->deleteJson('/api/v1/persons/'.$id)->assertStatus(200);

        $this->assertNull(DB::connection(self::PG)->table('nomencladores.personas')->find($id));
    }

    // ---------------------------------------------------------------
    // Solicitudes
    // ---------------------------------------------------------------

    public function testListarSolicitudes(): void
    {
        $response = $this->auth()->getJson('/api/v1/solicitudes');

        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => [['id', 'persona_id', 'estado_id', 'servicio_id']], 'total', 'message']);
    }

    public function testListarSolicitudesPorDefectoSoloDevuelveIdsDeServicio(): void
    {
        $id = $this->newSolicitud();

        $response = $this->auth()->getJson('/api/v1/solicitudes');

        $response->assertStatus(200);
        $response->assertJsonPath('data.0.id', $id);
        $response->assertJsonPath('data.0.servicio_id', $this->servicioId);

        $item = $response->json('data.0');
        $this->assertArrayNotHasKey('servicio', $item);
        $this->assertArrayNotHasKey('servicio_rel', $item);
        $this->assertArrayNotHasKey('persona', $item);
    }

    public function testListarSolicitudesConRelaciones(): void
    {
        $id = $this->newSolicitud();
        $personaDeLaSolicitud = (int) DB::connection(self::PG)->table('datos.solicitudes')->where('id', $id)->value('persona_id');

        $response = $this->auth()->getJson('/api/v1/solicitudes?persona_rel=true&servicio_rel=true');

        $response->assertStatus(200);
        $response->assertJsonPath('data.0.id', $id);
        $response->assertJsonPath('data.0.persona.id', $personaDeLaSolicitud);
        $response->assertJsonPath('data.0.servicio_rel.id', $this->servicioId);
        $response->assertJsonPath('data.0.servicio_rel.nombre', DB::connection(self::PG)->table('nomencladores.servicios')->where('id', $this->servicioId)->value('nombre'));

        $item = $response->json('data.0');
        $this->assertArrayNotHasKey('servicio', $item);
    }

    public function testListarSolicitudesFiltradaPorEstadoYServicio(): void
    {
        $this->newSolicitud();

        $this->auth()
            ->getJson('/api/v1/solicitudes?estado_id='.$this->estadoId.'&servicio_id='.$this->servicioId)
            ->assertStatus(200);
    }

    public function testMostrarSolicitud(): void
    {
        $id = $this->newSolicitud();

        $response = $this->auth()->getJson('/api/v1/solicitudes/'.$id);

        $response->assertStatus(200);
        $response->assertJsonPath('data.id', $id);
    }

    public function testCrearSolicitud(): void
    {
        $personaId = $this->newPersona();

        $response = $this->auth()->postJson('/api/v1/solicitudes', [
            'persona_id' => $personaId,
            'servicio_id' => $this->servicioId,
            'estado_id' => $this->estadoId,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.persona_id', $personaId);
    }

    public function testActualizarSolicitud(): void
    {
        $id = $this->newSolicitud();

        $response = $this->auth()->putJson('/api/v1/solicitudes/'.$id, [
            'estado_id' => $this->estadoId,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.id', $id);
    }

    public function testEliminarSolicitud(): void
    {
        $id = $this->newSolicitud();

        $this->auth()->deleteJson('/api/v1/solicitudes/'.$id)->assertStatus(200);

        $this->assertNull(DB::connection(self::PG)->table('datos.solicitudes')->find($id));
    }

    // ---------------------------------------------------------------
    // Permisos
    // ---------------------------------------------------------------

    public function testListarPermisos(): void
    {
        $response = $this->auth()->getJson('/api/v1/permissions');

        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => [['id', 'name', 'guard_name']], 'total', 'message']);
    }

    public function testMostrarPermiso(): void
    {
        $id = DB::connection(self::PG)->table('autenticacion.permissions')->where('name', 'users.list')->value('id');

        $response = $this->auth()->getJson('/api/v1/permissions/'.$id);

        $response->assertStatus(200);
        $response->assertJsonPath('data.name', 'users.list');
    }

    public function testCrearPermiso(): void
    {
        $nombre = 'permiso.'.$this->suffix;

        $response = $this->auth()->postJson('/api/v1/permissions', [
            'name' => $nombre,
            'guard_name' => 'web',
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.name', $nombre);
    }

    public function testActualizarPermiso(): void
    {
        $nombre = 'permiso.editado.'.$this->suffix;
        $id = $this->newPermission();

        $response = $this->auth()->putJson('/api/v1/permissions/'.$id, ['name' => $nombre]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.name', $nombre);
    }

    public function testEliminarPermiso(): void
    {
        $id = $this->newPermission();

        $this->auth()->deleteJson('/api/v1/permissions/'.$id)->assertStatus(200);

        $this->assertNull(DB::connection(self::PG)->table('autenticacion.permissions')->find($id));
    }

    public function testPermisosDeUnRol(): void
    {
        $permissionId = $this->newPermission();
        $rolId = $this->newRol();

        $this->auth()->postJson('/api/v1/permissions/assign/role', [
            'rol_id' => $rolId,
            'permission_ids' => [$permissionId],
        ])->assertStatus(200);

        $response = $this->auth()->getJson('/api/v1/permissions/roles/'.$rolId);

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }

    public function testPermisosDeUnUsuario(): void
    {
        $response = $this->auth()->getJson('/api/v1/permissions/users/'.$this->userId);

        $response->assertStatus(200);
        $this->assertNotEmpty($response->json('data'));
    }

    public function testAsignarPermisoAUsuario(): void
    {
        $permissionId = $this->newPermission();

        $this->auth()->postJson('/api/v1/permissions/assign/user', [
            'user_id' => $this->userId,
            'permission_ids' => [$permissionId],
        ])->assertStatus(200);

        $vinculo = DB::connection(self::PG)->table('autenticacion.model_has_permissions')
            ->where('permission_id', $permissionId)
            ->where('model_id', $this->userId)
            ->exists();

        $this->assertTrue($vinculo);
    }

    public function testRevocarPermisoDeUsuario(): void
    {
        $permissionId = $this->newPermission();

        $this->auth()->postJson('/api/v1/permissions/assign/user', [
            'user_id' => $this->userId,
            'permission_ids' => [$permissionId],
        ])->assertStatus(200);

        $this->auth()->deleteJson('/api/v1/permissions/revoke/user', [
            'user_id' => $this->userId,
            'permission_ids' => [$permissionId],
        ])->assertStatus(200);

        $vinculo = DB::connection(self::PG)->table('autenticacion.model_has_permissions')
            ->where('permission_id', $permissionId)
            ->where('model_id', $this->userId)
            ->exists();

        $this->assertFalse($vinculo);
    }

    public function testRevocarPermisoDeRol(): void
    {
        $permissionId = $this->newPermission();
        $rolId = $this->newRol();

        $this->auth()->postJson('/api/v1/permissions/assign/role', [
            'rol_id' => $rolId,
            'permission_ids' => [$permissionId],
        ])->assertStatus(200);

        $this->auth()->deleteJson('/api/v1/permissions/revoke/role', [
            'rol_id' => $rolId,
            'permission_ids' => [$permissionId],
        ])->assertStatus(200);

        $vinculo = DB::connection(self::PG)->table('autenticacion.role_has_permissions')
            ->where('permission_id', $permissionId)
            ->where('role_id', $rolId)
            ->exists();

        $this->assertFalse($vinculo);
    }

    public function testListarPermisosFiltrado(): void
    {
        $this->auth()->getJson('/api/v1/permissions?name=users&guard_name=web')->assertStatus(200);
    }

    public function testListarPermisosPaginado(): void
    {
        $this->auth()->getJson('/api/v1/permissions?paginate=5')->assertStatus(200);
    }

    // ---------------------------------------------------------------
    // Utilidades
    // ---------------------------------------------------------------

    private function auth(): static
    {
        return $this->withHeaders([
            'Authorization' => $this->token,
            'Accept' => 'application/json',
        ]);
    }

    private function seedPermissions(): void
    {
        $now = now()->format('Y-m-d H:i:s');
        $table = DB::connection(self::PG);

        foreach (self::PERMISSIONS as $name) {
            $table->table('autenticacion.permissions')->updateOrInsert(
                ['name' => $name, 'guard_name' => 'web'],
                ['created_at' => $now, 'updated_at' => $now]
            );

            $id = $table->table('autenticacion.permissions')
                ->where('name', $name)
                ->where('guard_name', 'web')
                ->value('id');

            $table->table('autenticacion.model_has_permissions')->insertOrIgnore([
                'permission_id' => $id,
                'model_type' => User::class,
                'model_id' => $this->userId,
            ]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function newPersona(): int
    {
        $now = now()->format('Y-m-d H:i:s');

        return DB::connection(self::PG)->table('nomencladores.personas')->insertGetId([
            'nombres' => 'Jugador',
            'apellidos' => 'Test '.$this->suffix,
            'ci' => $this->uniqueCi(),
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function newServicio(): int
    {
        $now = now()->format('Y-m-d H:i:s');

        return DB::connection(self::PG)->table('nomencladores.servicios')->insertGetId([
            'nombre' => 'Servicio '.Str::lower(Str::random(12)),
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function ensureEstado(string $nombre): int
    {
        $id = DB::connection(self::PG)->table('nomencladores.estados')->where('nombre', $nombre)->value('id');
        if ($id) {
            return (int) $id;
        }

        $now = now()->format('Y-m-d H:i:s');

        return (int) DB::connection(self::PG)->table('nomencladores.estados')->insertGetId([
            'nombre' => $nombre,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function newRol(): int
    {
        $now = now()->format('Y-m-d H:i:s');

        return (int) DB::connection(self::PG)->table('autenticacion.rols')->insertGetId([
            'name' => 'rol '.Str::lower(Str::random(12)),
            'guard_name' => 'web',
            'estado' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function newPermission(): int
    {
        $now = now()->format('Y-m-d H:i:s');
        $nombre = 'permiso.'.Str::lower(Str::random(12));

        $id = (int) DB::connection(self::PG)->table('autenticacion.permissions')->insertGetId([
            'name' => $nombre,
            'guard_name' => 'web',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $id;
    }

    private function newUser(string $prefijo): int
    {
        $now = now()->format('Y-m-d H:i:s');
        $usuario = $prefijo.substr(Str::lower(Str::random(10)), 0, 8);

        return (int) DB::connection(self::PG)->table('autenticacion.users')->insertGetId([
            'usuario' => $usuario,
            'password' => \Illuminate\Support\Facades\Hash::make(self::PASSWORD),
            'rol_id' => $this->rolId,
            'persona_id' => $this->newPersona(),
            'estado' => true,
            'valid' => true,
            'int' => 0,
            'password_expiry' => now()->addYear()->format('Y-m-d H:i:s'),
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function newSolicitud(): int
    {
        $now = now()->format('Y-m-d H:i:s');

        return (int) DB::connection(self::PG)->table('datos.solicitudes')->insertGetId([
            'persona_id' => $this->newPersona(),
            'estado_id' => $this->estadoId,
            'servicio_id' => $this->servicioId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function uniqueCi(): string
    {
        return str_pad((string) abs(crc32(uniqid('ci', true))), 11, '0', STR_PAD_LEFT);
    }
}