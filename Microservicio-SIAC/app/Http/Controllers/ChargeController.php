<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChargeListRequest_v1;
use App\Http\Requests\ChargeStoreRequest_v1;
use App\Http\Requests\ChargeUpdateRequest_v1;
use App\Http\Resources\ChargeResource;
use App\Models\charge;
use Illuminate\Http\Request;

use G4T\Swagger\Attributes\SwaggerSection;
use Illuminate\Support\Facades\Http;

#[SwaggerSection('Routes of the Charge')]
class ChargeController extends Controller
{
    ////////////////// Version 1.0 ////////////////////////
        
        // List the charges
        public function v1_list(ChargeListRequest_v1 $request)
        {
            $modelo = charge::orderByDesc('id')->get();
            if ( $request->has('name')) {
                $modelo = $modelo->intersect(charge::whereRaw('LOWER(name) LIKE ?',['%'.strtolower($request->name).'%'])->get());
            }elseif ($request->has('state')) {
                ($request->state == 'active')? $modelo = $modelo->intersect(charge::where('state', true)->get()): $modelo = $modelo->intersect(charge::where('state', false)->get());
            }
            
            $cant = $modelo->count();

            if ( $request->has('paginate')) {
                return $modelo->toQuery()->paginate($request->paginate);
            }
            else {
                return response(
                    [
                        'data' => ChargeResource::collection($modelo),
                        'total' => $cant,
                        'message' => 'Completed process.'
                    ], 200);
            }
        }

        // Show a charge
        public function v1_show(charge $school_level_id)
        {
                return response(
                    [
                        'data' => new ChargeResource($school_level_id),
                        'message' => 'Consulta satisfactoria.'
                    ], 200);
        }

        // Create a school level
        public function v1_create(ChargeStoreRequest_v1 $request)
        {
            $charge = charge::create([
                "name" => $request->name,
                "description" => $request->description,
            ]);

            //////////////////AUDITORIA/////////////////////////////////////////
            AuditoriaController::auditoria_pro($request, 'Crear cargo');

            ///////////////// Evento ///////////////////////////////////
            // try {
            //     // Conexión con el Servidor de Websocket
            //     Http::get(env('PUSHER_HOST').':'.env('PUSHER_PORT').'/app/'.env('PUSHER_APP_KEY'));
            //     event(new RolEvents(RolResource::collection(rol::orderByDesc('id')->get())));
            // } catch (\Throwable $th) {
            // }
            
                return response(
                    [
                        'data' => new ChargeResource($charge),
                        'message' => 'Completed process.'
                    ], 200);
        }

        // Update a school level
        public function v1_update(ChargeUpdateRequest_v1 $request, $charge_id)
        {
            $charge = charge::find($charge_id);
            $charge->update([
                "name" => ($request->has("name"))? $request->name: $charge->name,
                "description" => ($request->has("description"))? $request->description: $charge->description,
                "state" => ($request->has("state"))? $request->state:  $charge->state,
            ]);

            //////////////////AUDITORIA/////////////////////////////////////////
            AuditoriaController::auditoria_pro($request, 'Actualizar cargo');

            ///////////////// Evento ///////////////////////////////////
            // try {
            //     // Conexión con el Servidor de Websocket
            //     Http::get(env('PUSHER_HOST').':'.env('PUSHER_PORT').'/app/'.env('PUSHER_APP_KEY'));
            //     event(new RolEvents(RolResource::collection(rol::orderByDesc('id')->get())));
            // } catch (\Throwable $th) {
            // }

            return response(
                [
                    'data' => new ChargeResource($charge),
                    'message' => 'Completed process.'
                ], 200);
        }

        // Delete a school level
        public function v1_delete(Request $request, charge $charge_id)
        {

            // if ($rol->users()->exists()) {
            //     return response(
            //         [
            //             'data' => [],
            //             'message' => 'El rol '.$rol->rol.' tiene usuarios relacionados.'
            //         ], 400);
            // }

            $charge_id->delete();

            //////////////////AUDITORIA/////////////////////////////////////////
            AuditoriaController::auditoria_pro($request, 'Eliminar cargo');

            ///////////////// Evento ///////////////////////////////////
            // try {
            //     // Conexión con el Servidor de Websocket
            //     Http::get(env('PUSHER_HOST').':'.env('PUSHER_PORT').'/app/'.env('PUSHER_APP_KEY'));
            //     event(new RolEvents(RolResource::collection(rol::orderByDesc('id')->get())));
            // } catch (\Throwable $th) {
            // }

            return response(
                [
                    'data' => new ChargeResource($charge_id),
                    'message' => 'Completed process.'
                ], 200);
        }
}
