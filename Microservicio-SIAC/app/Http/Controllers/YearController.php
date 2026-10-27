<?php

namespace App\Http\Controllers;

use App\Http\Requests\YearListRequest_v1;
use App\Http\Requests\YearStoreRequest_v1;
use App\Http\Requests\YearUpdateRequest_v1;
use App\Http\Resources\ProvinceResource;
use App\Http\Resources\YearResource;
use App\Models\province;
use App\Models\year;
use Illuminate\Http\Request;

use G4T\Swagger\Attributes\SwaggerSection;

#[SwaggerSection('Routes of the years')]
class YearController extends Controller
{
    ////////////////// Version 1.0 ////////////////////////
        
        //List the years
        public function v1_list(YearListRequest_v1 $request)
        {
            $modelo = year::orderByDesc('id')->get();
            if ( $request->has('year')) {
                $modelo = $modelo->intersect(year::where('year', $request->year)->get());
            }
            
            $cant = $modelo->count();

            if ( $request->has('paginate')) {
                return $modelo->toQuery()->paginate($request->paginate);
            }
            else {
                return response(
                    [
                        'data' => YearResource::collection($modelo),
                        'total' => $cant,
                        'message' => 'Successful consultation.'
                    ], 200);
            }
        }

        //Show a year
        public function v1_show(year $year)
        {
                return response(
                    [
                        'data' => new ProvinceResource($year),
                        'message' => 'Successful consultation.'
                    ], 200);
        }

        //Create a year
        public function v1_create(YearStoreRequest_v1 $request)
        {
            $year = year::create([
                "year" => $request->year,
            ]);

            //////////////////AUDITORIA/////////////////////////////////////////
            AuditoriaController::auditoria_pro($request, 'Crear un año');

            ///////////////// Evento ///////////////////////////////////
            // try {
            //     // Conexión con el Servidor de Websocket
            //     Http::get(env('PUSHER_HOST').':'.env('PUSHER_PORT').'/app/'.env('PUSHER_APP_KEY'));
            //     event(new RolEvents(RolResource::collection(rol::orderByDesc('id')->get())));
            // } catch (\Throwable $th) {
            // }
            
            return response(
                [
                    // 'data' => new YearResource($year),
                    'message' => 'Successful creation.'
                ], 200);
        }

        //Update a year
        public function v1_update(YearUpdateRequest_v1 $request, $year)
        {
            $year = year::find($year);
            $year->update([
                "year" => ($request->has("year"))? $request->year: $year->year,
            ]);

            //////////////////AUDITORIA/////////////////////////////////////////
            AuditoriaController::auditoria_pro($request, 'Actualizar año');

            ///////////////// Evento ///////////////////////////////////
            // try {
            //     // Conexión con el Servidor de Websocket
            //     Http::get(env('PUSHER_HOST').':'.env('PUSHER_PORT').'/app/'.env('PUSHER_APP_KEY'));
            //     event(new RolEvents(RolResource::collection(rol::orderByDesc('id')->get())));
            // } catch (\Throwable $th) {
            // }

            return response(
                [
                    // 'data' => new YearResource($year),
                    'message' => 'Update successful.'
                ], 200);
        }

        //Delete a year
        public function v1_delete(Request $request, year $year)
        {
            $year->delete();

            //////////////////AUDITORIA/////////////////////////////////////////
            AuditoriaController::auditoria_pro($request, 'Eliminar año');

            ///////////////// Evento ///////////////////////////////////
            // try {
            //     // Conexión con el Servidor de Websocket
            //     Http::get(env('PUSHER_HOST').':'.env('PUSHER_PORT').'/app/'.env('PUSHER_APP_KEY'));
            //     event(new RolEvents(RolResource::collection(rol::orderByDesc('id')->get())));
            // } catch (\Throwable $th) {
            // }

            return response(
                [
                    'data' => new ProvinceResource($year),
                    'message' => 'Successful delete.'
                ], 200);
        }
}
