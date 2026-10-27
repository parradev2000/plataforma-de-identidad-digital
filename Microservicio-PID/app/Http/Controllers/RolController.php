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
        
        
        /**
        * Listar roles
        * 
        * Devuelve el listado de los roles
        */
        public function list(RolListRequest_v1 $request)
        {
            $query = rol::query()->orderByDesc('id');

            if ($request->has('rol')) {
                $query->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($request->rol) . '%']);
            } elseif ($request->has('name')) {
                $query->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($request->name) . '%']);
            }

            if ($request->has('estado')) {
                $query->where('estado', $request->estado === 'active');
            }

            $cant = $query->count();

            if ($request->has('paginate')) {
                $result = $query->paginate($request->paginate);
                return RolResource::collection($result);
            } else {
                return response(
                    [
                        'data' => RolResource::collection($query->get()),
                        'total' => $cant,
                        'message' => 'Consulta satisfactoria.'
                    ], 200);
            }
        }

        /**
        * Listar un rol
        * Devuelve un rol por el ID
        */
        public function show(rol $rol)
        {
            return response()->json(
                [
                    'data' => new RolResource($rol),
                    'message' => 'Consulta satisfactoria.'
                ], 200);
        }

        /**
        * Crear un rol
        * 
        * Crea un nuevo rol
        */
        public function create(RolStoreRequest_v1 $request)
        {
            $rolName = $request->rol ?? $request->name;
            $estado = $request->has('estado') && $request->estado !== null ? $request->estado : true;
            $rol = rol::create([
                "name" => strtolower($rolName),
                "guard_name" => $request->guard_name ?? 'web',
                "estado" => $estado
            ]);

            //////////////////AUDITORIA/////////////////////////////////////////
            AuditoriaController::auditoria_pro($request, 'Se creó el rol '.$rol->name);

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

        /**
        * Crear un roles masivamente
        * 
        * Crea varios roles
        */
        public function create_masivo(RolStoreMasivoRequest_v1 $request)
        {
            $roles = collect();
            foreach ($request->items as $value) {
                $rolName = $value['rol'] ?? $value['name'] ?? null;
                if (!$rolName) {
                    return response(
                        [
                            'message' => 'Este nombre del rol ya existe.'
                        ], 422);
                }
                if (rol::whereRaw('LOWER(name) = ?', [strtolower($rolName)])->exists()) {
                    return response(
                        [
                            'message' => 'Este nombre del rol ya existe.'
                        ], 422);
                }
                $roles = $roles->push(rol::create([
                    'name' => strtolower($rolName),
                    'guard_name' => $value['guard_name'] ?? 'web',
                    'estado' => $value['estado'] ?? true,
                ]));
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

        /**
        * Actualizar un rol
        * 
        * Actualiza un rol
        */
        public function update(RolUpdateRequest_v1 $request, $rol)
        {
            $rol = rol::find($rol);
            $data = [];
            if ($request->has("rol")) {
                $data["name"] = strtolower($request->rol);
            } elseif ($request->has("name")) {
                $data["name"] = strtolower($request->name);
            }
            if ($request->has("estado")) {
                $data["estado"] = $request->estado;
            }
            $rol->update($data);

            //////////////////AUDITORIA/////////////////////////////////////////
            AuditoriaController::auditoria_pro($request, 'Se actualizó el rol '.$rol->name);

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

        /**
        * Actualizar roles masivamente
        */
        public function update_masivo(RolUpdateMasivoRequest_v1 $request)
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

        /**
        * Eliminar un rol
        * 
        * Actualiza varios roles
        */
        public function delete(Request $request, rol $rol)
        {

            if ($rol->users()->exists()) {
                return response(
                    [
                        'data' => [],
                        'message' => 'El rol '.($rol->rol ?? $rol->name).' tiene usuarios relacionados.'
                    ], 400);
            }

            $rol->delete();

            //////////////////AUDITORIA/////////////////////////////////////////
            AuditoriaController::auditoria_pro($request, 'Se eliminó el rol '.($rol->rol ?? $rol->name));

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

        /**
        * Eliminar roles masivamente
        * 
        * Elimna varios roles
        */
        public function delete_masivo(RolDeleteRequest_v1 $request)
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
                                    'message' => 'El rol '.($rol->rol ?? $rol->name).' tiene usuarios relacionados.'
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
