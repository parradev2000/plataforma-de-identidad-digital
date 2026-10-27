<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use G4T\Swagger\Attributes\SwaggerSection;
use App\Http\Resources\SexResource;
use App\Http\Requests\SexListRequest_v1;
use App\Models\sex;
use Illuminate\Support\Facades\Http;
use App\Events\SexEvents;
use App\Http\Requests\SexCreateRequest_v1;
use App\Http\Requests\SexUpdateRequest_v1;
use Illuminate\Http\Request;

#[SwaggerSection('Route of sex nomenclator')]
class SexController extends Controller{

    //List sex
    public function list_sex(SexListRequest_v1 $request)
    {
        $model = sex::orderByDesc('name')->get();

        if ( $request->has('name')) {
            $model = $model->intersect(sex::whereRaw('LOWER(name) LIKE ?',['%'.strtolower($request->name).'%'])->get());
        }elseif ( $request->has('id')) {
            $model = $model->intersect(sex::whereRaw('LOWER(id) LIKE ?',['%'.strtolower($request->id).'%'])->get());
        }elseif ( $request->has('abbreviation')) {
            $model = $model->intersect(sex::whereRaw('LOWER(abbreviation) LIKE ?',['%'.strtolower($request->abbreviation).'%'])->get());
        }
        
        $cant = $model->count();

        if ( $request->has('paginate')) {
            return $model->toQuery()->paginate($request->paginate);
        }
        else {
            return response(
                [ 
                'data' => SexResource::collection($model),
                'total' => $cant,
                'message' => 'Process complete.'
            ], 200);
        }    

    }

    //Get information about a sex
    public function show_sex(sex $idsex)
    {
        return response(
            [
                'data' => new SexResource($idsex),
                'message' => 'Process completed.'
            ], 200);
    }

    //Create a new sex
    public function create_sex(SexCreateRequest_v1 $request)
        {
            //return $request;
            $sex = sex::create([
                "name" => $request->name,
                "abbreviation" => $request->abbreviation,
                "description" => $request->description
            ]);

            
            //////////////////AUDITORIA/////////////////////////////////////////
            AuditoriaController::auditoria_pro($request, 'Crear sexo');

            ///////////////// Evento ///////////////////////////////////
            try {
                // Conexión con el Servidor de Websocket
                Http::get(env('PUSHER_HOST').':'.env('PUSHER_PORT').'/app/'.env('PUSHER_APP_KEY'));
                event(new SexEvents(SexResource::collection(sex::orderByDesc('id')->get())));
                } catch (\Throwable $th) {
            }            
                return response(
                    [
                        'data' => new SexResource($sex),
                        'message' => 'Creation completed.'
                    ], 200);
        }


    //Update a sex
    public function update_sex(SexUpdateRequest_v1 $request, $idsex)
    {
        $sex = sex::find($idsex);
        $sex->update([
            "name" => ($request->has("name"))? $request->name: $sex->name,
            "abbreviation" => ($request->has("abbreviation"))? $request->abbreviation: $sex->abbreviation,
            "description" => ($request->has("description"))? $request->description: $sex->description,
            "state" => ($request->has("state"))? $request->state: true
        ]);

        //////////////////AUDITORIA/////////////////////////////////////////
        AuditoriaController::auditoria_pro($request, 'Actualizar sexo');

        ///////////////// Evento ///////////////////////////////////
        try {
            // Conexión con el Servidor de Websocket
            Http::get(env('PUSHER_HOST').':'.env('PUSHER_PORT').'/app/'.env('PUSHER_APP_KEY'));
            event(new SexEvents(SexResource::collection(sex::orderByDesc('id')->get())));
        } catch (\Throwable $th) {
        }

        return response(
            [
                'data' => new SexResource($sex),
                'message' => 'Update completed.'
            ], 200);
    }

    //Delete a Sex
    public function delete_sex(Request $request, sex $idsex)
    {

        $idsex->delete();

        //////////////////AUDITORIA/////////////////////////////////////////
        AuditoriaController::auditoria_pro($request, 'Eliminar sexo');

        ///////////////// Evento ///////////////////////////////////
        try {
            // Conexión con el Servidor de Websocket
            Http::get(env('PUSHER_HOST').':'.env('PUSHER_PORT').'/app/'.env('PUSHER_APP_KEY'));
            event(new SexEvents(SexResource::collection(sex::orderByDesc('id')->get())));
        } catch (\Throwable $th) {
        }

        return response(
            [
                'data' => new SexResource($idsex),
                'message' => 'Removal completed.'
            ], 200);
    }


}











?>