<?php

namespace App\Http\Controllers;

use App\Http\Requests\QueryListRegRequest_v1;
use App\Models\base_delegation;
use App\Models\person;
use Illuminate\Http\Request;

use G4T\Swagger\Attributes\SwaggerSection;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

#[SwaggerSection('Routes of the Queries')]
class QueryController extends Controller
{
    // List the count 
    public function v1_list_count()
    {
        $cant_person = person::all()->count();
        $cant_base_delegation = base_delegation::all()->except('1')->count();
        return response(
            [
                'cant_person' => $cant_person,
                'cant_base_delegation' => $cant_base_delegation,
                'message' => 'Completed process.'
            ], 200);
    }

    // List the count members for base delegation
    public function v1_cant_member_base_delegation()
    {
        $base_delegation = base_delegation::all()->except('1')->load('persons')->makeHidden('sub');
        return response(
            [
                'data' => $base_delegation,
                'message' => 'Completed process.'
            ], 200);
    }

    // List cant person for day
    public function v1_list_CantRegPersonDiarias(QueryListRegRequest_v1 $request){
        $response = collect();
        $numeroDeDias = Carbon::createFromDate($request->year, $request->month)->daysInMonth;
        for ($dia = 1; $dia <= $numeroDeDias; $dia++) {
            $person = person::whereMonth('created_at', $request->month)
            ->whereYear('created_at', $request->year)
            ->whereDay('created_at', $dia)
            ->get();
            
            $response->push([
                "dia" => $dia,
                "cant" => $person->count()
            ]);
        } 

        return $response;
    }
}
