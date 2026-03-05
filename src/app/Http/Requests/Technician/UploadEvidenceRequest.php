<?php

namespace App\Http\Requests\Technician;

use Illuminate\Foundation\Http\FormRequest;

/**
 * FormRequest para subir una evidencia (archivo) a un parte.
 *
 * Regla: Evidencias son opcionales, sin límite práctico.
 */
class UploadEvidenceRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'max:10240'], // Máximo 10MB
            'metadata' => ['sometimes', 'nullable', 'array'],
        ];
    }
}
