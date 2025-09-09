<?php

namespace Database\Factories;

use App\Models\Edificio;
use Illuminate\Database\Eloquent\Factories\Factory;

class DepartamentoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'numero' => $this->faker->unique()->bothify('Dpto-###'),
            'edificio_id' => Edificio::factory(),
        ];
    }
}
