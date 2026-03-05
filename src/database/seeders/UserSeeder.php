<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Crea usuarios de prueba para cada rol según la tarea:
     * - admin@local.test / password
     * - tech@local.test / password
     * - client@local.test / password
     */
    public function run(): void
    {
        // Admin
        User::firstOrCreate(
            ['email' => 'admin@local.test'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Technician
        User::firstOrCreate(
            ['email' => 'tech@local.test'],
            [
                'name' => 'Técnico',
                'password' => Hash::make('password'),
                'role' => 'technician',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Client
        User::firstOrCreate(
            ['email' => 'client@local.test'],
            [
                'name' => 'Cliente',
                'password' => Hash::make('password'),
                'role' => 'client',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
