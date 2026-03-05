<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Policy para gestionar permisos de Client.
 *
 * Reglas de autorización:
 * - Admin: puede ver/gestionar todo
 * - Client: solo puede ver su propio cliente (auth()->user()->client->id)
 */
class ClientPolicy
{
    /**
     * Determina si el usuario puede ver cualquier cliente.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        // Admin puede ver todos, client solo ve el suyo (filtrado en query)
        return true;
    }

    /**
     * Determina si el usuario puede ver un cliente específico.
     *
     * Reglas:
     * - Admin: puede ver cualquier cliente
     * - Client: solo su propio cliente (auth()->user()->client->id)
     *
     * @param User $user
     * @param Client $client
     * @return bool|Response
     */
    public function view(User $user, Client $client): bool|Response
    {
        // Admin: puede ver cualquier cliente
        if ($user->isAdmin()) {
            return true;
        }

        // Client: solo su propio cliente
        if ($user->isClient()) {
            $userClient = $user->client;
            if (!$userClient) {
                return false;
            }
            return $client->id === $userClient->id;
        }

        return false;
    }

    /**
     * Determina si el usuario puede ver el saldo de un cliente.
     *
     * Reglas:
     * - Admin: puede ver el saldo de cualquier cliente
     * - Client: solo el saldo de su propio cliente
     *
     * @param User $user
     * @param Client $client
     * @return bool|Response
     */
    public function viewBalance(User $user, Client $client): bool|Response
    {
        // Admin: puede ver el saldo de cualquier cliente
        if ($user->isAdmin()) {
            return true;
        }

        // Client: solo el saldo de su propio cliente
        if ($user->isClient()) {
            $userClient = $user->client;
            if (!$userClient) {
                return false;
            }
            return $client->id === $userClient->id;
        }

        return false;
    }

    /**
     * Determina si el usuario puede ver los partes de trabajo de un cliente.
     *
     * Reglas:
     * - Admin: puede ver los partes de cualquier cliente
     * - Client: solo los partes de su propio cliente
     *
     * @param User $user
     * @param Client $client
     * @return bool|Response
     */
    public function viewWorkReports(User $user, Client $client): bool|Response
    {
        // Admin: puede ver los partes de cualquier cliente
        if ($user->isAdmin()) {
            return true;
        }

        // Client: solo los partes de su propio cliente
        if ($user->isClient()) {
            $userClient = $user->client;
            if (!$userClient) {
                return false;
            }
            return $client->id === $userClient->id;
        }

        return false;
    }

    /**
     * Determina si el usuario puede crear clientes.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        // Solo admin puede crear clientes
        return $user->isAdmin();
    }

    /**
     * Determina si el usuario puede actualizar un cliente.
     *
     * @param User $user
     * @param Client $client
     * @return bool|Response
     */
    public function update(User $user, Client $client): bool|Response
    {
        // Solo admin puede actualizar clientes
        return $user->isAdmin();
    }

    /**
     * Determina si el usuario puede eliminar un cliente.
     *
     * @param User $user
     * @param Client $client
     * @return bool|Response
     */
    public function delete(User $user, Client $client): bool|Response
    {
        // Solo admin puede eliminar clientes
        return $user->isAdmin();
    }
}
