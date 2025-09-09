<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\Medidor; 
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ConsumoAgua>
 */
class ConsumoAguaFactory extends Factory
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
            'fecha_hora' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'litros' => $this->faker->randomFloat(2, 10, 500),
            'caudal' => $this->faker->randomFloat(2, 0.5, 15),
            'voltaje_bateria' => $this->faker->randomFloat(2, 3.0, 4.2),
        ];
    }

}
