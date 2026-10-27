<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChargeListRequest_v1;
use App\Http\Requests\ChargeStoreRequest_v1;
use App\Http\Requests\ChargeUpdateRequest_v1;
use App\Http\Resources\ChargeResource;
use App\Models\charge_anec;
use Illuminate\Http\Request;

use G4T\Swagger\Attributes\SwaggerSection;
use Illuminate\Support\Facades\Http;

#[SwaggerSection('Routes of the Charge ANEC')]
class ChargeAnecController extends Controller
{
    ////////////////// Version 1.0 ////////////////////////
        
        // List the charges
        public function v1_list(ChargeListRequest_v1 $request)
        {
            $modelo = charge_anec::orderByDesc('id')->get();
            if ( $request->has('name')) {
                $modelo = $modelo->intersect(charge_anec::whereRaw('LOWER(name) LIKE ?',['%'.strtolower($request->name).'%'])->get());
            }elseif ($request->has('state')) {
                ($request->state == 'active')? $modelo = $modelo->intersect(charge_anec::where('state', true)->get()): $modelo = $modelo->intersect(charge_anec::where('state', false)->get());
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

        // Show a charge_anec
        public function v1_show(charge_anec $school_level_id)
        {
                return response(
                    [
                        'data' => new ChargeResource($school_level_id),
                        'message' => 'Consulta satisfactoria.'
                    ], 200);
        }

        // Create a charge_anec
        public function v1_create(ChargeStoreRequest_v1 $request)
        {
            charge_anec::create([
                "name" => $request->name,
                "description" => $request->description,
            ]);

            //////////////////AUDITORIA/////////////////////////////////////////
            AuditoriaController::auditoria_pro($request, 'Crear cargo de la ANEC');

                return response(
                    [
                        'message' => 'Completed process.'
                    ], 200);
        }

        // Update a charge_anec
        public function v1_update(ChargeUpdateRequest_v1 $request, $charge_anec)
        {
            $charge_anec = charge_anec::find($charge_anec);
            $charge_anec->update([
                "name" => ($request->has("name"))? $request->name: $charge_anec->name,
                "description" => ($request->has("description"))? $request->description: $charge_anec->description,
                "state" => ($request->has("state"))? $request->state:  $charge_anec->state,
            ]);

            //////////////////AUDITORIA/////////////////////////////////////////
            AuditoriaController::auditoria_pro($request, 'Actualizar cargo de la ANEC');

            return response(
                [
                    'message' => 'Completed process.'
                ], 200);
        }

        // Delete a charge_anec
        public function v1_delete(Request $request, charge_anec $charge_anec)
        {

            $charge_anec->delete();

            //////////////////AUDITORIA/////////////////////////////////////////
            AuditoriaController::auditoria_pro($request, 'Eliminar cargo de la ANEC');

            return response(
                [
                    // 'data' => new ChargeResource($charge_id),
                    'message' => 'Completed process.'
                ], 200);
        }
}
