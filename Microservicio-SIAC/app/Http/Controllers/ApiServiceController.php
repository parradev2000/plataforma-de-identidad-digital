<?php

namespace App\Http\Controllers;

use App\Events\RolEvents;
use App\Helpers\Helpers;

use Illuminate\Http\Request;
use App\Http\Requests\ApiRequest;

use G4T\Swagger\Attributes\SwaggerSection;
use Illuminate\Support\Facades\Http;

use Illuminate\Support\Facades\DB;

#[SwaggerSection('Datos de Cuadros')]
class ApiServiceController extends Controller
{

   public function getDatos(ApiRequest $request)
   {
      $users = DB::table('oficiales')
      ->select(
         'expediente',
         'grado',
         DB::raw('unidad as um'),
         'ano_ingreso_far',
         'descripcion',
         'f_desde',
         'f_hasta',
         'estado'
      )
      ->join('cargos', 'oficiales.id', '=', 'cargos.idpersona')
      ->where('ci', $request->ci)
      ->get();

      return response()->json([
         'data' => $users
      ], 200);
   }

}
