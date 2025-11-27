<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Mantenimiento;
use App\Models\Medidor;
use Carbon\Carbon;

class MantenimientoSeeder extends Seeder
{
    public function run(): void
    {
        $medidores = Medidor::all();
        
        $tiposMantenimiento = [
            'preventivo' => [
                'cobertura' => 'incluido_suscripcion',
                'costo' => 60.00,
                'descripciones' => [
                    'Mantenimiento preventivo programado',
                    'Revisión periódica del medidor',
                    'Limpieza y calibración preventiva',
                    'Verificación de funcionamiento',
                    'Inspección de componentes internos'
                ]
            ],
            'correctivo' => [
                'cobertura' => 'cobrado',
                'costo' => 100.00,
                'descripciones' => [
                    'Reparación de sensor de flujo',
                    'Cambio de batería del medidor',
                    'Reparación de circuito interno',
                    'Sustitución de componentes dañados',
                    'Corrección de lecturas erróneas'
                ]
            ],
            'instalacion' => [
                'cobertura' => 'cobrado', 
                'costo' => 150.00,
                'descripciones' => [
                    'Instalación inicial del medidor',
                    'Reinstalación después de mantenimiento mayor',
                    'Cambio de ubicación del medidor',
                    'Instalación en nuevo departamento'
                ]
            ],
            'calibracion' => [
                'cobertura' => 'incluido_suscripcion',
                'costo' => 80.00,
                'descripciones' => [
                    'Calibración de precisión del medidor',
                    'Ajuste de sensibilidad del sensor',
                    'Re-calibración post-reparación',
                    'Verificación de exactitud de lecturas'
                ]
            ]
        ];

        $estados = ['pendiente', 'en_proceso', 'completado', 'cancelado'];
        
        foreach ($medidores as $medidor) {
            // Cada medidor tendrá entre 2 y 6 mantenimientos
            $cantidadMantenimientos = rand(2, 6);
            
            for ($i = 0; $i < $cantidadMantenimientos; $i++) {
                $tipo = array_rand($tiposMantenimiento);
                $tipoConfig = $tiposMantenimiento[$tipo];
                
                // Fecha aleatoria en los últimos 12 meses
                $fecha = Carbon::now()->subMonths(rand(0, 11))->subDays(rand(0, 30));
                
                // Estado aleatorio, pero más probabilidad de completado para mantenimientos antiguos
                $estado = $this->getEstadoAleatorio($fecha, $estados);
                
                // Costo puede variar ligeramente
                $costo = $tipoConfig['costo'];
                if ($tipoConfig['cobertura'] == 'cobrado') {
                    $costo += rand(-20, 20); // Variación de ±20
                    $costo = max(0, $costo); // No negativo
                }
                
                Mantenimiento::create([
                    'id_medidor' => $medidor->id,
                    'tipo' => $tipo,
                    'cobertura' => $tipoConfig['cobertura'],
                    'costo' => $costo,
                    'fecha' => $fecha->format('Y-m-d'),
                    'descripcion' => $tipoConfig['descripciones'][array_rand($tipoConfig['descripciones'])],
                    'estado' => $estado,
                    'created_at' => $fecha,
                    'updated_at' => $estado == 'completado' ? $fecha->copy()->addDays(rand(1, 5)) : $fecha,
                ]);
            }
            
            // Asegurar que cada medidor tenga al menos un mantenimiento preventivo reciente
            $this->crearMantenimientoPreventivoReciente($medidor, $tiposMantenimiento['preventivo']);
        }
        
        // Crear algunos mantenimientos futuros programados
        $this->crearMantenimientosFuturos($medidores, $tiposMantenimiento);
    }
    
    private function getEstadoAleatorio(Carbon $fecha, array $estados): string
    {
        $diasDesdeMantenimiento = $fecha->diffInDays(Carbon::now());
        
        // Si el mantenimiento es muy antiguo, probablemente está completado
        if ($diasDesdeMantenimiento > 60) {
            $probabilidades = ['completado' => 70, 'cancelado' => 20, 'en_proceso' => 5, 'pendiente' => 5];
        }
        // Si es reciente, puede estar en proceso o pendiente
        elseif ($diasDesdeMantenimiento > 30) {
            $probabilidades = ['completado' => 40, 'en_proceso' => 30, 'pendiente' => 20, 'cancelado' => 10];
        }
        // Si es muy reciente, probablemente pendiente o en proceso
        else {
            $probabilidades = ['pendiente' => 50, 'en_proceso' => 30, 'completado' => 15, 'cancelado' => 5];
        }
        
        return $this->seleccionarConProbabilidad($probabilidades);
    }
    
    private function seleccionarConProbabilidad(array $probabilidades): string
    {
        $total = array_sum($probabilidades);
        $random = rand(1, $total);
        $current = 0;
        
        foreach ($probabilidades as $estado => $probabilidad) {
            $current += $probabilidad;
            if ($random <= $current) {
                return $estado;
            }
        }
        
        return array_key_first($probabilidades);
    }
    
    private function crearMantenimientoPreventivoReciente($medidor, $preventivoConfig): void
    {
        // Verificar si ya tiene un preventivo reciente
        $tienePreventivoReciente = Mantenimiento::where('id_medidor', $medidor->id)
            ->where('tipo', 'preventivo')
            ->where('fecha', '>=', Carbon::now()->subMonths(3))
            ->exists();
            
        if (!$tienePreventivoReciente) {
            Mantenimiento::create([
                'id_medidor' => $medidor->id,
                'tipo' => 'preventivo',
                'cobertura' => $preventivoConfig['cobertura'],
                'costo' => $preventivoConfig['costo'],
                'fecha' => Carbon::now()->subDays(rand(1, 90))->format('Y-m-d'),
                'descripcion' => $preventivoConfig['descripciones'][array_rand($preventivoConfig['descripciones'])],
                'estado' => 'completado',
            ]);
        }
    }
    
    private function crearMantenimientosFuturos($medidores, $tiposMantenimiento): void
    {
        // 30% de los medidores tendrán mantenimientos futuros programados
        $medidoresConFuturos = $medidores->random(ceil($medidores->count() * 0.3));
        
        foreach ($medidoresConFuturos as $medidor) {
            $tipo = rand(0, 1) ? 'preventivo' : 'calibracion'; // Solo preventivos o calibraciones futuras
            
            Mantenimiento::create([
                'id_medidor' => $medidor->id,
                'tipo' => $tipo,
                'cobertura' => $tiposMantenimiento[$tipo]['cobertura'],
                'costo' => $tiposMantenimiento[$tipo]['costo'],
                'fecha' => Carbon::now()->addDays(rand(15, 90))->format('Y-m-d'),
                'descripcion' => $tiposMantenimiento[$tipo]['descripciones'][array_rand($tiposMantenimiento[$tipo]['descripciones'])] . ' - PROGRAMADO',
                'estado' => 'pendiente',
            ]);
        }
    }
}