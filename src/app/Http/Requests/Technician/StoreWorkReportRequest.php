<?php

namespace App\Http\Requests\Technician;

use Illuminate\Foundation\Http\FormRequest;

/**
 * FormRequest para crear un nuevo parte de trabajo.
 *
 * Regla: Solo el técnico autenticado puede crear partes (autorización vía middleware).
 */
class StoreWorkReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * La autorización real se hace vía middleware role:technician.
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
            'client_id' => ['required', 'integer', 'exists:clients,id'],
            'title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
        ];
    }
}
