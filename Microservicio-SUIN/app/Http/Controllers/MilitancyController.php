<?php

namespace App\Http\Controllers;

use App\Events\RolEvents;
use App\Helpers\Helpers;
use App\Http\Requests\MilitancyListRequest_v1;
use App\Http\Requests\MilitancyStoreRequest_v1;
use App\Http\Requests\MilitancyUpdateRequest_v1;
use App\Http\Requests\RolDeleteRequest_v1;
use App\Http\Requests\RolListRequest_v1;
use App\Http\Requests\RolStoreMasivoRequest_v1;
use App\Http\Requests\RolStoreRequest_v1;
use App\Http\Requests\RolUpdateMasivoRequest_v1;
use App\Http\Requests\RolUpdateRequest_v1;
use App\Http\Resources\MilitancyResource;
use App\Http\Resources\RolResource;
use App\Models\militancy;
use App\Models\rol;
use Illuminate\Http\Request;

use G4T\Swagger\Attributes\SwaggerSection;
use Illuminate\Support\Facades\Http;

#[SwaggerSection('Routes of the militancy')]
class MilitancyController extends Controller
{
    ////////////////// Version 1.0 ////////////////////////
        
        // List the militancy
        public function v1_list(MilitancyListRequest_v1 $request)
        {
            $modelo = militancy::orderByDesc('id')->get();
            if ( $request->has('name')) {
                $modelo = $modelo->intersect(militancy::whereRaw('LOWER(name) LIKE ?',['%'.strtolower($request->name).'%'])->get());
            }elseif ($request->has('state')) {
                ($request->state == 'active')? $modelo = $modelo->intersect(militancy::where('state', true)->get()): $modelo = $modelo->intersect(militancy::where('state', false)->get());
            }
            
            $cant = $modelo->count();

            if ( $request->has('paginate')) {
                return $modelo->toQuery()->paginate($request->paginate);
            }
            else {
                return response(
                    [
                        'data' => MilitancyResource::collection($modelo),
                        'total' => $cant,
                        'message' => 'Completed process.'
                    ], 200);
            }
        }

        // Show a militancy
        public function v1_show(militancy $militancy_id)
        {
                return response(
                    [
                        'data' => new MilitancyResource($militancy_id),
                        'message' => 'Completed process.'
                    ], 200);
        }

        //Create a militancy
        public function v1_create(MilitancyStoreRequest_v1 $request)
        {
            $militancy = militancy::create([
                "name" => $request->name,
            ]);

            //////////////////AUDITORIA/////////////////////////////////////////
            AuditoriaController::auditoria_pro($request, 'Crear militancia');

            ///////////////// Evento ///////////////////////////////////
            // try {
            //     // Conexión con el Servidor de Websocket
            //     Http::get(env('PUSHER_HOST').':'.env('PUSHER_PORT').'/app/'.env('PUSHER_APP_KEY'));
            //     event(new RolEvents(RolResource::collection(rol::orderByDesc('id')->get())));
            // } catch (\Throwable $th) {
            // }
            
                return response(
                    [
                        'data' => new MilitancyResource($militancy),
                        'message' => 'Completed process.'
                    ], 200);
        }

        // Update a militancy
        public function v1_update(MilitancyUpdateRequest_v1 $request, $militancy_id)
        {
            $militancy = militancy::find($militancy_id);
            $militancy->update([
                "name" => ($request->has("name")) ? $request->name : $militancy->name,
                "state" => ($request->has("state")) ? $request->state : $militancy->state
            ]);

            //////////////////AUDITORIA/////////////////////////////////////////
            AuditoriaController::auditoria_pro($request, 'Actualizar rol');

            ///////////////// Evento ///////////////////////////////////
            // try {
            //     // Conexión con el Servidor de Websocket
            //     Http::get(env('PUSHER_HOST').':'.env('PUSHER_PORT').'/app/'.env('PUSHER_APP_KEY'));
            //     event(new RolEvents(RolResource::collection(rol::orderByDesc('id')->get())));
            // } catch (\Throwable $th) {
            // }

            return response(
                [
                    'data' => new MilitancyResource($militancy),
                    'message' => 'Completed process.'
                ], 200);
        }

        // Delete a militancy
        public function v1_delete(Request $request, militancy $militancy_id)
        {

            // if ($rol->users()->exists()) {
            //     return response(
            //         [
            //             'data' => [],
            //             'message' => 'El rol '.$rol->rol.' tiene usuarios relacionados.'
            //         ], 400);
            // }

            $militancy_id->delete();

            //////////////////AUDITORIA/////////////////////////////////////////
            AuditoriaController::auditoria_pro($request, 'Eliminar militancia');

            ///////////////// Evento ///////////////////////////////////
            // try {
            //     // Conexión con el Servidor de Websocket
            //     Http::get(env('PUSHER_HOST').':'.env('PUSHER_PORT').'/app/'.env('PUSHER_APP_KEY'));
            //     event(new RolEvents(RolResource::collection(rol::orderByDesc('id')->get())));
            // } catch (\Throwable $th) {
            // }

            return response(
                [
                    'data' => new MilitancyResource($militancy_id),
                    'message' => 'Completed process.'
                ], 200);
        }
}
