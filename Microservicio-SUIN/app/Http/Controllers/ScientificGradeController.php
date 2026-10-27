<?php

namespace App\Http\Controllers;

use App\Events\RolEvents;
use App\Helpers\Helpers;

use App\Http\Requests\ScientificGradeStoreRequest_v1;
use App\Http\Requests\ScientificGradeUpdateRequest_v1;
use App\Http\Requests\ScientificGradeRequest;
use App\Http\Resources\ScientificGradeResource;

use App\Models\ScientificGrade;
use Illuminate\Http\Request;

use G4T\Swagger\Attributes\SwaggerSection;
use Illuminate\Support\Facades\Http;

#[SwaggerSection('Scientific Grades Nomenclator Pathways')]
class ScientificGradeController extends Controller
{
    ////////////////// Version 1.0 ////////////////////////
        
        //List Scientific Grades
        public function v1_list(Request $request)
        {   
            $modelo = ScientificGrade::orderByDesc('id')->get();
            if ( $request->has('category')) {
                $modelo = $modelo->intersect(ScientificGrade::whereRaw('LOWER(description) LIKE ?',['%'.strtolower($request->category).'%'])->get());
            }
            
            $cant = $modelo->count();

            if ( $request->has('paginate')) {
                return $modelo->toQuery()->paginate($request->paginate);
            }
            else {
                return response(
                    [
                        'data' => ScientificGradeResource::collection($modelo),
                        'total' => $cant,
                        'message' => 'Process completed.'
                    ], 200);
            }
        }

        //Display an element by its identifier
        public function v1_show(ScientificGrade $scgrade)
        {
                return response(
                    [
                        'data' => new ScientificGradeResource($scgrade),
                        'message' => 'Process completed.'
                    ], 200);
        }

        //Create Scientific Grades
        public function v1_create(ScientificGradeStoreRequest_v1 $request)
        {
            $academic = ScientificGrade::create([
                "description" => $request->description,
                "state" => true
            ]);

            //////////////////AUDIT/////////////////////////////////////////
            AuditoriaController::auditoria_pro($request, 'Create scientific grade');

           
                return response(
                    [
                        'data' => new ScientificGradeResource($academic),
                        'message' => 'Process completed.'
                    ], 200);
        }
       

        //Update a Scientific Grades
        public function v1_update(ScientificGradeUpdateRequest_v1 $request, $IdScientificGrade)
        {
            //return 'hola';
            $ObjScientificGrade = ScientificGrade::find($IdScientificGrade);            
            $ObjScientificGrade->update([
                "description" => ($request->has("description"))? $request->description: $ObjScientificGrade->description,
                "state" => ($request->has("state"))? $request->state:  $ObjScientificGrade->state,
            ]);

            //////////////////AUDITORIA/////////////////////////////////////////
            AuditoriaController::auditoria_pro($request, 'Update scientific grade');

            return response(
                [
                    'message' => 'Process completed.'
                ], 200);
        }        

        //Delete a Scientific Grade
        public function v1_delete(Request $request, ScientificGrade $IdScientificGrade)
        {            
            $IdScientificGrade->delete();

            //////////////////AUDITORIA/////////////////////////////////////////
            AuditoriaController::auditoria_pro($request, 'Delete acadescientific grade');

            return response(
                [
                    'message' => 'Process completed.'
                ], 200);
        }
}
