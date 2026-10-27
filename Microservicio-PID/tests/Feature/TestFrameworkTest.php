<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TestFrameworkTest extends TestCase
{
    protected  $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Simulamos la obtención de un token (esto puede variar según tu aplicación)
        $response = $this->post('/api/v1/login',[
            "usuario" => "admin",
            "password" => "34a26f547b8a329ab6a18d34c122ad3e",
            "terminal" => "pc"
        ])->assertStatus(200);

        $data = $response->json();
        $this->token = $data['data']['token'];
    }

    /**
     * Testeando la ruta get de los roles.
     */
    public function testRouteRoles(): void
    {
        $this->get('/api/v1/roles',[
            "Authorization" => $this->token,
        ])->assertStatus(200);
    }

    /**
     * Testeando la ruta get de los usuarios.
     */
    public function testRouteUsers(): void
    {
        $this->get('/api/v1/users',[
            "Authorization" => $this->token,
        ])->assertStatus(200);
    }

    // /**
    //  * Testeando la ruta get de las auditorias.
    //  */
    public function testRouteAuditorias(): void
    {
        $this->get('/api/v1/auditoria',[
            "Authorization" => $this->token,
        ])->assertStatus(200);
    }
}
