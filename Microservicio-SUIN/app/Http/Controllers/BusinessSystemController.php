<?php

namespace App\Http\Controllers;

use App\Http\Requests\BusinessSystemListRequest_v1;
use App\Http\Requests\BusinessSystemStoreRequest_v1;
use App\Http\Requests\ChargeUpdateRequest_v1;
use App\Http\Resources\ChargeResource;
use App\Models\business_system;
use Illuminate\Http\Request;

use G4T\Swagger\Attributes\SwaggerSection;
use Illuminate\Support\Facades\Http;

#[SwaggerSection('Routes of the Business System ')]
class BusinessSystemController extends Controller
{
    ////////////////// Version 1.0 ////////////////////////
        
        // List the charges
        public function v1_list(BusinessSystemListRequest_v1 $request)
        {
            $modelo = business_system::orderByDesc('id')->get();
            if ( $request->has('name')) {
                $modelo = $modelo->intersect(business_system::whereRaw('LOWER(name) LIKE ?',['%'.strtolower($request->name).'%'])->get());
            }elseif ( $request->has('description')) {
                $modelo = $modelo->intersect(business_system::whereRaw('LOWER(description) LIKE ?',['%'.strtolower($request->description).'%'])->get());
            }elseif ($request->has('state')) {
                ($request->state == 'active')? $modelo = $modelo->intersect(business_system::where('state', true)->get()): $modelo = $modelo->intersect(business_system::where('state', false)->get());
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

        // Show a business_system
        public function v1_show(business_system $business_system)
        {
            return response(
                [
                    'data' => new ChargeResource($business_system),
                    'message' => 'Consulta satisfactoria.'
                ], 200);
        }

        // Create a business_system
        public function v1_create(BusinessSystemStoreRequest_v1 $request)
        {
            $business_system = business_system::create([
                "name" => $request->name,
                "description" => $request->description,
            ]);

            //////////////////AUDITORIA/////////////////////////////////////////
            AuditoriaController::auditoria_pro($request, 'Crear organizacion del sistema empresarial');
            
            return response(
                [
                    'data' => new ChargeResource($business_system),
                    'message' => 'Completed process.'
                ], 200);
        }

        // Update a business_system
        public function v1_update(ChargeUpdateRequest_v1 $request, $charge_id)
        {
            $business_system = business_system::find($charge_id);
            $business_system->update([
                "name" => ($request->has("name"))? $request->name: $business_system->name,
                "description" => ($request->has("description"))? $request->description: $business_system->description,
                "state" => ($request->has("state"))? $request->state:  $business_system->state,
            ]);

            //////////////////AUDITORIA/////////////////////////////////////////
            AuditoriaController::auditoria_pro($request, 'Actualizar cargo');

            return response(
                [
                    'data' => new ChargeResource($business_system),
                    'message' => 'Completed process.'
                ], 200);
        }

        // Delete a business_system
        public function v1_delete(Request $request, business_system $charge_id)
        {
            $charge_id->delete();

            //////////////////AUDITORIA/////////////////////////////////////////
            AuditoriaController::auditoria_pro($request, 'Eliminar cargo');

            return response(
                [
                    'data' => new ChargeResource($charge_id),
                    'message' => 'Completed process.'
                ], 200);
        }
}
