<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * FormRequest para actualizar un parte de trabajo desde el panel admin.
 *
 * Regla: Solo se permiten editar campos básicos (title, description, summary).
 * NO se permite cambiar tiempos manualmente (regla: tiempos solo vía cronómetro).
 */
class UpdateWorkReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * La autorización real se hace vía middleware role:admin y Policy.
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
