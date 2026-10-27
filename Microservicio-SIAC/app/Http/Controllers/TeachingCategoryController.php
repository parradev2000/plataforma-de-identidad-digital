<?php

namespace App\Http\Controllers;

use App\Events\RolEvents;
use App\Helpers\Helpers;
use App\Http\Requests\ResearchCategoryListRequest_v1;
use App\Http\Requests\ResearchCategoryStoreRequest_v1;
use App\Http\Requests\ResearchCategoryUpdateRequest_v1;
use App\Http\Requests\RolDeleteRequest_v1;
use App\Http\Requests\RolListRequest_v1;
use App\Http\Requests\RolStoreMasivoRequest_v1;
use App\Http\Requests\RolStoreRequest_v1;
use App\Http\Requests\RolUpdateMasivoRequest_v1;
use App\Http\Requests\RolUpdateRequest_v1;
use App\Http\Requests\SchoolLevelListRequest_v1;
use App\Http\Requests\SchoolLevelUpdateRequest_v1;
use App\Http\Requests\SchoolLeveStoreRequest_v1;
use App\Http\Requests\TeachingCategoryListRequest_v1;
use App\Http\Requests\TeachingCategoryStoreRequest_v1;
use App\Http\Requests\TeachingCategoryUpdateRequest_v1;
use App\Http\Resources\ResearchCategoryResource;
use App\Http\Resources\RolResource;
use App\Http\Resources\SchoolLevelResource;
use App\Http\Resources\TeachingCategoryResource;
use App\Models\research_category;
use App\Models\rol;
use App\Models\school_level;
use App\Models\teaching_category;
use Illuminate\Http\Request;

use G4T\Swagger\Attributes\SwaggerSection;
use Illuminate\Support\Facades\Http;

#[SwaggerSection('Routes of the teaching categories')]
class TeachingCategoryController extends Controller
{
    ////////////////// Version 1.0 ////////////////////////
        
        // List the teaching categories
        public function v1_list(TeachingCategoryListRequest_v1 $request)
        {
            $modelo = teaching_category::orderByDesc('id')->get();
            if ( $request->has('name')) {
                $modelo = $modelo->intersect(teaching_category::whereRaw('LOWER(name) LIKE ?',['%'.strtolower($request->name).'%'])->get());
            }elseif ($request->has('state')) {
                ($request->state == 'active')? $modelo = $modelo->intersect(teaching_category::where('state', true)->get()): $modelo = $modelo->intersect(teaching_category::where('state', false)->get());
            }
            
            $cant = $modelo->count();

            if ( $request->has('paginate')) {
                return $modelo->toQuery()->paginate($request->paginate);
            }
            else {
                return response(
                    [
                        'data' => TeachingCategoryResource::collection($modelo),
                        'total' => $cant,
                        'message' => 'Completed process.'
                    ], 200);
            }
        }

        // Show a teaching category
        public function v1_show(teaching_category $teaching_category_id)
        {
                return response(
                    [
                        'data' => new TeachingCategoryResource($teaching_category_id),
                        'message' => 'Completed process.'
                    ], 200);
        }

        // Create a teaching category
        public function v1_create(TeachingCategoryStoreRequest_v1 $request)
        {
            $teaching_category = teaching_category::create([
                "name" => $request->name,
            ]);

            //////////////////AUDITORIA/////////////////////////////////////////
            AuditoriaController::auditoria_pro($request, 'Crear categoria investigativa');

            ///////////////// Evento ///////////////////////////////////
            // try {
            //     // Conexión con el Servidor de Websocket
            //     Http::get(env('PUSHER_HOST').':'.env('PUSHER_PORT').'/app/'.env('PUSHER_APP_KEY'));
            //     event(new RolEvents(RolResource::collection(rol::orderByDesc('id')->get())));
            // } catch (\Throwable $th) {
            // }
            
                return response(
                    [
                        'data' => new ResearchCategoryResource($teaching_category),
                        'message' => 'Completed process.'
                    ], 200);
        }

        // Update a teaching category
        public function v1_update(TeachingCategoryUpdateRequest_v1 $request, $teaching_category_id)
        {
            $teaching_category = teaching_category::find($teaching_category_id);
            $teaching_category->update([
                "name" => ($request->has("name"))? $request->name: $teaching_category->name,
                "state" => ($request->has("state"))? $request->state:  $teaching_category->state,
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
                    'data' => new ResearchCategoryResource($teaching_category),
                    'message' => 'Completed process.'
                ], 200);
        }

        // Delete a teaching category
        public function v1_delete(Request $request, teaching_category $teaching_category_id)
        {

            // if ($rol->users()->exists()) {
            //     return response(
            //         [
            //             'data' => [],
            //             'message' => 'El rol '.$rol->rol.' tiene usuarios relacionados.'
            //         ], 400);
            // }

            $teaching_category_id->delete();

            //////////////////AUDITORIA/////////////////////////////////////////
            AuditoriaController::auditoria_pro($request, 'Eliminar categoria investigativa');

            ///////////////// Evento ///////////////////////////////////
            // try {
            //     // Conexión con el Servidor de Websocket
            //     Http::get(env('PUSHER_HOST').':'.env('PUSHER_PORT').'/app/'.env('PUSHER_APP_KEY'));
            //     event(new RolEvents(RolResource::collection(rol::orderByDesc('id')->get())));
            // } catch (\Throwable $th) {
            // }

            return response(
                [
                    'data' => new TeachingCategoryResource($teaching_category_id),
                    'message' => 'Completed process.'
                ], 200);
        }
}
