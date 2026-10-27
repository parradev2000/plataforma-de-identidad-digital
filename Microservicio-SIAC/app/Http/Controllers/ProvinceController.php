<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProvinceListRequest_v1;
use App\Http\Requests\ProvinceStoreRequest_v1;
use App\Http\Requests\ProvinceUpdateRequest_v1;
use App\Http\Resources\ProvinceResource;
use App\Models\province;
use Illuminate\Http\Request;

use G4T\Swagger\Attributes\SwaggerSection;

#[SwaggerSection('Routes of the municipalities')]
class ProvinceController extends Controller
{
    ////////////////// Version 1.0 ////////////////////////
        
        // List the provinces
        public function v1_list(ProvinceListRequest_v1 $request)
        {
            $modelo = province::orderByDesc('id')->get();
            if ( $request->has('name')) {
                $modelo = $modelo->intersect(province::whereRaw('LOWER(name) LIKE ?',['%'.strtolower($request->name).'%'])->get());
            }elseif ($request->has('state')) {
                ($request->state == 'active')? $modelo = $modelo->intersect(province::where('state', true)->get()): $modelo = $modelo->intersect(province::where('state', false)->get());
            }
            
            $cant = $modelo->count();

            if ( $request->has('paginate')) {
                return $modelo->toQuery()->paginate($request->paginate);
            }
            else {
                return response(
                    [
                        'data' => ProvinceResource::collection($modelo),
                        'total' => $cant,
                        'message' => 'Successful consultation.'
                    ], 200);
            }
        }

        //Show a province
        public function v1_show(province $province)
        {
                return response(
                    [
                        'data' => new ProvinceResource($province),
                        'message' => 'Successful consultation.'
                    ], 200);
        }

        //Create a province
        public function v1_create(ProvinceStoreRequest_v1 $request)
        {
            $province = province::create([
                "name" => $request->name,
                "state" => ($request->has("state"))? $request->state: true
            ]);

            //////////////////AUDITORIA/////////////////////////////////////////
            AuditoriaController::auditoria_pro($request, 'Crear una provincia');

            ///////////////// Evento ///////////////////////////////////
            // try {
            //     // Conexión con el Servidor de Websocket
            //     Http::get(env('PUSHER_HOST').':'.env('PUSHER_PORT').'/app/'.env('PUSHER_APP_KEY'));
            //     event(new RolEvents(RolResource::collection(rol::orderByDesc('id')->get())));
            // } catch (\Throwable $th) {
            // }
            
            return response(
                [
                    'data' => new ProvinceResource($province),
                    'message' => 'Successful creation.'
                ], 200);
        }

        //Update a province
        public function v1_update(ProvinceUpdateRequest_v1 $request, $province)
        {
            $province = province::find($province);
            $province->update([
                "name" => ($request->has("name"))? $request->name: $province->name,
                "state" => ($request->has("state"))? $request->state: $province->state
            ]);

            //////////////////AUDITORIA/////////////////////////////////////////
            AuditoriaController::auditoria_pro($request, 'Actualizar provincia');

            ///////////////// Evento ///////////////////////////////////
            // try {
            //     // Conexión con el Servidor de Websocket
            //     Http::get(env('PUSHER_HOST').':'.env('PUSHER_PORT').'/app/'.env('PUSHER_APP_KEY'));
            //     event(new RolEvents(RolResource::collection(rol::orderByDesc('id')->get())));
            // } catch (\Throwable $th) {
            // }

            return response(
                [
                    'data' => new ProvinceResource($province),
                    'message' => 'Update successful.'
                ], 200);
        }

        //Delete a province
        public function v1_delete(Request $request, province $province)
        {
            $province->delete();

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
                    'data' => new ProvinceResource($province),
                    'message' => 'Successful delete.'
                ], 200);
        }
}
