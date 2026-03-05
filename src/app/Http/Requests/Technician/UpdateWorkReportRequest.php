<?php

namespace App\Http\Requests\Technician;

use Illuminate\Foundation\Http\FormRequest;

/**
 * FormRequest para actualizar un parte de trabajo.
 *
 * Regla: Solo se permiten editar campos básicos (title, description, summary).
 * NO se permite cambiar tiempos manualmente (regla: tiempos solo vía cronómetro).
 */
class UpdateWorkReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * La autorización real se hace vía middleware role:technician y verificación de pertenencia.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Regla: Solo se permiten editar campos básicos, no tiempos.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'summary' => ['sometimes', 'nullable', 'string'],
        ];
    }
}
