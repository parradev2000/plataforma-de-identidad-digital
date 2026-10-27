<?php

namespace App\Http\Controllers;

use App\Http\Resources\EstadoResource;
use App\Models\Estado;
use App\Http\Requests\EstadoStoreRequest_v1;
use App\Http\Requests\EstadoUpdateRequest_v1;
use Illuminate\Http\Request;
use G4T\Swagger\Attributes\SwaggerSection;

#[SwaggerSection('Rutas de los estados')]
class EstadoController extends Controller
{
    ////////////////// Version 1.0 ////////////////////////

    /**
    * Listar estados
    *
    * Devuelve el listado de los estados
    */
    public function list(Request $request)
    {
        $query = Estado::query()->orderBy('nombre');

        if ($request->has('nombre')) {
            $query->whereRaw('LOWER(nombre) LIKE ?', ['%' . strtolower($request->nombre) . '%']);
        }

        $cant = $query->count();

        if ($request->has('paginate') && $request->paginate > 0) {
            $result = $query->paginate($request->paginate);
            return EstadoResource::collection($result);
        }

        return response([
            'data' => EstadoResource::collection($query->get()),
            'total' => $cant,
            'message' => 'Consulta satisfactoria.',
        ], 200);
    }

    /**
    * Listar un estado
    * Devuelve un estado por el ID
    */
    public function show(Estado $estado)
    {
        return response([
            'data' => new EstadoResource($estado),
            'message' => 'Consulta satisfactoria.',
        ], 200);
    }

    /**
    * Crear un estado
    *
    * Crea un nuevo estado
    */
    public function create(EstadoStoreRequest_v1 $request)
    {
        $estado = Estado::create([
            'nombre' => $request->nombre,
        ]);

        return response([
            'data' => new EstadoResource($estado),
            'message' => 'Creacion satisfactoria.',
        ], 200);
    }

    /**
    * Actualizar un estado
    *
    * Actualiza un estado
    */
    public function update(EstadoUpdateRequest_v1 $request, Estado $estado)
    {
        if ($request->has('nombre')) {
            $estado->nombre = $request->nombre;
            $estado->save();
        }

        return response([
            'data' => new EstadoResource($estado),
            'message' => 'Actualizacion satisfactoria.',
        ], 200);
    }

    /**
    * Eliminar un estado
    *
    * Elimina un estado
    */
    public function delete(Request $request, Estado $estado)
    {
        $estado->delete();

        return response([
            'data' => new EstadoResource($estado),
            'message' => 'Eliminacion satisfactoria.',
        ], 200);
    }
}