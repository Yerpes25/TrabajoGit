<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * FormRequest para actualizar un bono existente.
 *
 * Regla: Solo admin puede actualizar bonos (autorización vía middleware).
 */
class UpdateBonusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * La autorización real se hace vía middleware role:admin.
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
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'seconds_total' => ['sometimes', 'required', 'integer', 'min:1'], // Mínimo 1 segundo
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
