<?php

namespace App\Http\Controllers;

use App\Http\Resources\SolicitudResource;
use App\Models\Solicitud;
use App\Http\Requests\SolicitudStoreRequest_v1;
use App\Http\Requests\SolicitudUpdateRequest_v1;
use App\Http\Requests\SolicitudListRequest_v1;
use Illuminate\Http\Request;
use G4T\Swagger\Attributes\SwaggerSection;

#[SwaggerSection('Rutas de las solicitudes')]
class SolicitudController extends Controller
{
    ////////////////// Version 1.0 ////////////////////////

    /**
    * Listar solicitudes
    *
    * Devuelve el listado de las solicitudes
    */
    public function list(SolicitudListRequest_v1 $request)
    {
        $with = ['estado', 'servicio'];
        $personasRel = $request->input('persona_rel') ?? $request->input('personas_rel');
        $showPersona = is_string($personasRel) ? in_array(strtolower($personasRel), ['true','1','on','yes']) : filter_var($personasRel, FILTER_VALIDATE_BOOLEAN);
        if ($showPersona || $request->has('persona_ci') || $request->has('ci') || $request->has('persona_nombres') || $request->has('persona_apellidos')) {
            $with[] = 'persona';
        }
        $query = Solicitud::with($with)->orderByDesc('id');

        if ($request->has('persona_id')) {
            $query->where('persona_id', $request->persona_id);
        }

        if ($request->has('persona_ci') || $request->has('ci')) {
            $ci = $request->persona_ci ?? $request->ci;
            $query->whereHas('persona', function ($q) use ($ci) {
                $q->where('ci', 'LIKE', '%' . $ci . '%');
            });
        }

        if ($request->has('persona_nombres')) {
            $query->whereHas('persona', function ($q) use ($request) {
                $q->whereRaw('LOWER(nombres) LIKE ?', ['%' . strtolower($request->persona_nombres) . '%']);
            });
        }

        if ($request->has('persona_apellidos')) {
            $query->whereHas('persona', function ($q) use ($request) {
                $q->whereRaw('LOWER(apellidos) LIKE ?', ['%' . strtolower($request->persona_apellidos) . '%']);
            });
        }

        if ($request->has('estado_id')) {
            $query->where('estado_id', $request->estado_id);
        }

        if ($request->has('servicio_id')) {
            $query->where('servicio_id', $request->servicio_id);
        }

        $cant = $query->count();

        if ($request->has('paginate') && $request->paginate > 0) {
            $result = $query->paginate($request->paginate);
            return SolicitudResource::collection($result);
        }

        return response([
            'data' => SolicitudResource::collection($query->get()),
            'total' => $cant,
            'message' => 'Consulta satisfactoria.',
        ], 200);
    }

    /**
    * Listar una solicitud
    * Devuelve una solicitud por el ID
    */
    public function show(Solicitud $solicitud)
    {
        $solicitud->load(['persona', 'estado', 'servicio']);
        return response([
            'data' => new SolicitudResource($solicitud),
            'message' => 'Consulta satisfactoria.',
        ], 200);
    }

    /**
    * Crear una solicitud
    *
    * Crea una nueva solicitud
    */
    public function create(SolicitudStoreRequest_v1 $request)
    {
        $solicitud = Solicitud::create([
            'persona_id' => $request->persona_id,
            'servicio_id' => $request->servicio_id,
            'estado_id' => $request->estado_id,
        ]);

        $solicitud->load(['persona', 'estado', 'servicio']);

        return response([
            'data' => new SolicitudResource($solicitud),
            'message' => 'Creacion satisfactoria.',
        ], 200);
    }

    /**
    * Actualizar una solicitud
    *
    * Actualiza una solicitud
    */
    public function update(SolicitudUpdateRequest_v1 $request, Solicitud $solicitud)
    {
        $data = [];
        if ($request->has('persona_id')) {
            $data['persona_id'] = $request->persona_id;
        }
        if ($request->has('servicio_id')) {
            $data['servicio_id'] = $request->servicio_id;
        }
        if ($request->has('estado_id')) {
            $data['estado_id'] = $request->estado_id;
        }

        $solicitud->update($data);
        $solicitud->load(['persona', 'estado', 'servicio']);

        return response([
            'data' => new SolicitudResource($solicitud),
            'message' => 'Actualizacion satisfactoria.',
        ], 200);
    }

    /**
    * Eliminar una solicitud
    *
    * Elimina una solicitud
    */
    public function delete(Request $request, Solicitud $solicitud)
    {
        $solicitud->delete();

        return response([
            'data' => new SolicitudResource($solicitud),
            'message' => 'Eliminacion satisfactoria.',
        ], 200);
    }
}