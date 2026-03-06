<?php

namespace App\Services;

use App\Models\Client;
use App\Models\ClientProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Service para gestionar usuarios y sus entidades relacionadas.
 *
 * Lógica de negocio centralizada para crear/actualizar usuarios, clientes y técnicos.
 * Regla: Al crear un cliente, se crea User + Client + ClientProfile en transacción.
 */
class UserService
{
    /**
     * Crea un técnico (usuario con role=technician).
     *
     * Regla: Solo crea el User, no hay entidad adicional como Client.
     *
     * @param array $data Datos del técnico (name, email, password, is_active)
     * @return User Usuario creado
     * @throws \Exception Si falla la creación
     */
    public function createTechnician(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => 'technician',
                'is_active' => $data['is_active'] ?? true,
            ]);

            return $user;
        });
    }

    /**
     * Crea un cliente completo: User + Client + ClientProfile.
     *
     * Regla: Se crean las tres entidades en una transacción para mantener integridad.
     * - User con role=client
     * - Client vinculado al User (user_id)
     * - ClientProfile con balance_seconds inicial 0
     *
     * @param array $userData Datos del usuario (name, email, password, is_active)
     * @param array $clientData Datos del cliente (legal_name, tax_id, phone, address, notes)
     * @return Client Cliente creado (con relaciones cargadas)
     * @throws \Exception Si falla la creación
     */
    public function createClient(array $userData, array $clientData): Client
    {
        return DB::transaction(function () use ($userData, $clientData) {
            // Crear User con role=client
            $user = User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => Hash::make($userData['password']),
                'role' => 'client',
                'is_active' => $userData['is_active'] ?? true,
            ]);

            // Crear Client vinculado al User
            $client = Client::create([
                'legal_name' => $clientData['legal_name'] ?? null,
                'tax_id' => $clientData['tax_id'] ?? null,
                'phone' => $clientData['phone'] ?? null,
                'address' => $clientData['address'] ?? null,
                'notes' => $clientData['notes'] ?? null,
                'user_id' => $user->id, // FK a users.id
            ]);

            // Crear ClientProfile con balance_seconds inicial 0
            ClientProfile::create([
                'client_id' => $client->id,
                'balance_seconds' => 0, // Saldo inicial en segundos
            ]);

            // Cargar relaciones para retornar
            $client->load('user', 'profile');

            return $client;
        });
    }

    /**
     * Actualiza un técnico existente.
     *
     * @param User $user Usuario técnico a actualizar
     * @param array $data Datos a actualizar
     * @return User Usuario actualizado
     */
    public function updateTechnician(User $user, array $data): User
    {
        // Verificar que es técnico
        if ($user->role !== 'technician') {
            throw new \InvalidArgumentException('El usuario no es un técnico.');
        }

        $updateData = [
            'name' => $data['name'] ?? $user->name,
            'email' => $data['email'] ?? $user->email,
            'is_active' => $data['is_active'] ?? $user->is_active,
        ];

        // Actualizar password solo si se proporciona
        if (isset($data['password']) && !empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $user->update($updateData);

        return $user->fresh();
    }

    /**
     * Actualiza un cliente existente (User + Client).
     *
     * Regla: Actualiza User y Client en transacción para mantener integridad.
     *
     * @param Client $client Cliente a actualizar
     * @param array $userData Datos del usuario a actualizar
     * @param array $clientData Datos del cliente a actualizar
     * @return Client Cliente actualizado (con relaciones cargadas)
     */
    public function updateClient(Client $client, array $userData, array $clientData): Client
    {
        return DB::transaction(function () use ($client, $userData, $clientData) {
            $user = $client->user;

            if (!$user) {
                throw new \InvalidArgumentException('El cliente no tiene un usuario asociado.');
            }

            // Actualizar User
            $userUpdateData = [
                'name' => $userData['name'] ?? $user->name,
                'email' => $userData['email'] ?? $user->email,
                'is_active' => $userData['is_active'] ?? $user->is_active,
            ];

            // Actualizar password solo si se proporciona
            if (isset($userData['password']) && !empty($userData['password'])) {
                $userUpdateData['password'] = Hash::make($userData['password']);
            }

            $user->update($userUpdateData);

            // Actualizar Client
            $client->update([
                'legal_name' => $clientData['legal_name'] ?? $client->legal_name,
                'tax_id' => $clientData['tax_id'] ?? $client->tax_id,
                'phone' => $clientData['phone'] ?? $client->phone,
                'address' => $clientData['address'] ?? $client->address,
                'notes' => $clientData['notes'] ?? $client->notes,
            ]);

            // Cargar relaciones para retornar
            $client->load('user', 'profile');

            return $client;
        });
    }

    /**
     * Verifica si un usuario tiene actividad asociada.
     *
     * Regla: Un usuario tiene actividad si tiene:
     * - Técnico: work_reports donde technician_id = user.id
     * - Cliente: work_reports donde client_id = client.id O balance_movements donde client_id = client.id
     *
     * @param User $user Usuario a verificar
     * @return bool True si tiene actividad, false en caso contrario
     */
    public function hasActivity(User $user): bool
    {
        if ($user->role === 'technician') {
            // Técnico: verificar si tiene partes de trabajo
            return $user->workReports()->exists();
        }

        if ($user->role === 'client') {
            $client = $user->client;
            if (!$client) {
                return false;
            }

            // Cliente: verificar si tiene partes de trabajo o movimientos de saldo
            return $client->workReports()->exists() || $client->balanceMovements()->exists();
        }

        // Admin no tiene actividad asociada (no se puede desactivar por esta razón)
        return false;
    }

    /**
     * Desactiva un usuario (is_active=false).
     *
     * Regla: Si tiene actividad, solo se desactiva, no se elimina.
     * Esto mantiene la integridad referencial de los datos históricos.
     *
     * @param User $user Usuario a desactivar
     * @return User Usuario desactivado
     */
    public function deactivate(User $user): User
    {
        $user->update(['is_active' => false]);
        return $user->fresh();
    }
}
