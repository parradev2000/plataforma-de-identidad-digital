<?php

namespace App\Http\Controllers;

use App\Http\Requests\SocialNetworkListRequest_v1;
use App\Http\Requests\SocialNetworkStoreRequest_v1;
use App\Http\Requests\SocialNetworkUpdateRequest_v1;
use App\Http\Resources\SocialNetworkResource;
use App\Models\social_network;
use Illuminate\Http\Request;

use G4T\Swagger\Attributes\SwaggerSection;
use Illuminate\Support\Facades\Http;

#[SwaggerSection('Routes of the Social Networks')]
class SocialNetworkController extends Controller
{
    ////////////////// Version 1.0 ////////////////////////
        
        // List the social_network
        public function v1_list(SocialNetworkListRequest_v1 $request)
        {
            $modelo = social_network::orderByDesc('id')->get();
            if ( $request->has('name')) {
                $modelo = $modelo->intersect(social_network::whereRaw('LOWER(name) LIKE ?',['%'.strtolower($request->name).'%'])->get());
            }
            
            $cant = $modelo->count();

            if ( $request->has('paginate')) {
                return $modelo->toQuery()->paginate($request->paginate);
            }
            else {
                return response(
                    [
                        'data' => SocialNetworkResource::collection($modelo),
                        'total' => $cant,
                        'message' => 'Completed process.'
                    ], 200);
            }
        }

        // Show a social_network
        public function v1_show(social_network $social_network)
        {
                return response(
                    [
                        'data' => new SocialNetworkResource($social_network),
                        'message' => 'Consulta satisfactoria.'
                    ], 200);
        }

        // Create a social_network
        public function v1_create(SocialNetworkStoreRequest_v1 $request)
        {
            $social_network = social_network::create([
                "name" => $request->name,
            ]);

            //////////////////AUDITORIA/////////////////////////////////////////
            AuditoriaController::auditoria_pro($request, 'Crear red social');
            
            return response(
                [
                    'data' => new SocialNetworkResource($social_network),
                    'message' => 'Completed process.'
                ], 200);
        }

        // Update a social_network
        public function v1_update(SocialNetworkUpdateRequest_v1 $request, $social_network_id)
        {
            $social_network = social_network::find($social_network_id);
            $social_network->update([
                "name" => ($request->has("name"))? $request->name: $social_network->name,
            ]);

            //////////////////AUDITORIA/////////////////////////////////////////
            AuditoriaController::auditoria_pro($request, 'Actualizar red social');

            return response(
                [
                    'data' => new SocialNetworkResource($social_network),
                    'message' => 'Completed process.'
                ], 200);
        }

        // Delete a social_network
        public function v1_delete(Request $request, social_network $social_network)
        {
            $social_network->delete();

            //////////////////AUDITORIA/////////////////////////////////////////
            AuditoriaController::auditoria_pro($request, 'Eliminar red social');

            return response(
                [
                    'data' => new SocialNetworkResource($social_network),
                    'message' => 'Completed process.'
                ], 200);
        }
}
