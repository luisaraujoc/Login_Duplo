<?php

namespace Database\Factories;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vehicle>
 */
class VehicleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $manufactureYear = fake()->numberBetween(2000, 2024);

        return [
            'plate' => strtoupper(fake()->unique()->bothify('???#?##')),
            'brand' => fake()->randomElement(['Fiat', 'Volkswagen', 'Chevrolet', 'Toyota', 'Ford']),
            'model' => fake()->word(),
            'manufacture_year' => $manufactureYear,
            'model_year' => $manufactureYear + 1,
            'renavam' => fake()->numerify('###########'),
            'chassis' => strtoupper(fake()->bothify('#################')),
        ];
    }
}
