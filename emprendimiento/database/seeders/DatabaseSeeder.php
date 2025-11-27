<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            ClienteSeeder::class,
            GatewaySeeder::class,
            EdificioSeeder::class,
            DepartamentoSeeder::class,
            MedidorSeeder::class,
            ResidenteDepartamentoSeeder::class,
            SuscripcionSeeder::class,
            ConsumoAguaSeeder::class,
            AlertaSeeder::class,
            MantenimientoSeeder::class,
        ]);
    }
}
