<?php

namespace App\Http\Controllers;

use App\Events\AuditoriaEvents;
use App\Helpers\Helpers;
use App\Http\Requests\AuditoriaDeleteRequest_v1;
use App\Http\Requests\AuditoriaListRequest_v1;
use App\Http\Resources\AuditoriaResource;
use App\Models\auditoria;
use Illuminate\Support\Str;
use Carbon\Carbon;

use G4T\Swagger\Attributes\SwaggerSection;
use Illuminate\Support\Facades\Http;

#[SwaggerSection('Rutas de la auditoria')]
class AuditoriaController extends Controller
{
    //Registrar las auditorias de los diferentes metodos de autenticación
    public static function auditoria_pro($request, $accion)
    {
        if ($request->header('Authorization')) {
            $user = Helpers::user_log_all($request->header('Authorization'));
            if ($user) {
                $nombres = '';
                if (isset($user->nombres) && $user->nombres) {
                    $nombres = $user->nombres;
                } elseif (method_exists($user, 'persona') && $user->persona) {
                    $nombres = trim(($user->persona->nombres ?? '') . ' ' . ($user->persona->apellidos ?? ''));
                }
                AuditoriaController::auditoria($accion, $user->usuario, $nombres, $request->ip());
            }
        } elseif ($request->header('X-Authorization')) {
            $xAuth = $request->header('X-Authorization');
            $parts = explode('.', $xAuth);
            if (count($parts) === 3) {
                list($base64Header, $base64Payload, $base64Signature) = $parts;
                $payload = json_decode(base64_decode($base64Payload));
                if ($payload) {
                    AuditoriaController::auditoria($accion, $payload->username ?? 'unknown', $payload->preferred_username ?? 'unknown', $request->ip());
                }
            }
        }
    }

    //Registrar las acciones del usuario en la aplicacion
    public static function auditoria($accion, $usuario, $nombres, $ip)
    {
        $auditoria = new auditoria();
        $auditoria->accion = $accion;
        $auditoria->usuario = $usuario;
        $auditoria->nombres = $nombres;
        $auditoria->ip = $ip;
        $auditoria->save();

        ///////////////// Evento ///////////////////////////////////
        try {
            // Conexión con el Servidor de Websocket
            Http::get(env('PUSHER_HOST').':'.env('PUSHER_PORT').'/app/'.env('PUSHER_APP_KEY'));
            event(new AuditoriaEvents(AuditoriaResource::collection(auditoria::orderByDesc('id')->get())));
        } catch (\Throwable $th) {
        }

    }

    /**
    * Listar auditorias
    * 
    * Devuelve el listado de las trazas de las acciones realizadas por los usuarios en el sistema
    */
    public function list(AuditoriaListRequest_v1 $request)
    {
        $query = auditoria::query()->orderByDesc('id');

        if ($request->has('id')) {
            $query->where('id', $request->id);
        } else {
            if ($request->has('fecha')) {
                $anio = date('Y', strtotime($request->fecha));
                $mes = date('m', strtotime($request->fecha));
                $dia = date('d', strtotime($request->fecha));

                $query->whereYear('created_at', '=', $anio)
                    ->whereMonth('created_at', '=', $mes)
                    ->whereDay('created_at', '=', $dia);
            }

            if ($request->has('usuario')) {
                $query->where('usuario', 'LIKE', '%' . Str::lower($request->usuario) . '%');
            }

            if ($request->has('nombres')) {
                $query->whereRaw('LOWER(nombres) LIKE ?', ['%' . strtolower($request->nombres) . '%']);
            }

            if ($request->has('accion')) {
                $query->whereRaw('LOWER(accion) LIKE ?', ['%' . strtolower($request->accion) . '%']);
            }

            if ($request->has('fecha_ini')) {
                $inicio = strtotime($request->fecha_ini);
                $fin = strtotime($request->fecha_fin);

                $query->whereDate('created_at', '>=', carbon::parse($inicio))
                    ->whereDate('created_at', '<=', carbon::parse($fin));
            }
        }

        $cant = $query->count();

        if ($request->pdf === "active") {
            return ReporteController::export_auditoria($query->get());
        }

        if ($request->has('paginate')) {
            $result = $query->paginate($request->paginate);
            return AuditoriaResource::collection($result);
        }
        else {
            return response(
                [
                    'data' => AuditoriaResource::collection($query->get()),
                    'total' => $cant,
                    'message' => 'Consulta satisfactoria.'
                ], 200);
        }
    }

    /**
    * Eliminar auditorias
    * 
    * Elimina las trazas de las acciones realizadas por los usuarios en el sistema
    */
    // public function delete(AuditoriaDeleteRequest_v1 $request){
    //     $modelo = auditoria::orderByDesc('id')->get();
    //     if ($request->has('fecha')) {
    //         $anio = date('Y', strtotime($request->fecha));
    //         $mes = date('m', strtotime($request->fecha));
    //         $dia = date('d', strtotime($request->fecha));

    //         $modelo = $modelo->intersect(auditoria::whereYear('created_at', '=', $anio)->whereMonth('created_at', '=', $mes)->whereDay('created_at', '=', $dia)->get());
    //     }elseif ($request->has('user_id')) {
    //         $modelo = $modelo->intersect(auditoria::where('user_id', $request->user_id)->get());
    //     }elseif ($request->has('accion')) {
    //         $modelo = $modelo->intersect(auditoria::where('accion','LIKE','%'.Str::lower($request->accion).'%')->get());
    //     }elseif ($request->has('fecha_ini')) {
    //         $inicio = strtotime($request->fecha_ini);
    //         $fin = strtotime($request->fecha_fin);

    //         $modelo = $modelo->intersect(auditoria::whereDate('created_at', '>=', carbon::parse($inicio))
    //                                     ->whereDate('created_at', '<=', carbon::parse($fin))
    //                                     ->get());
    //     }

    //     if ($request->has('id')) {
    //         $modelo = auditoria::where('id', $request->id)->get();
    //     }

    //     if ($request->all === 'true') {
    //         if ($request->reset === 'true') {
    //             $modelo->toQuery()->truncate();
    //         } else {
    //             $modelo->toQuery()->delete();
    //         }
    //     } else {
    //         $modelo->toQuery()->delete();
    //     }

    //     $cant = $modelo->count();
    //     //////////////////AUDITORIA/////////////////////////////////////////
    //     AuditoriaController::auditoria_pro($request, 'Eliminar auditoria');

    //     return response(
    //         [
    //             'data' => AuditoriaResource::collection($modelo),
    //             'total' => $cant,
    //             'message' => 'Eliminacion satisfactoria.'
    //         ], 200);
    // }
}

