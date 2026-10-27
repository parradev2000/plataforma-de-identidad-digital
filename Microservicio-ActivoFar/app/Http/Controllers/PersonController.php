<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssigPersonBaseDelegationStoreRequest_v1;
use App\Http\Requests\PersonListRequest_v1;
use App\Http\Requests\PersonStoreRequest_v1;
use App\Http\Requests\PersonUpdateRequest_v1;
use App\Http\Resources\PersonResource;
use App\Models\base_delegation_charges_anec_person;
use App\Models\person;
use App\Models\personal_profile;
use App\Models\professional_profile;
use App\Models\social_network;
use App\Models\social_network_person;
use App\Models\work_profile;
use Illuminate\Http\Request;

use G4T\Swagger\Attributes\SwaggerSection;

#[SwaggerSection('Persons Nomenclator Pathways')]
class PersonController extends Controller
{
    ////////////////// Version 1.0 ////////////////////////
        
        //List data
        public function v1_list(PersonListRequest_v1 $request)
        {
            $modelo = person::orderByDesc('id')->get();
            if ( $request->has('names')) {
                $modelo = $modelo->intersect(person::whereRaw('LOWER(names) LIKE ?',['%'.strtolower($request->names).'%'])->get());
            }elseif ( $request->has('identity')) {
                $modelo = $modelo->intersect(person::whereRaw('LOWER(identity) LIKE ?',['%'.strtolower($request->identity).'%'])->get());
            }elseif ( $request->has('state')) {
                $modelo = $modelo->intersect(person::where('state',$request->state)->get());
            }elseif ( $request->has('personal_profile_id')) {
                $modelo = $modelo->intersect(person::where('personal_profile_id',$request->personal_profile_id)->get());
            }elseif ( $request->has('professional_profile_id')) {
                $modelo = $modelo->intersect(person::where('professional_profile_id',$request->professional_profile_id)->get());
            }elseif ( $request->has('work_profile_id')) {
                $modelo = $modelo->intersect(person::where('work_profile_id',$request->work_profile_id)->get());
            }
            
            $cant = $modelo->count();

            if ( $request->has('paginate')) {
                return $modelo->toQuery()->paginate($request->paginate);
            }
            else {
                return response(
                    [
                        'data' => PersonResource::collection($modelo),
                        'total' => $cant,
                        'message' => 'Process completed.'
                    ], 200);
            }
        }

        //Display an element by its identifier
        public function v1_show(person $person)
        {
                return response(
                    [
                        'data' => new PersonResource($person),
                        'message' => 'Process completed.'
                    ], 200);
        }

        //Create person
        public function v1_create(PersonStoreRequest_v1 $request)
        {
            try {
                $personal_profile = personal_profile::create([
                    "phone" => $request->personal_phone,
                    "mail" => $request->private_mail,
                    "address" => $request->address,
                    "militancy_id" => $request->militancy_id,
                    "municipality_id" => $request->municipality_id,
                ]);
    
                $professional_profile = professional_profile::create([
                    "professional_ability" => $request->professional_ability,
                    "specialty" => $request->specialty,
                    "scientific_postgraduate_specialty" => $request->scientific_postgraduate_specialty,
                    "academic_postgraduate_specialty" => $request->academic_postgraduate_specialty,
                    "school_level_id" => $request->school_level_id,
                    "academic_level_id" => $request->academic_level_id,
                    "teaching_category_id" => $request->teaching_category_id,
                    "research_category_id" => $request->research_category_id,
                    "scientific_grade_id" => $request->scientific_grade_id,
                ]);
                
                $work_profile = work_profile::create([
                    "work_center" => $request->work_center,
                    "phone" => $request->work_phone,
                    "mail" => $request->work_mail,
                    "charge_id" => $request->charge_id,
                    "year_id" => $request->year_entry_anec,
                    "entity_id" => $request->entity_id,
                    "business_system_id" => $request->business_system_id,
                ]);
    
                $person = person::create([
                    "names" => $request->names,
                    "identity" => $request->identity,
                    "personal_profile_id" => $personal_profile->id,
                    "professional_profile_id" => $professional_profile->id,
                    "work_profile_id" => $work_profile->id,
                ]);

                // Agregar redes sociales
                foreach ($request->social_networks as $social_network) {
                    
                    social_network_person::create([
                        "social_network_id" => $social_network['id'],
                        "person_id" => $person->id,
                        "user" => $social_network['user'],
                    ]);
                }
            } catch (\Throwable $th) {
                $personal_profile? $personal_profile->delete(): true;
                $professional_profile? $professional_profile->delete(): true;
                $work_profile? $work_profile->delete(): true;
                $person? $person->delete(): true;
                return response(
                    [
                        'message' => 'Error creating models.'
                    ], 400);
            }
            
            //////////////////AUDITORIA/////////////////////////////////////////
            AuditoriaController::auditoria_pro($request, 'Create person');

            return response(
                [
                    'message' => 'Process completed.'
                ], 200);
        }
       
