<?php

namespace App\Http\Controllers;

use App\Events\RolEvents;
use App\Helpers\Helpers;
use App\Http\Requests\RolDeleteRequest_v1;
use App\Http\Requests\RolListRequest_v1;
use App\Http\Requests\RolStoreMasivoRequest_v1;
use App\Http\Requests\RolStoreRequest_v1;
use App\Http\Requests\RolUpdateMasivoRequest_v1;
use App\Http\Requests\RolUpdateRequest_v1;
use App\Http\Resources\RolResource;
use App\Models\rol;
use Illuminate\Http\Request;

use G4T\Swagger\Attributes\SwaggerSection;
use Illuminate\Support\Facades\Http;

#[SwaggerSection('Rutas de los roles')]
class RolController extends Controller
{
    ////////////////// Version 1.0 ////////////////////////
        
        //Listar los roles
        public function v1_list(RolListRequest_v1 $request)
        {
            $modelo = rol::orderByDesc('id')->get();
            if ( $request->has('rol')) {
                $modelo = $modelo->intersect(rol::whereRaw('LOWER(rol) LIKE ?',['%'.strtolower($request->rol).'%'])->get());
            }elseif ($request->has('estado')) {
                ($request->estado == 'active')? $modelo = $modelo->intersect(rol::where('estado', true)->get()): $modelo = $modelo->intersect(rol::where('estado', false)->get());
            }
            
            $cant = $modelo->count();

            if ( $request->has('paginate')) {
                return $modelo->toQuery()->paginate($request->paginate);
            }
            else {
                return response(
                    [
                        'data' => RolResource::collection($modelo),
                        'total' => $cant,
                        'message' => 'Consulta satisfactoria.'
                    ], 200);
            }
        }

        //Listar un rol
        public function v1_show(rol $rol)
        {
                return response(
                    [
                        'data' => new RolResource($rol),
                        'message' => 'Consulta satisfactoria.'
                    ], 200);
        }

        //Crear un rol
        public function v1_create(RolStoreRequest_v1 $request)
        {
            $rol = rol::create([
                "rol" => $request->rol,
                "estado" => ($request->has("estado"))? $request->estado: true
            ]);

            //////////////////AUDITORIA/////////////////////////////////////////
            AuditoriaController::auditoria_pro($request, 'Crear rol');

            ///////////////// Evento ///////////////////////////////////
            try {
                // Conexión con el Servidor de Websocket
                Http::get(env('PUSHER_HOST').':'.env('PUSHER_PORT').'/app/'.env('PUSHER_APP_KEY'));
                event(new RolEvents(RolResource::collection(rol::orderByDesc('id')->get())));
            } catch (\Throwable $th) {
            }
            
                return response(
                    [
                        'data' => new RolResource($rol),
                        'message' => 'Creacion satisfactoria.'
                    ], 200);
        }

        //Crear roles
        public function v1_create_masivo(RolStoreMasivoRequest_v1 $request)
        {
            $roles = collect();
            foreach ($request->items as $value) {
                if (rol::where('rol', $value['rol'])->exists()) {
                    return response(
                        [
                            'message' => 'Este nombre del rol ya existe.'
                        ], 422);
                }
                $roles = $roles->push(rol::create($value));
            }

            //////////////////AUDITORIA/////////////////////////////////////////
            AuditoriaController::auditoria_pro($request, 'Crear roles');

            ///////////////// Evento ///////////////////////////////////
            try {
                // Conexión con el Servidor de Websocket
                Http::get(env('PUSHER_HOST').':'.env('PUSHER_PORT').'/app/'.env('PUSHER_APP_KEY'));
                event(new RolEvents(RolResource::collection(rol::orderByDesc('id')->get())));
            } catch (\Throwable $th) {
            }

            return response(
                [
                    'data' => RolResource::collection($roles),
                    'cant' => $roles->count(),
                    'message' => 'Creacion satisfactoria.'
                ], 200);
        }

