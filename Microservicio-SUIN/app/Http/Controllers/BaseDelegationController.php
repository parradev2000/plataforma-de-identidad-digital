<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseDelegationListRequest_v1;
use App\Http\Requests\BaseDelegationStoreRequest_v1;
use App\Http\Requests\BaseDelegationUpdateRequest_v1;
use App\Http\Resources\BaseDelegationResource;
use App\Models\base_delegation;
use Illuminate\Http\Request;

class BaseDelegationController  extends Controller
{
    // Returns entities
    public function v1_list(BaseDelegationListRequest_v1 $request){
        $modelo = base_delegation::where('father', 1)->orderByDesc('id')->get();

        if ($request->all()) {
            $modelo = base_delegation::orderByDesc('id')->get();
            if ( $request->has('name')) {
                $modelo = $modelo->intersect(base_delegation::whereRaw('LOWER(name) LIKE ?',['%'.strtolower($request->name).'%'])->get());
            }elseif ( $request->has('description')) {
                $modelo = $modelo->intersect(base_delegation::whereRaw('LOWER(description) LIKE ?',['%'.strtolower($request->description).'%'])->get());
            }elseif ( $request->has('father')) {
                $modelo = $modelo->intersect(base_delegation::where('father', $request->father_id)->get());
            }elseif ($request->has('state')) {
                ($request->state == 'active')? $modelo = $modelo->intersect(base_delegation::where('state', true)->get()): $modelo = $modelo->intersect(base_delegation::where('state', false)->get());
            }
        }
        
        if ( $request->has('paginate')) {
            return $modelo->toQuery()->paginate($request->paginate);
        }
        else {
            return response([
                'data' => BaseDelegationResource::collection($modelo),
                'message' => 'Consulta satisfactoria.'
            ]);
        }
        
    }

    // Returns a base_delegation
    public function v1_show(base_delegation $base_delegation){
        return response([
            'data' => new BaseDelegationResource($base_delegation->makeHidden('sub')),
            'message' => 'Consulta satisfactoria.'
        ]);
    }

    // Create a base_delegation
    public function v1_create(BaseDelegationStoreRequest_v1 $request)
    {
        // Validate when sending the father_id field that exists in the table
        if (!base_delegation::find($request->father)) {
            return response(
                [
                    'message' => 'The parent base_delegation does not exist.'
                ], 400);
        }
        
        $base_delegation = base_delegation::create([
            "name" => $request->name,
            "description" => $request->description,
            "father" => $request->father,
            "state" => $request->has("state")? $request->state: true
        ]);

        //////////////////AUDITORIA/////////////////////////////////////////
        AuditoriaController::auditoria_pro($request, 'Crear delegación de base');

        return response(
            [
                // 'data' => $base_delegation,
                'message' => 'Successful creation.'
            ], 201);
    }

    // Update a base_delegation
    public function v1_update(BaseDelegationUpdateRequest_v1 $request, $base_delegation)
    {
        $base_delegation = base_delegation::find($base_delegation);

        // Validate when sending the father_id field that exists in the table
        if ($request->has('father') && !base_delegation::find($request->father)) {
            return response(
                [
                    'message' => 'The parent structure does not exist.'
                ], 400);
        }

        $base_delegation->update([
            "name" => ($request->has("name"))? $request->name: $base_delegation->name,
            "public" => ($request->has("public"))? $request->public: $base_delegation->public,
            "code" => ($request->has("code"))? $request->code: $base_delegation->code,
            "description" => ($request->has("description"))? $request->description: $base_delegation->description,
            "father" => ($request->has("father"))? $request->father: $base_delegation->father,
            "state" => ($request->has("state"))? $request->state: $base_delegation->state
        ]);

        //////////////////AUDITORIA/////////////////////////////////////////
        AuditoriaController::auditoria_pro($request, 'Actualizar delegación de base');

        return response(
            [
                // 'data' => $base_delegation,
                'message' => 'Update successful.'
            ], 200);
    }

    // Delete a base_delegation
    public function v1_delete(Request $request, $base_delegation){

        $this->borrarArbol($base_delegation, $nodo_error_group);

        // if ($nodo_error_group) {
        //     return response([
        //         'message' => 'This base_delegation '.$nodo_error_group->name.' has a relationship with groups.'
        //     ], 400);
        // }
        
        //////////////////AUDITORIA/////////////////////////////////////////
        AuditoriaController::auditoria_pro($request, 'Eliminar delegación de base');

        return response([
            'message' => 'Successful delete.'
        ]);
    }

    public function borrarArbol($id, &$nodo_error_group=null){
        $nodo = base_delegation::find($id);
        // if ($nodo->groups->first()) {
        //     $nodo_error_group = $nodo;
        //     return;
        // }

        // if ($nodo->phones->first()) {
        //     foreach ($nodo->phones as $phone) {
        //         $phone->delete();
        //     }
        // }
        $nodo->delete();

        $hijos = base_delegation::where('father', $id)->get();
        if ($hijos->first()) {
            foreach ($hijos as $key => $hijo) {
                $this->borrarArbol($hijo->id);
            } 
        }
    }
}


