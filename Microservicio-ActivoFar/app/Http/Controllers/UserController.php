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
use App\Http\Requests\UserRegisterAllRequest_v1;
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
    //Listar usuarios
    public function v1_list(UserListRequest_v1 $request)
    {
        $modelo = User::orderByDesc('estado')->orderBy('nombres')->orderByDesc('id')->get();
        if ( $request->has('nombres')) {
            $modelo = $modelo->intersect(User::whereRaw('LOWER(nombres) LIKE ?',['%'.strtolower($request->nombres).'%'])->get());
        }elseif ($request->has('usuario')) {
            $modelo = $modelo->intersect(User::whereRaw('LOWER(usuario) LIKE ?',['%'.strtolower($request->usuario).'%'])->get());
        }elseif ($request->has('carne')) {
            $modelo = $modelo->intersect(User::whereRaw('LOWER(carne) LIKE ?',['%'.strtolower($request->carne).'%'])->get());
        }elseif ($request->has('rol_id')) {
            $modelo = $modelo->intersect(User::where('rol_id', $request->rol_id)->get());
        }elseif ($request->has('estado')) {
            ($request->estado == 'active')? $modelo = $modelo->intersect(User::where('estado', true)->get()): $modelo = $modelo->intersect(User::where('estado', false)->get());
        }
        $cant = $modelo->count();

        $pag = $request->paginate;
        if ( $pag> 0) {
            return $modelo->toQuery()->paginate($pag);
        } else {
            return response(
                [
                    'data' => UserResource::collection($modelo),
                    'total' => $cant,
                    'message' => 'Consulta satisfactoria.'
                ],200);
        }
    }

    //Listar un rol
    public function v1_show(User $user)
    {
            return response(
                [
                    'data' => new UserResource($user),
                    'message' => 'Consulta satisfactoria.'
                ], 200);
    }

    //Registrar un usuario(usuario => (iniciales del nombre) * (los primeros 6 digitos del carne) contraseña => (carnet de identidad) )
    public function v1_register(UserRegistrerRequest_v1 $request)
    {
        //Conformar el nombre de usuario
        $palabra = "";
        $palabras = explode(" ", $request->nombres);

        foreach ($palabras as $palabraActual) {
            $inicial = substr($palabraActual, 0, 1);
            $palabra .= $inicial;
        }
        $carne = substr($request->carne, 0, 6);
        $nomb_usuario = $palabra.'.'.$carne;

        $user = new User();
        $user->nombres = $request->nombres;
        $user->usuario = $nomb_usuario;
        $user->carne = $request->carne;
        $user->rol_id = $request->rol_id;
        $user->password_expiry = $user->password_expire();
        $user->password = Hash::make(md5($request->carne));
        $user->save();

        //////////////////AUDITORIA/////////////////////////////////////////
        AuditoriaController::auditoria_pro($request, 'Registrar usuario');


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

    //Registrar un usuario
    public function v1_register_all(UserRegisterAllRequest_v1 $request)
    {
        $user = new User();
        $user->nombres = $request->nombres;
        $user->usuario = $request->usuario;
        $user->carne = $request->carne;
        $user->rol_id = $request->rol_id;
        $user->password_expiry = $user->password_expire();
        $user->password = Hash::make(md5('User*2024'));
        $user->save();

        //////////////////AUDITORIA/////////////////////////////////////////
        AuditoriaController::auditoria_pro($request, 'Registrar usuario');

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
   
    //Actualizar usuarios
    public function v1_update(UserUpdateRequest_v1 $request, $user)
    {
        $user = User::find($user);
            $user->update([
                "nombres" => ($request->has("nombres"))? $request->nombres: $user->nombres,
                "usuario" => ($request->has("usuario"))? $request->usuario: $user->usuario,
                "carne" => ($request->has("carne"))? $request->carne: $user->carne,
                "rol_id" => ($request->has("rol_id"))? $request->rol_id: $user->rol_id,
                "estado" => ($request->has("estado"))? $request->estado: $user->estado
            ]);

        //////////////////AUDITORIA/////////////////////////////////////////
        AuditoriaController::auditoria_pro($request, 'Actualizar usuario');

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

    //Actualizar roles
    public function v1_update_masivo(UserUpdateMasivoRequest_v1 $request)
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

    //Cambiar la contraseña a un usuario
    public function v1_change_password(UserUpdatePasswordRequest_v1 $request, User $user)
    {
        $user->update([
            "password" => Hash::make($request->new_password),
            "password_expiry" => $user->password_expire(),
            "int" => 0,
            "estado" => true,
        ]);

        //////////////////AUDITORIA/////////////////////////////////////////
        AuditoriaController::auditoria_pro($request, 'Cambiar Contraseña');

        return response(
            [
                'data' => new UserResource($user),
                'total' => 0,
                'message' => 'Contraseña cambiada correctamente.'
            ],200);
    }

    //Resetear contraseña a un usuario(User*2024)
    public function v1_reset_password(Request $request, User $user)
    {
        $user->update([
            "password" => Hash::make(md5('User*2024')),
            "password_expiry" => $user->password_expire(),
            "int" => 0,
            "estado" => true,
        ]);

        //////////////////AUDITORIA/////////////////////////////////////////
        AuditoriaController::auditoria_pro($request, 'Resetear contraseña');

        return response(
            [
                'data' => new UserResource($user),
                'total' => 0,
                'message' => 'Contraseña reseteada correctamente.'
            ],200);
    }

    //Eliminar usuarios
    public function v1_delete(Request $request, User $user){
        $user->delete();

        //////////////////AUDITORIA/////////////////////////////////////////
        AuditoriaController::auditoria_pro($request, 'Eliminar usuario');

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

    //Eliminar usuarios
    public function v1_delete_masivo(UserDeleteRequest_v1 $request)
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


