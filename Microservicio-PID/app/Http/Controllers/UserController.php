<?php

namespace App\Http\Controllers;

use App\Events\UserEvents;
use App\Models\User;
use App\Helpers\Helpers;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\AuditoriaController;
use App\Http\Requests\UserDeleteRequest_v1;
use App\Http\Requests\UserListRequest_v1;
use App\Http\Requests\UserRegistrerRequest_v1;
use App\Http\Requests\UserUpdateMasivoRequest_v1;
use App\Http\Requests\UserUpdatePasswordRequest_v1;
use App\Http\Requests\UserUpdateRequest_v1;
use App\Mail\UserCreatedMail;
use G4T\Swagger\Attributes\SwaggerSection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

#[SwaggerSection('Rutas de los usuarios')]
class UserController extends Controller
{
    /**
    * Listar usuarios
    * 
    * Devuelve el listado de usuarios
    */
    public function list(UserListRequest_v1 $request)
    {
        $personaRel = $request->input('persona_rel');
        $showPersona = is_string($personaRel) ? in_array(strtolower($personaRel), ['true','1','on','yes']) : filter_var($personaRel, FILTER_VALIDATE_BOOLEAN);
        $rolRel = $request->input('rol_rel');
        $showRol = is_string($rolRel) ? in_array(strtolower($rolRel), ['true','1','on','yes']) : filter_var($rolRel, FILTER_VALIDATE_BOOLEAN);

        $with = [];
        if ($showPersona === true) {
            $with[] = 'persona';
        }
        if ($showRol === true) {
            $with[] = 'rol';
        }

        $query = User::query();
        if (!empty($with)) {
            $query->with($with);
        }
        $query->orderByDesc('estado')->orderBy('usuario')->orderByDesc('id');

        if ($request->has('usuario')) {
            $query->whereRaw('LOWER(usuario) LIKE ?', ['%' . strtolower($request->usuario) . '%']);
        }

        if ($request->has('rol_id')) {
            $query->where('rol_id', $request->rol_id);
        }

        if ($request->has('estado')) {
            $query->where('estado', $request->estado === 'active');
        }

        $cant = $query->count();

        $pag = $request->paginate;
        if ($pag > 0) {
            $result = $query->paginate($pag);
            return UserResource::collection($result);
        } else {
            $modelo = $query->get();
            return response(
                [
                    'data' => UserResource::collection($modelo),
                    'total' => $cant,
                    'message' => 'Consulta satisfactoria.'
                ],200);
        }
    }

    /**
    * Listar un usuario
    * 
    * Devuelve un usuario
    */
    public function show(User $user)
    {
            return response(
                [
                    'data' => new UserResource($user),
                    'message' => 'Consulta satisfactoria.'
                ], 200);
    }

    /**
    * Registrar un usuario
    *
    * Contraseña por defecto es (User*(el año actual))
    */
    public function register(UserRegistrerRequest_v1 $request)
    {
        $user = new User();
        $user->usuario = $request->usuario;
        if ($request->has('persona_id')) {
            $user->persona_id = $request->persona_id;
        }
        $user->rol_id = $request->rol_id;
        $user->password_expiry = $user->password_expire();
        $user->password = Hash::make(hash('sha256', 'User*'.now()->year));
        $user->save();

        //////////////////AUDITORIA/////////////////////////////////////////
        AuditoriaController::auditoria_pro($request, 'Se registró el usuario '.$user->nombres);

        ///////////////// Evento ///////////////////////////////////
        try {
            // Conexión con el Servidor de Websocket
            Http::get(env('PUSHER_HOST').':'.env('PUSHER_PORT').'/app/'.env('PUSHER_APP_KEY'));
            event(new UserEvents(UserResource::collection(User::orderByDesc('estado')->orderBy('nombres')->orderByDesc('id')->get())));
        } catch (\Throwable $th) {
        }

        return response(
            [
                'data' => new UserResource($user),
                'cant' => 0,
                'message' => 'Usuario registrado satisfactoriamente.'
            ],201);
    }

    /**
    * Actualizar usuario
    */
    public function update(UserUpdateRequest_v1 $request, $user)
    {
        $user = User::find($user);
            $data = [
                "usuario" => ($request->has("usuario"))? $request->usuario: $user->usuario,
                "rol_id" => ($request->has("rol_id"))? $request->rol_id: $user->rol_id,
                "estado" => ($request->has("estado"))? $request->estado: $user->estado
            ];
            if ($request->has('persona_id')) {
                $data['persona_id'] = $request->persona_id;
            }
            $user->update($data);

        //////////////////AUDITORIA/////////////////////////////////////////
        AuditoriaController::auditoria_pro($request, 'Se actualizó el usuario '.$user->nombres);

        ///////////////// Evento ///////////////////////////////////
        try {
            // Conexión con el Servidor de Websocket
            Http::get(env('PUSHER_HOST').':'.env('PUSHER_PORT').'/app/'.env('PUSHER_APP_KEY'));
            event(new UserEvents(UserResource::collection(User::orderByDesc('estado')->orderBy('nombres')->orderByDesc('id')->get())));
        } catch (\Throwable $th) {
        }

        return response(
            [
                'data' => new UserResource($user),
                'message' => 'Actualizacion satisfactoria.'
            ], 200);
    }

