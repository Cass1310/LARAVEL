<?php

namespace Database\Factories;

use App\Models\Departamento; 
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str; 
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Medidor>
 */
class MedidorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'device_id' => strtoupper(Str::random(12)),
            'departamento_id' => Departamento::factory(),
        ];
    }

}
