<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $client = $this->route('client');
        $userId = $client->user_id ?? null;

        return [
            // Datos del usuario (User)
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('users','email')->ignore($userId)],
            'password' => ['sometimes', 'nullable', 'confirmed', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[\W]).{8,}$/'],
            'is_active' => ['sometimes', 'boolean'],
            // Datos del cliente (Client)
            'legal_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            // DNI
            'tax_id' => ['sometimes', 'nullable', 'regex:/^[0-9]{8}[A-Za-z]$/'],
            // Teléfono
            'phone' => ['sometimes', 'nullable', 'regex:/^[0-9]{9}$/'],
            'address' => ['sometimes', 'nullable', 'string'],
            'notes' => ['sometimes', 'nullable', 'string'],
        ];
    }

    /**
     * Mensajes personalizados
     */
    public function messages(): array
    {
        return [
            'email.required' => 'El correo es obligatorio.',
            'email.email' => 'El correo no tiene un formato válido.',
            'email.unique' => 'Este correo ya está registrado.',

            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.regex' => 'La contraseña debe tener mínimo 8 caracteres, mayúscula, minúscula, número y símbolo.',

            'tax_id.regex' => 'El DNI debe tener 8 números y una letra.',

            'phone.regex' => 'El teléfono debe tener 9 números.',

            'name.required' => 'El nombre es obligatorio.',
        ];
    }

    /**
     * Nombres amigables de campos
     */
    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'email' => 'correo electrónico',
            'password' => 'contraseña',
            'tax_id' => 'DNI',
            'phone' => 'teléfono',
            'client_email' => 'correo del cliente',
        ];
    }

    /**
     * Obtiene los datos del usuario para actualizar el User.
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