    /**
    * Actualizar usuarios masivamente
    */
    public function update_masivo(UserUpdateMasivoRequest_v1 $request)
    {
        if ($request->has('items')) {
            $roles = User::find($request->items);
            if ($roles) {
                ($request->has("estado"))? $roles->toQuery()->update(["estado" => $request->estado]): "";
                ($request->has("rol_id"))? $roles->toQuery()->update(["rol_id" => $request->rol_id]): "";
            }
        }

        if ($request->all === true) {
            $roles = User::all();
            ($request->has("estado"))? $roles->toQuery()->update(["estado" => $request->estado]): "";
            ($request->has("rol_id"))? $roles->toQuery()->update(["rol_id" => $request->rol_id]): "";
        }
        
        //////////////////AUDITORIA/////////////////////////////////////////
        AuditoriaController::auditoria_pro($request, 'Actualizar roles');

        ///////////////// Evento ///////////////////////////////////
        try {
            // Conexión con el Servidor de Websocket
            Http::get(env('PUSHER_HOST').':'.env('PUSHER_PORT').'/app/'.env('PUSHER_APP_KEY'));
            event(new UserEvents(UserResource::collection(User::orderByDesc('estado')->orderBy('nombres')->orderByDesc('id')->get())));
        } catch (\Throwable $th) {
        }

        return response(
            [
                'message' => 'Actualizacion satisfactoria.',
                'cant' => $roles->count(),
            ], 200);
    }

    /**
    * Cambiar la contraseña de un usuario
    */
    public function change_password(UserUpdatePasswordRequest_v1 $request, User $user)
    {
        if (!Hash::check($request->password, $user->password)){
            return response(
            [
                'message' => 'Contraseña anterior incorrecta.'
            ],400);
        }

        $user->update([
            "password" => Hash::make($request->new_password),
            "password_expiry" => $user->password_expire(),
            "int" => 0,
            "estado" => true,
            "valid" => true,
        ]);

        //////////////////AUDITORIA/////////////////////////////////////////
        AuditoriaController::auditoria_pro($request, 'Se cambió la contraseña del usuario '.$user->nombres);

        return response(
            [
                'data' => new UserResource($user),
                'message' => 'Contraseña cambiada correctamente.'
            ],200);
    }

    /**
    * Resetear contraseña a un usuario 
    *
    * Contraseña por defecto es (User*(el año actual))
    */
    public function reset_password(Request $request, User $user)
    {
        $user->update([
            "password" => Hash::make(hash('sha256', 'User*'.now()->year)),
            "password_expiry" => $user->password_expire(),
            "int" => 0,
            "estado" => true,
            "valid" => false,
        ]);

        //////////////////AUDITORIA/////////////////////////////////////////
        AuditoriaController::auditoria_pro($request, 'Se reseteó la contraseña del usuario '.$user->nombres);

        return response(
            [
                'data' => new UserResource($user),
                'total' => 0,
                'message' => 'Contraseña reseteada correctamente.'
            ],200);
    }

    /**
    * Eliminar usuario
    */
    public function delete(Request $request, User $user){
        $user->delete();

        //////////////////AUDITORIA/////////////////////////////////////////
        AuditoriaController::auditoria_pro($request, 'Se eliminó el usuario '.$user->nombres);

        ///////////////// Evento ///////////////////////////////////
        try {
            // Conexión con el Servidor de Websocket
            Http::get(env('PUSHER_HOST').':'.env('PUSHER_PORT').'/app/'.env('PUSHER_APP_KEY'));
            event(new UserEvents(UserResource::collection(User::orderByDesc('estado')->orderBy('nombres')->orderByDesc('id')->get())));
        } catch (\Throwable $th) {
        }

            return response(
                [
                    'data' => new UserResource($user),
                    'message' => 'Eliminacion satisfactoria.'
                ], 200);
    }

    /**
    * Eliminar usuarios masivamente
    */
    public function delete_masivo(UserDeleteRequest_v1 $request)
    {
        $count = 0;
        if ($request->has('items')) {
            foreach ($request->items as $value) {
                $user = User::find($value);
                if ($user) {
                    $user->delete();
                    $count ++;
                }
            }
        }

        if ($request->all) {
            $count = User::all()->count();
            if ($request->reset) {
                User::all()->truncate();
            } else {
                User::all()->delete();
            }
        }

        //////////////////AUDITORIA/////////////////////////////////////////
        AuditoriaController::auditoria_pro($request, 'Eliminar usuarios');

        ///////////////// Evento ///////////////////////////////////
        try {
            // Conexión con el Servidor de Websocket
            Http::get(env('PUSHER_HOST').':'.env('PUSHER_PORT').'/app/'.env('PUSHER_APP_KEY'));
            event(new UserEvents(UserResource::collection(User::orderByDesc('estado')->orderBy('nombres')->orderByDesc('id')->get())));
        } catch (\Throwable $th) {
        }
        
        return response(
            [
                'message' => 'Eliminacion satisfactoria.',
                'cant' => $count
            ], 200);
    }
}


