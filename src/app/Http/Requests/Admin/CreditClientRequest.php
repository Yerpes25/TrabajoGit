<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * FormRequest para asignar saldo (crédito) a un cliente.
 *
 * Regla: El input puede ser en horas (float/int) y se convierte a segundos en el backend.
 * El saldo siempre se maneja en segundos internamente.
 */
class CreditClientRequest extends FormRequest
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
            'hours' => ['required', 'numeric', 'min:0.01'], // Mínimo 0.01 horas (36 segundos)
            'reason' => ['sometimes', 'nullable', 'string', 'max:255'],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ];
    }

    /**
     * Convierte las horas a segundos.
     *
     * Regla: El saldo siempre se maneja en segundos internamente.
     * Este método convierte el input de horas a segundos para pasarlo a BalanceService.
     *
     * @return int Segundos calculados desde las horas
     */
    public function getSeconds(): int
    {
        $hours = (float) $this->input('hours');
        return (int) round($hours * 3600); // 1 hora = 3600 segundos
    }
}
