<?php

namespace App\Http\Controllers;

use App\Http\Requests\EntityListRequest_v1;
use App\Http\Requests\EntityStoreRequest_v1;
use App\Http\Requests\EntityUpdateRequest_v1;
use App\Http\Requests\YearListRequest_v1;
use App\Http\Requests\YearStoreRequest_v1;
use App\Http\Requests\YearUpdateRequest_v1;
use App\Http\Resources\EntityResource;
use App\Http\Resources\ProvinceResource;
use App\Http\Resources\YearResource;
use App\Models\entity;
use App\Models\province;
use App\Models\year;
use Illuminate\Http\Request;

use G4T\Swagger\Attributes\SwaggerSection;

#[SwaggerSection('Routes of the entities')]
class EntityController extends Controller
{
    ////////////////// Version 1.0 ////////////////////////
        
        //List the entities
        public function v1_list(EntityListRequest_v1 $request)
        {
            $modelo = entity::orderByDesc('id')->get();

            if ( $request->has('name')) {
                $modelo = $modelo->intersect(entity::where('name', $request->name)->get());
            }elseif ( $request->has('description')) {
                $modelo = $modelo->intersect(entity::where('description', $request->description)->get());
            }
            
            $cant = $modelo->count();

            if ( $request->has('paginate')) {
                return $modelo->toQuery()->paginate($request->paginate);
            }
            else {
                return response(
                    [
                        'data' => EntityResource::collection($modelo),
                        'total' => $cant,
                        'message' => 'Successful consultation.'
                    ], 200);
            }
        }

        //Show a entity
        public function v1_show(entity $entity)
        {
                return response(
                    [
                        'data' => new ProvinceResource($entity),
                        'message' => 'Successful consultation.'
                    ], 200);
        }

        //Create a entity
        public function v1_create(EntityStoreRequest_v1 $request)
        {
            $entity = entity::create([
                "name" => $request->name,
                "description" => $request->description,
            ]);

            //////////////////AUDITORIA/////////////////////////////////////////
            AuditoriaController::auditoria_pro($request, 'Crear una entidad');

            ///////////////// Evento ///////////////////////////////////
            // try {
            //     // Conexión con el Servidor de Websocket
            //     Http::get(env('PUSHER_HOST').':'.env('PUSHER_PORT').'/app/'.env('PUSHER_APP_KEY'));
            //     event(new RolEvents(RolResource::collection(rol::orderByDesc('id')->get())));
            // } catch (\Throwable $th) {
            // }
            
            return response(
                [
                    // 'data' => new EntityResource($entity),
                    'message' => 'Successful creation.'
                ], 200);
        }

        //Update a entity
        public function v1_update(EntityUpdateRequest_v1 $request, $entity)
        {
            $entity = entity::find($entity);
            $entity->update([
                "name" => ($request->has("name"))? $request->name: $entity->name,
                "description" => ($request->has("description"))? $request->description: $entity->description,
                "state" => ($request->has("state"))? $request->state: $entity->state,
            ]);

            //////////////////AUDITORIA/////////////////////////////////////////
            AuditoriaController::auditoria_pro($request, 'Actualizar entidad');

            ///////////////// Evento ///////////////////////////////////
            // try {
            //     // Conexión con el Servidor de Websocket
            //     Http::get(env('PUSHER_HOST').':'.env('PUSHER_PORT').'/app/'.env('PUSHER_APP_KEY'));
            //     event(new RolEvents(RolResource::collection(rol::orderByDesc('id')->get())));
            // } catch (\Throwable $th) {
            // }

            return response(
                [
                    // 'data' => new YearResource($year),
                    'message' => 'Update successful.'
                ], 200);
        }

        //Delete a entity
        public function v1_delete(Request $request, entity $entity)
        {
            $entity->delete();

            //////////////////AUDITORIA/////////////////////////////////////////
            AuditoriaController::auditoria_pro($request, 'Eliminar entidad');

            ///////////////// Evento ///////////////////////////////////
            // try {
            //     // Conexión con el Servidor de Websocket
            //     Http::get(env('PUSHER_HOST').':'.env('PUSHER_PORT').'/app/'.env('PUSHER_APP_KEY'));
            //     event(new RolEvents(RolResource::collection(rol::orderByDesc('id')->get())));
            // } catch (\Throwable $th) {
            // }

            return response(
                [
                    // 'data' => new EntityResource($entity),
                    'message' => 'Successful delete.'
                ], 200);
        }
}
