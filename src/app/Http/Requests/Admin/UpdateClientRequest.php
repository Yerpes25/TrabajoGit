<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * FormRequest para actualizar un cliente existente.
 *
 * Regla: Actualiza User + Client en transacción.
 * Password es opcional en update (solo se actualiza si se proporciona).
 */
class UpdateClientRequest extends FormRequest
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
        $client = $this->route('client');
        $userId = $client->user_id ?? null;

        return [
            // Datos del usuario (User)
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', \Illuminate\Validation\Rule::unique('users')->ignore($userId)],
            'password' => ['sometimes', 'nullable', 'string', 'min:8', 'confirmed'],
            'is_active' => ['sometimes', 'boolean'],

            // Datos del cliente (Client)
            'client_name' => ['sometimes', 'required', 'string', 'max:255'],
            'legal_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'tax_id' => ['sometimes', 'nullable', 'string', 'max:255'],
            'client_email' => ['sometimes', 'nullable', 'string', 'email', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:255'],
            'address' => ['sometimes', 'nullable', 'string'],
            'notes' => ['sometimes', 'nullable', 'string'],
        ];
    }

    /**
     * Obtiene los datos del usuario para actualizar el User.
     *
     * @return array
     */
    public function getUserData(): array
    {
        return array_filter([
            'name' => $this->input('name'),
            'email' => $this->input('email'),
            'password' => $this->input('password'),
            'is_active' => $this->input('is_active'),
        ], fn($value) => $value !== null);
    }

    /**
     * Obtiene los datos del cliente para actualizar el Client.
     *
     * @return array
     */
    public function getClientData(): array
    {
        return array_filter([
            'name' => $this->input('client_name'),
            'legal_name' => $this->input('legal_name'),
            'tax_id' => $this->input('tax_id'),
            'email' => $this->input('client_email'),
            'phone' => $this->input('phone'),
            'address' => $this->input('address'),
            'notes' => $this->input('notes'),
        ], fn($value) => $value !== null);
    }
}