        //Actulalizar un rol
        public function v1_update(RolUpdateRequest_v1 $request, $rol)
        {
            $rol = rol::find($rol);
            $rol->update([
                "rol" => ($request->has("rol"))? $request->rol: $rol->rol,
                "estado" => ($request->has("estado"))? $request->estado: $rol->estado
            ]);

            //////////////////AUDITORIA/////////////////////////////////////////
            AuditoriaController::auditoria_pro($request, 'Actualizar rol');

            ///////////////// Evento ///////////////////////////////////
            try {
                // Conexión con el Servidor de Websocket
                Http::get(env('PUSHER_HOST').':'.env('PUSHER_PORT').'/app/'.env('PUSHER_APP_KEY'));
                event(new RolEvents(RolResource::collection(rol::orderByDesc('id')->get())));
            } catch (\Throwable $th) {
            }

            return response(
                [
                    'data' => new RolResource($rol),
                    'message' => 'Actualizacion satisfactoria.'
                ], 200);
        }

        //Actualizar roles
        public function v1_update_masivo(RolUpdateMasivoRequest_v1 $request)
        {
            if ($request->has('items')) {
                $roles = rol::find($request->items);
                ($request->has("estado"))? $roles->toQuery()->update(["estado" => $request->estado]): "";
            }

            if ($request->all) {
                $roles = rol::all();
                ($request->has("estado"))? $roles->toQuery()->update(["estado" => $request->estado]): "";
            }
            
            //////////////////AUDITORIA/////////////////////////////////////////
            AuditoriaController::auditoria_pro($request, 'Actualizar roles');

            ///////////////// Evento ///////////////////////////////////
            try {
                // Conexión con el Servidor de Websocket
                Http::get(env('PUSHER_HOST').':'.env('PUSHER_PORT').'/app/'.env('PUSHER_APP_KEY'));
                event(new RolEvents(RolResource::collection(rol::orderByDesc('id')->get())));
            } catch (\Throwable $th) {
            }

            return response(
                [
                    'message' => 'Actualizacion satisfactoria.',
                    'cant' => $roles->count(),
                ], 200);
        }

        //Eliminar un rol
        public function v1_delete(Request $request, rol $rol)
        {

            if ($rol->users()->exists()) {
                return response(
                    [
                        'data' => [],
                        'message' => 'El rol '.$rol->rol.' tiene usuarios relacionados.'
                    ], 400);
            }

            $rol->delete();

            //////////////////AUDITORIA/////////////////////////////////////////
            AuditoriaController::auditoria_pro($request, 'Eliminar rol');

            ///////////////// Evento ///////////////////////////////////
            try {
                // Conexión con el Servidor de Websocket
                Http::get(env('PUSHER_HOST').':'.env('PUSHER_PORT').'/app/'.env('PUSHER_APP_KEY'));
                event(new RolEvents(RolResource::collection(rol::orderByDesc('id')->get())));
            } catch (\Throwable $th) {
            }

            return response(
                [
                    'data' => new RolResource($rol),
                    'message' => 'Eliminacion satisfactoria.'
                ], 200);
        }

        //Eliminar roles
        public function v1_delete_masivo(RolDeleteRequest_v1 $request)
        {
            $count = 0;
            if ($request->has('items')) {
                foreach ($request->items as $value) {
                    $rol = rol::find($value);
                    if ($rol) {
                        if ($rol->users()->exists()) {
                            return response(
                                [
                                    'data' => [],
                                    'message' => 'El rol '.$rol->rol.' tiene usuarios relacionados.'
                                ], 400);
                        }
                        $rol->delete();
                        $count ++;
                    }
                }
            }

            if ($request->all) {
                $count = rol::all()->count();
                if ($request->reset) {
                    rol::all()->truncate();
                } else {
                    rol::all()->delete();
                }
            }

            //////////////////AUDITORIA/////////////////////////////////////////
            AuditoriaController::auditoria_pro($request, 'Eliminar roles');

            ///////////////// Evento ///////////////////////////////////
            try {
                // Conexión con el Servidor de Websocket
                Http::get(env('PUSHER_HOST').':'.env('PUSHER_PORT').'/app/'.env('PUSHER_APP_KEY'));
                event(new RolEvents(RolResource::collection(rol::orderByDesc('id')->get())));
            } catch (\Throwable $th) {
            }
            
            return response(
                [
                    'message' => 'Eliminacion satisfactoria.',
                    'cant' => $count
                ], 200);
        }
}
