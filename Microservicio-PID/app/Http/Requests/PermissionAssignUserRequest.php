<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PermissionAssignUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|integer',
            'permission_ids' => 'required|array|min:1',
            'permission_ids.*' => 'required|integer',
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'El ID del usuario es requerido.',
            'permission_ids.required' => 'Los permisos son requeridos.',
            'permission_ids.array' => 'Los permisos deben ser un arreglo.',
            'permission_ids.*.required' => 'Cada permiso debe tener un ID válido.',
        ];
    }
}