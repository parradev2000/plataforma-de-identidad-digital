<?php

namespace App\Http\Controllers;
use G4T\Swagger\Attributes\SwaggerSection;
use App\Http\Resources\AgeResource;
use App\Http\Requests\AgeListRequest_v1;
use App\Models\age;
use Illuminate\Support\Facades\Http;
use App\Events\AgeEvents;
use App\Http\Requests\AgeCreateRequest_v1;
use App\Http\Requests\AgeUpdateRequest_v1;
use Illuminate\Http\Request;

#[SwaggerSection('Route of age nomenclator')]
class AgeController extends Controller{

     //List age
     public function list_ages(AgeListRequest_v1 $request)
     {
         $model = age::orderByDesc('age')->get();
 
         if ( $request->has('age')) {
             $model = $model->intersect(age::whereRaw('LOWER(age) LIKE ?',['%'.strtolower($request->name).'%'])->get());
         }
 
         if ( $request->has('id')) {
             $model = $model->intersect(age::whereRaw('LOWER(id) LIKE ?',['%'.strtolower($request->id).'%'])->get());
         }
         $cant = $model->count();
 
         if ( $request->has('paginate')) {
             return $model->toQuery()->paginate($request->paginate);
         }
         else {
             return response(
                 [ 
                 'data' => AgeResource::collection($model),
                 'total' => $cant,
                 'message' => 'Process completed.'
             ], 200);
         }    
 
     }

     //Get information about a age
    public function show_age(age $idage)
    {
        return response(
            [
                'data' => new AgeResource($idage),
                'message' => 'Process completed.'
            ], 200);
    }

    ///Create a new age
    public function create_age(AgeCreateRequest_v1 $request)
        {
            //return $request;
            $age = age::create([
                "age" => $request->age,
                "description" => $request->description
            ]);

            
            //////////////////AUDITORIA/////////////////////////////////////////
            AuditoriaController::auditoria_pro($request, 'Crear edad');

            ///////////////// Evento ///////////////////////////////////
            try {
                // Conexión con el Servidor de Websocket
                Http::get(env('PUSHER_HOST').':'.env('PUSHER_PORT').'/app/'.env('PUSHER_APP_KEY'));
                event(new AgeEvents(ageResource::collection(age::orderByDesc('id')->get())));
                } catch (\Throwable $th) {
            }            
                return response(
                    [
                        'data' => new AgeResource($age),
                        'message' => 'Creation completed.'
                    ], 200);
        }

    //Update a age
    public function update_age(AgeUpdateRequest_v1 $request, $idage)
    {
        $age = age::find($idage);
        $age->update([
            "age" => ($request->has("age"))? $request->age: $age->age,
            "description" => ($request->has("description"))? $request->description: $age->description,
            "state" => ($request->has("state"))? $request->state: true
        ]);

        //////////////////AUDITORIA/////////////////////////////////////////
        AuditoriaController::auditoria_pro($request, 'Actualizar edad');

        ///////////////// Evento ///////////////////////////////////
        try {
            // Conexión con el Servidor de Websocket
            Http::get(env('PUSHER_HOST').':'.env('PUSHER_PORT').'/app/'.env('PUSHER_APP_KEY'));
            event(new AgeEvents(AgeResource::collection(age::orderByDesc('id')->get())));
        } catch (\Throwable $th) {
        }

        return response(
            [
                'data' => new AgeResource($age),
                'message' => 'Update completed.'
            ], 200);
    }


//Delete a Sex
public function delete_age(Request $request, age $idage)
{

    $idage->delete();

    //////////////////AUDITORIA/////////////////////////////////////////
    AuditoriaController::auditoria_pro($request, 'Eliminar edad');

    ///////////////// Evento ///////////////////////////////////
    try {
        // Conexión con el Servidor de Websocket
        Http::get(env('PUSHER_HOST').':'.env('PUSHER_PORT').'/app/'.env('PUSHER_APP_KEY'));
        event(new AgeEvents(AgeResource::collection(age::orderByDesc('id')->get())));
    } catch (\Throwable $th) {
    }

    return response(
        [
            'data' => new AgeResource($idage),
            'message' => 'Removal completed.'
        ], 200);
}

    



}





?>