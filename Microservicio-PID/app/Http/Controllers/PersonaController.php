<?php

namespace App\Http\Controllers;

use App\Http\Resources\PersonaResource;
use App\Models\Persona;
use App\Http\Requests\PersonaStoreRequest_v1;
use App\Http\Requests\PersonaUpdateRequest_v1;
use App\Http\Requests\PersonaListRequest_v1;
use Illuminate\Http\Request;
use G4T\Swagger\Attributes\SwaggerSection;

#[SwaggerSection('Rutas de las personas')]
class PersonaController extends Controller
{
    ////////////////// Version 1.0 ////////////////////////

    /**
    * Listar personas
    *
    * Devuelve el listado de las personas
    */
    public function list(PersonaListRequest_v1 $request)
    {
        $solicitudesRel = $request->input('solicitudes_rel');
        $showSolicitudes = is_string($solicitudesRel) ? in_array(strtolower($solicitudesRel), ['true','1','on','yes']) : filter_var($solicitudesRel, FILTER_VALIDATE_BOOLEAN);

        $query = Persona::query()->orderBy('nombres')->orderBy('apellidos');

        if ($showSolicitudes === true) {
            $query->with('solicitudes');
        }

        if ($request->has('nombres')) {
            $query->whereRaw('LOWER(nombres) LIKE ?', ['%' . strtolower($request->nombres) . '%']);
        }

        if ($request->has('apellidos')) {
            $query->whereRaw('LOWER(apellidos) LIKE ?', ['%' . strtolower($request->apellidos) . '%']);
        }

        if ($request->has('ci')) {
            $query->where('ci', 'LIKE', '%' . $request->ci . '%');
        }

        $cant = $query->count();

        if ($request->has('paginate') && $request->paginate > 0) {
            $result = $query->paginate($request->paginate);
            if ($showSolicitudes === true) {
                $result->load('solicitudes');
            }
            return PersonaResource::collection($result);
        }

        $items = $query->get();
        if ($showSolicitudes === true) {
            $items->load('solicitudes');
        }

        return response([
            'data' => PersonaResource::collection($items),
            'total' => $cant,
            'message' => 'Consulta satisfactoria.',
        ], 200);
    }

    /**
    * Listar una persona
    * Devuelve una persona por el ID
    */
    public function show(Persona $persona)
    {
        return response([
            'data' => new PersonaResource($persona),
            'message' => 'Consulta satisfactoria.',
        ], 200);
    }

    /**
    * Crear una persona
    *
    * Crea una nueva persona
    */
    public function create(PersonaStoreRequest_v1 $request)
    {
        $persona = Persona::create([
            'nombres' => $request->nombres,
            'apellidos' => $request->apellidos,
            'ci' => $request->ci,
        ]);

        return response([
            'data' => new PersonaResource($persona),
            'message' => 'Creacion satisfactoria.',
        ], 200);
    }

    /**
    * Actualizar una persona
    *
    * Actualiza una persona
    */
    public function update(PersonaUpdateRequest_v1 $request, Persona $persona)
    {
        $data = [];
        if ($request->has('nombres')) {
            $data['nombres'] = $request->nombres;
        }
        if ($request->has('apellidos')) {
            $data['apellidos'] = $request->apellidos;
        }
        if ($request->has('ci')) {
            $data['ci'] = $request->ci;
        }

        $persona->update($data);

        return response([
            'data' => new PersonaResource($persona),
            'message' => 'Actualizacion satisfactoria.',
        ], 200);
    }

    /**
    * Eliminar una persona
    *
    * Elimina una persona
    */
    public function delete(Request $request, Persona $persona)
    {
        $persona->delete();

        return response([
            'data' => new PersonaResource($persona),
            'message' => 'Eliminacion satisfactoria.',
        ], 200);
    }
}