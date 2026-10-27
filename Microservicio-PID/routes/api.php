<?php

use App\Http\Controllers\AuditoriaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ServicioController;
use App\Http\Controllers\EstadoController;
use App\Http\Controllers\SolicitudController;
use App\Http\Controllers\PersonaController;
use Illuminate\Support\Facades\Route;

$middleware = env('MIDDLEWARE');

//////////// Rutas de autenticación //////////////////////
Route::prefix('v1')->controller(AuthController::class)->group(function() {
    Route::get('auth/me', 'auth_me')->middleware('autenticacion_estandar');
    Route::post('login', 'login');
    Route::delete('logout', 'logout')->middleware('autenticacion_estandar');

    Route::get('v1/wso/get_token', 'get_token');
    Route::get('v2/wso/verificar_token', 'verificar_token')->middleware('autenticacion_wso');
    Route::post('v1/wso/revocar_token', 'revocar_token')->middleware('autenticacion_wso');
});

// Cambiar contraseña
Route::prefix('v1')->middleware([$middleware, 'permission:users.change_password'])->controller(UserController::class)->group(function(){
    Route::put('users/change_password/{user}', 'change_password');
});



/////////////////// Rutas de usuarios ////////////////////
Route::prefix('v1')->middleware([$middleware, 'permission:users.list'])->controller(UserController::class)->group(function(){
    Route::get('users', 'list');
    Route::get('users/{user}', 'show');
});

// Registrar usuarios
Route::prefix('v1')->middleware([$middleware, 'permission:users.create'])->controller(UserController::class)->group(function(){
    Route::post('users', 'register');
});

// Actualizar usuarios
Route::prefix('v1')->middleware([$middleware, 'permission:users.edit'])->controller(UserController::class)->group(function(){
    Route::put('users/{user}', 'update'); 
    Route::put('users/reset_password/{user}', 'reset_password');
    Route::put('users/update_masivo', 'update_masivo');
});

// Eliminar usuarios
Route::prefix('v1')->middleware([$middleware, 'permission:users.delete'])->controller(UserController::class)->group(function(){
    Route::delete('users/{user}', 'delete');
    Route::post('users/delete_masivo', 'delete_masivo');
});



//////////////////////////// Rutas de roles /////////////////////////
Route::prefix('v1')->middleware([$middleware, 'permission:roles.list'])->controller(RolController::class)->group(function(){
    Route::get('roles', 'list');
    Route::get('roles/{rol}', 'show');
});

// Crear rol
Route::prefix('v1')->middleware([$middleware, 'permission:roles.create'])->controller(RolController::class)->group(function(){
    Route::post('roles', 'create');
    Route::post('roles/create_masivo', 'create_masivo');
});

// actualizar rol
Route::prefix('v1')->middleware([$middleware, 'permission:roles.edit'])->controller(RolController::class)->group(function(){
    Route::put('roles/{rol}', 'update');
    Route::put('roles/update_masivo', 'update_masivo');
});

// Eliminar rol
Route::prefix('v1')->middleware([$middleware, 'permission:roles.delete'])->controller(RolController::class)->group(function(){
    Route::delete('roles/{rol}', 'delete');
    Route::post('roles/delete_masivo', 'delete_masivo');
});



/////////////////////////////// Rutas de auditoria /////////////////////////////
Route::prefix('v1')->middleware([$middleware, 'permission:audits.list'])->controller(AuditoriaController::class)->group(function(){
    Route::get('auditoria', 'list');
});



////////////////////////////// Rutas de servicios //////////////////////////////
Route::prefix('v1')->middleware([$middleware, 'permission:services.list'])->controller(ServicioController::class)->group(function(){
    Route::get('services', 'list');
    Route::get('services/{servicio}', 'show');
});

Route::prefix('v1')->middleware([$middleware, 'permission:services.create'])->controller(ServicioController::class)->group(function(){
    Route::post('services', 'create');
});

Route::prefix('v1')->middleware([$middleware, 'permission:services.edit'])->controller(ServicioController::class)->group(function(){
    Route::put('services/{servicio}', 'update');
});

