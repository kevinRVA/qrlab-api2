<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Laboratory;

class CoordinatorSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('12345678');

        /**
         * Coordinadores y sus laboratorios asignados.
         * El número de laboratorio corresponde al nombre "Laboratorio N"
         * que existe en la tabla laboratories (seeded por DatabaseSeeder).
         */
        $coordinadores = [
            [
                'name'  => 'Karen Hernandez',
                'email' => 'karen.hernandez@qrlab.com',
                'code'  => 'COORD-001',
                'labs'  => [1, 2, 8, 7],
            ],  
            [
                'name'  => 'Francisco Linares',
                'email' => 'francisco.linares@qrlab.com',
                'code'  => 'COORD-002',
                'labs'  => [1, 2, 8, 7],
            ],
            [
                'name'  => 'Evelyn Saravia',
                'email' => 'evelyn.saravia@qrlab.com',
                'code'  => 'COORD-003',
                'labs'  => [10, 4, 11, 3],
            ],
            [
                'name'  => 'Mario Valdez',
                'email' => 'mario.valdez@qrlab.com',
                'code'  => 'COORD-004',
                'labs'  => [10, 4, 11, 3],
            ],
            [
                'name'  => 'Nelson Lopez',
                'email' => 'nelson.lopez@qrlab.com',
                'code'  => 'COORD-005',
                'labs'  => [14, 15, 12],
            ],
            [
                'name'  => 'Marvin Mira',
                'email' => 'marvin.mira@qrlab.com',
                'code'  => 'COORD-006',
                'labs'  => [6, 9, 5],
            ],
            [
                'name'  => 'Edwin Martinez',
                'email' => 'edwin.martinez@qrlab.com',
                'code'  => 'COORD-007',
                'labs'  => [6, 9, 5],
            ],
            [
                'name'  => 'Ruben Escobar',
                'email' => 'ruben.escobar@qrlab.com',
                'code'  => 'COORD-008',
                'labs'  => [13],
            ],
        ];

        foreach ($coordinadores as $data) {
            // Crear (o encontrar si ya existe) el usuario coordinador
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'      => $data['name'],
                    'password'  => $password,
                    'role'      => 'coordinador',
                    'user_code' => $data['code'],
                ]
            );

            // Asignar los laboratorios por número (ej: 1 → "Laboratorio 1")
            $labIds = Laboratory::whereIn(
                'name',
                array_map(fn($n) => "Laboratorio {$n}", $data['labs'])
            )->pluck('id')->toArray();

            // Sincronizar sin duplicar (sync en pivot)
            $user->coordinatorLabs()->syncWithoutDetaching($labIds);

            $this->command->info("✓ {$data['name']} → Labs: " . implode(', ', $data['labs']));
        }

        $this->command->info('');
        $this->command->info('Contraseña de todos los coordinadores: 12345678');
    }
}
