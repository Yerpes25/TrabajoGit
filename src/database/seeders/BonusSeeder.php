<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BonusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('bonuses')->insert([
            [
                'name' => 'Bono Web Básico',
                'description' => 'Bono para pequeñas modificaciones en páginas web: textos, imágenes o estilos.',
                'seconds_total' => 18000, // 5 horas
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Bono Desarrollo Web',
                'description' => 'Bono para desarrollo de nuevas funcionalidades en páginas web.',
                'seconds_total' => 36000, // 10 horas
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Bono Tienda Online',
                'description' => 'Bono específico para mejoras o mantenimiento de ecommerce.',
                'seconds_total' => 54000, // 15 horas
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Bono Optimización Web',
                'description' => 'Bono para optimización de rendimiento, velocidad y SEO técnico.',
                'seconds_total' => 28800, // 8 horas
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Bono Mantenimiento Web',
                'description' => 'Bono para mantenimiento mensual de páginas web.',
                'seconds_total' => 72000, // 20 horas
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
