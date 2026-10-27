<?php

namespace App\Http\Controllers;

use App\Events\RolEvents;
use App\Helpers\Helpers;

use Illuminate\Http\Request;
use App\Http\Requests\ApiRequest;

use G4T\Swagger\Attributes\SwaggerSection;
use Illuminate\Support\Facades\Http;

use DB;

#[SwaggerSection('Datos de Activo-Far')]
class ApiServiceController extends Controller
{

   public function getDatos(ApiRequest $request)
   {
      $users = DB::table('mod_persona.persona')->where('ci', $request->ci)->get();

      return response()->json($users, 200);
   }

}
