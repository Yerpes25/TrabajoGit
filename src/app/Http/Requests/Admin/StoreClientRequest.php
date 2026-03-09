<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * FormRequest para crear un nuevo cliente.
 *
 * Regla: Al crear un cliente, se crea User + Client + ClientProfile.
 * La validación cubre ambos: datos del usuario y datos del cliente.
 */
class StoreClientRequest extends FormRequest
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
            // Datos del usuario (User)
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'confirmed', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[\W]).{8,}$/'],
            'is_active' => ['sometimes', 'boolean'],

            // Datos del cliente (Client)
            'legal_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            // DNI
            'tax_id' => ['sometimes', 'nullable', 'regex:/^[0-9]{8}[A-Za-z]$/'],
            // Teléfono (9 números)
            'phone' => ['sometimes', 'nullable', 'regex:/^[0-9]{9}$/'],
            'address' => ['sometimes', 'nullable', 'string'],
            'notes' => ['sometimes', 'nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'password.regex' => 'La contraseña debe tener mayúscula, minúscula, número y símbolo.',
            'tax_id.regex' => 'El DNI debe tener 8 números y una letra.',
            'phone.regex' => 'El teléfono debe tener 9 números.',
            'email.unique' => 'Este correo ya está registrado.',
        ];
    }

    /**
     * Obtiene los datos del usuario para crear el User.
     *
     * @return array
     */
    public function getUserData(): array
    {
        return [
            'name' => $this->input('name'),
            'email' => $this->input('email'),
            'password' => $this->input('password'),
            'is_active' => $this->input('is_active', true),
        ];
    }

    /**
     * Obtiene los datos del cliente para crear el Client.
     *
     * @return array
     */
    public function getClientData(): array
    {
        return [
            'legal_name' => $this->input('legal_name'),
            'tax_id' => $this->input('tax_id'),
            'phone' => $this->input('phone'),
            'address' => $this->input('address'),
            'notes' => $this->input('notes'),
        ];
    }
}
