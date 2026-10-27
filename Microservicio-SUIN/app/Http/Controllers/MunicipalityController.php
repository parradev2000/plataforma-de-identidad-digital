<?php

namespace App\Http\Controllers;

use App\Http\Requests\MunicipalityListRequest_v1;
use App\Http\Requests\MunicipalityStoreRequest_v1;
use App\Http\Requests\MunicipalityUpdateRequest_v1;
use App\Http\Resources\MunicipalityResource;
use App\Models\municipality;
use App\Models\province;
use Illuminate\Http\Request;

use G4T\Swagger\Attributes\SwaggerSection;
use Illuminate\Support\Facades\Http;

#[SwaggerSection('Routes of the municipalities')]
class MunicipalityController extends Controller
{
    ////////////////// Version 1.0 ////////////////////////
        
        // List the municipalities
        public function v1_list(MunicipalityListRequest_v1 $request)
        {
            $modelo = municipality::orderByDesc('id')->get();
            if ( $request->has('name')) {
                $modelo = $modelo->intersect(municipality::whereRaw('LOWER(name) LIKE ?',['%'.strtolower($request->name).'%'])->get());
            }elseif ($request->has('state')) {
                ($request->state == 'active')? $modelo = $modelo->intersect(municipality::where('state', true)->get()): $modelo = $modelo->intersect(municipality::where('state', false)->get());
            }elseif ($request->has('province_id')) {
                $modelo = $modelo->intersect(municipality::where('province_id', $request->province_id)->get());
            }
            
            $cant = $modelo->count();

            if ( $request->has('paginate')) {
                return $modelo->toQuery()->paginate($request->paginate);
            }
            else {
                return response(
                    [
                        'data' => MunicipalityResource::collection($modelo),
                        'total' => $cant,
                        'message' => 'Successful consultation.'
                    ], 200);
            }
        }

        //Show a municipality
        public function v1_show(municipality $municipality)
        {
                return response(
                    [
                        'data' => new MunicipalityResource($municipality),
                        'message' => 'Successful consultation.'
                    ], 200);
        }

        //Create a municipality
        public function v1_create(MunicipalityStoreRequest_v1 $request)
        {
            if (!province::find($request->province_id)) {
                return response(['message' => 'The province does not exist'], 422);
            }

            $municipality = municipality::create([
                "name" => $request->name,
                "province_id" => $request->province_id,
                "state" => ($request->has("state"))? $request->state: true
            ]);

            //////////////////AUDITORIA/////////////////////////////////////////
            AuditoriaController::auditoria_pro($request, 'Crear un municipio');

            ///////////////// Evento ///////////////////////////////////
            // try {
            //     // Conexión con el Servidor de Websocket
            //     Http::get(env('PUSHER_HOST').':'.env('PUSHER_PORT').'/app/'.env('PUSHER_APP_KEY'));
            //     event(new RolEvents(RolResource::collection(rol::orderByDesc('id')->get())));
            // } catch (\Throwable $th) {
            // }
            
            return response(
                [
                    'data' => new MunicipalityResource($municipality),
                    'message' => 'Successful creation.'
                ], 201);
        }

        //Update a municipality
        public function v1_update(MunicipalityUpdateRequest_v1 $request, $municipality)
        {
            if (!province::find($request->province_id) && $request->has('province_id')) {
                return response(['message' => 'The province does not exist'], 422);
            }

            $municipality = municipality::find($municipality);
            $municipality->update([
                "name" => ($request->has("name"))? $request->name: $municipality->name,
                "province_id" => ($request->has("province_id"))? $request->province_id: $municipality->province_id,
                "state" => ($request->has("state"))? $request->state: $municipality->state
            ]);

            //////////////////AUDITORIA/////////////////////////////////////////
            AuditoriaController::auditoria_pro($request, 'Actualizar un municipio');

            ///////////////// Evento ///////////////////////////////////
            // try {
            //     // Conexión con el Servidor de Websocket
            //     Http::get(env('PUSHER_HOST').':'.env('PUSHER_PORT').'/app/'.env('PUSHER_APP_KEY'));
            //     event(new RolEvents(RolResource::collection(rol::orderByDesc('id')->get())));
            // } catch (\Throwable $th) {
            // }

            return response(
                [
                    'data' => new MunicipalityResource($municipality),
                    'message' => 'Update successful.'
                ], 200);
        }

        //Delete a municipality
        public function v1_delete(Request $request, municipality $municipality)
        {
            $municipality->delete();

            //////////////////AUDITORIA/////////////////////////////////////////
            AuditoriaController::auditoria_pro($request, 'Eliminar provincia');

            ///////////////// Evento ///////////////////////////////////
            // try {
            //     // Conexión con el Servidor de Websocket
            //     Http::get(env('PUSHER_HOST').':'.env('PUSHER_PORT').'/app/'.env('PUSHER_APP_KEY'));
            //     event(new RolEvents(RolResource::collection(rol::orderByDesc('id')->get())));
            // } catch (\Throwable $th) {
            // }

            return response(
                [
                    'data' => new MunicipalityResource($municipality),
                    'message' => 'Successful delete.'
                ], 200);
        }
}
