<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApiRequest;

use G4T\Swagger\Attributes\SwaggerSection;
use Illuminate\Support\Facades\Http;

use DB;

#[SwaggerSection('Datos de SUIN')]
class ApiServiceController extends Controller
{

   public function getDatos(ApiRequest $request)
   {
      $users = DB::table('mod_persona.persona')->where('ci', $request->ci)->get();

      $resp = "Proceso Terminado";

      return response()->json([
         'data' => $users
         
      ], 200);
         }
}