        //Assign person a base delegation
        public function v1_assig_person_base_delegation(AssigPersonBaseDelegationStoreRequest_v1 $request)
        {
            base_delegation_charges_anec_person::create([
                "base_delegation_id" => $request->base_delegation_id,
                "charge_anec_id" => $request->charge_anec_id,
                "person_id" => $request->person_id
            ]);
            
            //////////////////AUDITORIA/////////////////////////////////////////
            AuditoriaController::auditoria_pro($request, 'Asignar persona a una delegación de base');

            return response(
                [
                    'message' => 'Process completed.'
                ], 200);
        }

        //Update person
        public function v1_update(PersonUpdateRequest_v1 $request, $person)
        {
            $person = person::find($person);            

            $person->personal_profile->update([
                "phone" => $request->has('personal_phone')? $request->personal_phone: $person->personal_profile->phone,
                "mail" => $request->has('private_mail')? $request->private_mail: $person->personal_profile->mail,
                "adress" => $request->has('adress')? $request->adress: $person->personal_profile->adress,
                "militancy_id" => $request->has('militancy_id')? $request->militancy_id: $person->professional_profile->militancy_id,
                "municipality_id" => $request->has('municipality_id')? $request->municipality_id: $person->personal_profile->municipality_id,
            ]);

            $person->professional_profile->update([
                "professional_ability" => $request->has('professional_ability')? $request->professional_ability: $person->professional_profile->professional_ability,
                "specialty" => $request->has('specialty')? $request->specialty: $person->professional_profile->specialty,
                "scientific_postgraduate_specialty" => $request->has('scientific_postgraduate_specialty')? $request->scientific_postgraduate_specialty: $person->professional_profile->scientific_postgraduate_specialty,
                "academic_postgraduate_specialty" => $request->has('academic_postgraduate_specialty')? $request->academic_postgraduate_specialty: $person->professional_profile->academic_postgraduate_specialty,
                "school_level_id" => $request->has('school_level_id')? $request->school_level_id: $person->professional_profile->school_level_id,
                "academic_level_id" => $request->has('academic_level_id')? $request->academic_level_id: $person->professional_profile->academic_level_id,
                "teaching_category_id" => $request->has('teaching_category_id')? $request->teaching_category_id: $person->professional_profile->teaching_category_id,
                "research_category_id" => $request->has('research_category_id')? $request->research_category_id: $person->professional_profile->research_category_id,
                "scientific_grade_id" => $request->has('scientific_grade_id')? $request->especiality: $person->professional_profile->scientific_grade_id,
            ]);
            
            $person->work_profile->update([
                "work_center" => $request->has('work_center')? $request->work_center: $person->work_profile->work_center,
                "phone" => $request->has('work_phone')? $request->work_phone: $person->work_profile->phone,
                "mail" => $request->has('work_mail')? $request->work_mail: $person->work_profile->mail,
                "charge_id" => $request->has('charge_id')? $request->charge_id: $person->work_profile->charge_id,
                "year_id" => $request->has('year_entry_anec')? $request->year_entry_anec: $person->work_profile->year_id,
                "entity_id" => $request->has('entity_id')? $request->entity_id: $person->work_profile->entity_id,
                "business_system_id" => $request->has('business_system_id')? $request->business_system_id: $person->work_profile->business_system_id,
            ]);

            $person->update([
                "names" => $request->has('names')? $request->names: $person->names,
                "identity" => $request->has('identity')? $request->identity: $person->identity,
            ]);

            //////////////////AUDITORIA/////////////////////////////////////////
            AuditoriaController::auditoria_pro($request, 'Update person');

            return response(
                [
                    'message' => 'Process completed.'
                ], 200);
        }        

        //Delete person
        public function v1_delete(Request $request, person $person)
        {
            $person->delete();

            //////////////////AUDITORIA/////////////////////////////////////////
            AuditoriaController::auditoria_pro($request, 'Delete person');

            return response(
                [
                    'message' => 'Process completed.'
                ], 200);
        }
        
}
