<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * FormRequest para emitir un bono a un cliente.
 *
 * Regla: Solo admin puede emitir bonos (autorización vía middleware).
 */
class IssueBonusRequest extends FormRequest
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
            'bonus_id' => ['required', 'integer', Rule::exists('bonuses', 'id')->where('is_active', true)],
            'note' => ['sometimes', 'nullable', 'string'],
        ];
    }
}
