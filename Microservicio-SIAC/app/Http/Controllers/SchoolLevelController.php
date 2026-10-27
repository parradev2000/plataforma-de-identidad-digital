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
use App\Http\Requests\SchoolLevelListRequest_v1;
use App\Http\Requests\SchoolLevelUpdateRequest_v1;
use App\Http\Requests\SchoolLeveStoreRequest_v1;
use App\Http\Resources\RolResource;
use App\Http\Resources\SchoolLevelResource;
use App\Models\rol;
use App\Models\school_level;
use Illuminate\Http\Request;

use G4T\Swagger\Attributes\SwaggerSection;
use Illuminate\Support\Facades\Http;

#[SwaggerSection('Routes of the school levels')]
class SchoolLevelController extends Controller
{
    ////////////////// Version 1.0 ////////////////////////
        
        // List the school levels
        public function v1_list(SchoolLevelListRequest_v1 $request)
        {
            $modelo = school_level::orderByDesc('id')->get();
            if ( $request->has('name')) {
                $modelo = $modelo->intersect(school_level::whereRaw('LOWER(name) LIKE ?',['%'.strtolower($request->name).'%'])->get());
            }elseif ($request->has('state')) {
                ($request->state == 'active')? $modelo = $modelo->intersect(school_level::where('state', true)->get()): $modelo = $modelo->intersect(school_level::where('state', false)->get());
            }
            
            $cant = $modelo->count();

            if ( $request->has('paginate')) {
                return $modelo->toQuery()->paginate($request->paginate);
            }
            else {
                return response(
                    [
                        'data' => SchoolLevelResource::collection($modelo),
                        'total' => $cant,
                        'message' => 'Completed process.'
                    ], 200);
            }
        }

        // Show a school level
        public function v1_show(school_level $school_level_id)
        {
                return response(
                    [
                        'data' => new SchoolLevelResource($school_level_id),
                        'message' => 'Consulta satisfactoria.'
                    ], 200);
        }

        // Create a school level
        public function v1_create(SchoolLeveStoreRequest_v1 $request)
        {
            $school_level = school_level::create([
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
                        'data' => new SchoolLevelResource($school_level),
                        'message' => 'Completed process.'
                    ], 200);
        }

        // Update a school level
        public function v1_update(SchoolLevelUpdateRequest_v1 $request, $school_level_id)
        {
            $school_level = school_level::find($school_level_id);
            $school_level->update([
                "name" => ($request->has("name"))? $request->name: $school_level->name,
                "state" => ($request->has("state"))? $request->state:  $school_level->state,
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
                    'data' => new SchoolLevelResource($school_level),
                    'message' => 'Completed process.'
                ], 200);
        }

        // Delete a school level
        public function v1_delete(Request $request, school_level $school_level_id)
        {

            // if ($rol->users()->exists()) {
            //     return response(
            //         [
            //             'data' => [],
            //             'message' => 'El rol '.$rol->rol.' tiene usuarios relacionados.'
            //         ], 400);
            // }

            $school_level_id->delete();

            //////////////////AUDITORIA/////////////////////////////////////////
            AuditoriaController::auditoria_pro($request, 'Eliminar rol');

            ///////////////// Evento ///////////////////////////////////
            // try {
            //     // Conexión con el Servidor de Websocket
            //     Http::get(env('PUSHER_HOST').':'.env('PUSHER_PORT').'/app/'.env('PUSHER_APP_KEY'));
            //     event(new RolEvents(RolResource::collection(rol::orderByDesc('id')->get())));
            // } catch (\Throwable $th) {
            // }

            return response(
                [
                    'data' => new SchoolLevelResource($school_level_id),
                    'message' => 'Completed process.'
                ], 200);
        }
}
