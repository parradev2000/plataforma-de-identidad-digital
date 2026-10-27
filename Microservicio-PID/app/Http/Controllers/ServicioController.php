<?php

namespace App\Http\Controllers;

use App\Http\Resources\ServicioResource;
use App\Models\Servicio;
use App\Http\Requests\ServicioStoreRequest_v1;
use App\Http\Requests\ServicioUpdateRequest_v1;
use App\Http\Requests\ServicioListRequest_v1;
use Illuminate\Http\Request;
use G4T\Swagger\Attributes\SwaggerSection;

#[SwaggerSection('Rutas de los servicios')]
class ServicioController extends Controller
{
    ////////////////// Version 1.0 ////////////////////////

    /**
    * Listar servicios
    *
    * Devuelve el listado de los servicios
    */
    public function list(ServicioListRequest_v1 $request)
    {
        $query = Servicio::query()->orderBy('nombre');

        if ($request->has('nombre')) {
            $query->whereRaw('LOWER(nombre) LIKE ?', ['%' . strtolower($request->nombre) . '%']);
        }

        $cant = $query->count();

        if ($request->has('paginate') && $request->paginate > 0) {
            $result = $query->paginate($request->paginate);
            return ServicioResource::collection($result);
        }

        return response([
            'data' => ServicioResource::collection($query->get()),
            'total' => $cant,
            'message' => 'Consulta satisfactoria.',
        ], 200);
    }

    /**
    * Listar un servicio
    * Devuelve un servicio por el ID
    */
    public function show(Servicio $servicio)
    {
        return response([
            'data' => new ServicioResource($servicio),
            'message' => 'Consulta satisfactoria.',
        ], 200);
    }

    /**
    * Crear un servicio
    *
    * Crea un nuevo servicio
    */
    public function create(ServicioStoreRequest_v1 $request)
    {
        $servicio = Servicio::create([
            'nombre' => $request->nombre,
        ]);

        return response([
            'data' => new ServicioResource($servicio),
            'message' => 'Creacion satisfactoria.',
        ], 200);
    }

    /**
    * Actualizar un servicio
    *
    * Actualiza un servicio
    */
    public function update(ServicioUpdateRequest_v1 $request, Servicio $servicio)
    {
        if ($request->has('nombre')) {
            $servicio->nombre = $request->nombre;
            $servicio->save();
        }

        return response([
            'data' => new ServicioResource($servicio),
            'message' => 'Actualizacion satisfactoria.',
        ], 200);
    }

    /**
    * Eliminar un servicio
    *
    * Elimina un servicio
    */
    public function delete(Request $request, Servicio $servicio)
    {
        $servicio->delete();

        return response([
            'data' => new ServicioResource($servicio),
            'message' => 'Eliminacion satisfactoria.',
        ], 200);
    }
}