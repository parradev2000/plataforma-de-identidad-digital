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
use App\Http\Resources\ResearchCategoryResource;
use App\Http\Resources\RolResource;
use App\Http\Resources\SchoolLevelResource;
use App\Models\research_category;
use App\Models\rol;
use App\Models\school_level;
use Illuminate\Http\Request;

use G4T\Swagger\Attributes\SwaggerSection;
use Illuminate\Support\Facades\Http;

#[SwaggerSection('Routes of the research categories')]
class ResearchCategoryController extends Controller
{
    ////////////////// Version 1.0 ////////////////////////
        
        // List the research categories
        public function v1_list(ResearchCategoryListRequest_v1 $request)
        {
            $modelo = research_category::orderByDesc('id')->get();
            if ( $request->has('name')) {
                $modelo = $modelo->intersect(research_category::whereRaw('LOWER(name) LIKE ?',['%'.strtolower($request->name).'%'])->get());
            }elseif ($request->has('state')) {
                ($request->state == 'active')? $modelo = $modelo->intersect(research_category::where('state', true)->get()): $modelo = $modelo->intersect(research_category::where('state', false)->get());
            }
            
            $cant = $modelo->count();

            if ( $request->has('paginate')) {
                return $modelo->toQuery()->paginate($request->paginate);
            }
            else {
                return response(
                    [
                        'data' => ResearchCategoryResource::collection($modelo),
                        'total' => $cant,
                        'message' => 'Completed process.'
                    ], 200);
            }
        }

        // Show a research category
        public function v1_show(research_category $research_category_id)
        {
                return response(
                    [
                        'data' => new ResearchCategoryResource($research_category_id),
                        'message' => 'Consulta satisfactoria.'
                    ], 200);
        }

        // Create a research category
        public function v1_create(ResearchCategoryStoreRequest_v1 $request)
        {
            $research_category = research_category::create([
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
                        'data' => new ResearchCategoryResource($research_category),
                        'message' => 'Completed process.'
                    ], 200);
        }

        // Update a research category
        public function v1_update(ResearchCategoryUpdateRequest_v1 $request, $research_category_id)
        {
            $research_category = research_category::find($research_category_id);
            $research_category->update([
                "name" => ($request->has("name"))? $request->name: $research_category->name,
                "state" => ($request->has("state"))? $request->state:  $research_category->state,
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
                    'data' => new ResearchCategoryResource($research_category),
                    'message' => 'Completed process.'
                ], 200);
        }

        // Delete a research category
        public function v1_delete(Request $request, research_category $research_category_id)
        {

            // if ($rol->users()->exists()) {
            //     return response(
            //         [
            //             'data' => [],
            //             'message' => 'El rol '.$rol->rol.' tiene usuarios relacionados.'
            //         ], 400);
            // }

            $research_category_id->delete();

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
                    'data' => new ResearchCategoryResource($research_category_id),
                    'message' => 'Completed process.'
                ], 200);
        }
}
