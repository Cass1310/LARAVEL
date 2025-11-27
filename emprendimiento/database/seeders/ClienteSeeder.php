<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cliente;

class ClienteSeeder extends Seeder
{
    public function run(): void
    {
        $clientes = [
            [
                'nit_opcional' => 'NIT002',
                'razon_social' => 'Propietario 2 S.R.L.',
            ],
            [
                'nit_opcional' => 'NIT003',
                'razon_social' => 'Propietario 3 S.R.L.',
            ],
            [
                'nit_opcional' => 'NIT004',
                'razon_social' => 'Propietario 4 S.R.L.',
            ],
            [
                'nit_opcional' => 'NIT000',
                'razon_social' => 'prueba1',
            ],
        ];

        foreach ($clientes as $cliente) {
            Cliente::create($cliente);
        }
    }
}