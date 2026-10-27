<?php

namespace App\Http\Controllers;

use App\Events\RolEvents;
use App\Helpers\Helpers;

use App\Http\Requests\AcademicLevelStoreRequest_v1;
use App\Http\Requests\AcademicLevelUpdateRequest_v1;
use App\Http\Requests\AcademicLevelRequest;
use App\Http\Resources\AcademicLevelResource;

use App\Models\AcademicLevel;
use Illuminate\Http\Request;

use G4T\Swagger\Attributes\SwaggerSection;
use Illuminate\Support\Facades\Http;

#[SwaggerSection('Academic Level Nomenclator Pathways')]
class AcademicLevelController extends Controller
{
    ////////////////// Version 1.0 ////////////////////////
        
        //List data
        public function v1_list(Request $request)
        {   
            $modelo = AcademicLevel::orderByDesc('id')->get();
            if ( $request->has('category')) {
                $modelo = $modelo->intersect(AcademicLevel::whereRaw('LOWER(description) LIKE ?',['%'.strtolower($request->category).'%'])->get());
            }
            
            $cant = $modelo->count();

            if ( $request->has('paginate')) {
                return $modelo->toQuery()->paginate($request->paginate);
            }
            else {
                return response(
                    [
                        'data' => AcademicLevelResource::collection($modelo),
                        'total' => $cant,
                        'message' => 'Process completed.'
                    ], 200);
            }
        }

        //Display an element by its identifier
        public function v1_show(AcademicLevel $academic)
        {
                return response(
                    [
                        'data' => new AcademicLevelResource($academic),
                        'message' => 'Process completed.'
                    ], 200);
        }

        //Create academic level
        public function v1_create(AcademicLevelStoreRequest_v1 $request)
        {
            $academic = AcademicLevel::create([
                "description" => $request->description,
                "state" => true
            ]);

            //////////////////AUDIT/////////////////////////////////////////
            AuditoriaController::auditoria_pro($request, 'Create academic level');

           
                return response(
                    [
                        'data' => new AcademicLevelResource($academic),
                        'message' => 'Process completed.'
                    ], 200);
        }
       

        //Actulalizar un rol
        public function v1_update(AcademicLevelUpdateRequest_v1 $request, $IdAcademic)
        {
            //return 'hola';
            $ObjAcademic = AcademicLevel::find($IdAcademic);            
            $ObjAcademic->update([
                "description" => ($request->has("description"))? $request->description: $ObjAcademic->description,
                "state" => ($request->has("state"))? $request->state:  $ObjAcademic->state,
            ]);

            //////////////////AUDITORIA/////////////////////////////////////////
            AuditoriaController::auditoria_pro($request, 'Update academic level');

            return response(
                [
                    'message' => 'Process completed.'
                ], 200);
        }        

        //Eliminar un rol
        public function v1_delete(Request $request, AcademicLevel $IdAcademic)
        {            
            $IdAcademic->delete();

            //////////////////AUDITORIA/////////////////////////////////////////
            AuditoriaController::auditoria_pro($request, 'Delete academic level');

            return response(
                [
                    'message' => 'Process completed.'
                ], 200);
        }
        
}