Route::prefix('v1')->middleware([$middleware, 'permission:services.delete'])->controller(ServicioController::class)->group(function(){
    Route::delete('services/{servicio}', 'delete');
});

////////////////////////////// Rutas de estados //////////////////////////////
Route::prefix('v1')->middleware([$middleware, 'permission:states.list'])->controller(EstadoController::class)->group(function(){
    Route::get('states', 'list');
    Route::get('states/{estado}', 'show');
});

Route::prefix('v1')->middleware([$middleware, 'permission:states.create'])->controller(EstadoController::class)->group(function(){
    Route::post('states', 'create');
});

Route::prefix('v1')->middleware([$middleware, 'permission:states.edit'])->controller(EstadoController::class)->group(function(){
    Route::put('states/{estado}', 'update');
});

Route::prefix('v1')->middleware([$middleware, 'permission:states.delete'])->controller(EstadoController::class)->group(function(){
    Route::delete('states/{estado}', 'delete');
});

////////////////////////////// Rutas de solicitudes //////////////////////////////
Route::prefix('v1')->middleware([$middleware, 'permission:solicitudes.list'])->controller(SolicitudController::class)->group(function(){
    Route::get('solicitudes', 'list');
    Route::get('solicitudes/{solicitud}', 'show');
});

Route::prefix('v1')->middleware([$middleware, 'permission:solicitudes.create'])->controller(SolicitudController::class)->group(function(){
    Route::post('solicitudes', 'create');
});

Route::prefix('v1')->middleware([$middleware, 'permission:solicitudes.edit'])->controller(SolicitudController::class)->group(function(){
    Route::put('solicitudes/{solicitud}', 'update');
});

Route::prefix('v1')->middleware([$middleware, 'permission:solicitudes.delete'])->controller(SolicitudController::class)->group(function(){
    Route::delete('solicitudes/{solicitud}', 'delete');
});

////////////////////////////// Rutas de personas //////////////////////////////
Route::prefix('v1')->middleware([$middleware, 'permission:persons.list'])->controller(PersonaController::class)->group(function(){
    Route::get('persons', 'list');
    Route::get('persons/{persona}', 'show');
});

Route::prefix('v1')->middleware([$middleware, 'permission:persons.create'])->controller(PersonaController::class)->group(function(){
    Route::post('persons', 'create');
});

Route::prefix('v1')->middleware([$middleware, 'permission:persons.edit'])->controller(PersonaController::class)->group(function(){
    Route::put('persons/{persona}', 'update');
});

Route::prefix('v1')->middleware([$middleware, 'permission:persons.delete'])->controller(PersonaController::class)->group(function(){
    Route::delete('persons/{persona}', 'delete');
});



////////////////////////////// Rutas de permisos //////////////////////////////
Route::prefix('v1')->middleware([$middleware, 'permission:permissions.list'])->controller(PermissionController::class)->group(function(){
    Route::get('permissions', 'list');
    Route::get('permissions/{id}', 'show');
    Route::get('permissions/roles/{rolId}', 'permissions_by_role');
    Route::get('permissions/users/{userId}', 'permissions_by_user');
});



// Rutas de permisos - CREATE
Route::prefix('v1')->middleware([$middleware, 'permission:permissions.create'])->controller(PermissionController::class)->group(function(){
    Route::post('permissions', 'create');
    Route::post('permissions/assign/role', 'assign_to_role');
    Route::post('permissions/assign/user', 'assign_to_user');
});

// Rutas de permisos - EDIT
Route::prefix('v1')->middleware([$middleware, 'permission:permissions.edit'])->controller(PermissionController::class)->group(function(){
    Route::put('permissions/{id}', 'update');
    Route::delete('permissions/revoke/role', 'revoke_from_role');
    Route::delete('permissions/revoke/user', 'revoke_from_user');
});

// Rutas de permisos - DELETE
Route::prefix('v1')->middleware([$middleware, 'permission:permissions.delete'])->controller(PermissionController::class)->group(function(){
    Route::delete('permissions/{id}', 'delete');
});