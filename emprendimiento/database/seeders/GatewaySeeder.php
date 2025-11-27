<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Gateway;

class GatewaySeeder extends Seeder
{
    public function run(): void
    {
        Gateway::create([
            'codigo_gateway' => 'GW-0001',
            'descripcion' => 'Gateway principal',
            'ubicacion' => 'Azotea',
        ]);
    }
}