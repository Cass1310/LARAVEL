<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Medidor; 
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Alerta>
 */
class AlertaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'medidor_id' => Medidor::factory(),
            'tipo' => $this->faker->randomElement(['fuga', 'consumo_alto', 'otro']),
            'mensaje' => $this->faker->sentence(),
            'fecha_hora' => now(),
            'estado' => 'pendiente',
        ];
    }

}
