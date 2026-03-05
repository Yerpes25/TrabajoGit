<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * FormRequest para crear un nuevo bono en el catálogo.
 *
 * Regla: Solo admin puede crear bonos (autorización vía middleware).
 */
class StoreBonusRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'seconds_total' => ['required', 'integer', 'min:1'], // Mínimo 1 segundo
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
