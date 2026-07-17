<?php

namespace Database\Factories;

use App\Models\FuelProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FuelProduct>
 */
class FuelProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->numerify('###'),
            'name' => fake()->randomElement(['Gasolina Comum', 'Gasolina Aditivada', 'Etanol', 'Diesel S10', 'GNV']),
            'unit' => 'L',
        ];
    }
}
