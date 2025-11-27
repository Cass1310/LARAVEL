<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SuscripcionPago;
use App\Models\Suscripcion;

class SuscripcionPagoSeeder extends Seeder
{
    public function run(): void
    {
        $suscripciones = Suscripcion::all();

        foreach ($suscripciones as $suscripcion) {
            SuscripcionPago::create([
                'id_suscripcion' => $suscripcion->id,
                'periodo' => $suscripcion->tipo,
                'monto' => $suscripcion->tipo == 'anual' ? 150.00 : 15.00,
                'estado' => 'pagado',
                'fecha_pago' => '2025-11-05',
            ]);
        }
    }
}