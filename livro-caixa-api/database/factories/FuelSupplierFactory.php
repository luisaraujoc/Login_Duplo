<?php

namespace Database\Factories;

use App\Models\FuelSupplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FuelSupplier>
 */
class FuelSupplierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_name' => fake()->company(),
            'address' => fake()->streetAddress(),
            'neighborhood' => fake()->citySuffix(),
            'city' => fake()->city(),
            'state' => fake()->stateAbbr(),
            'zip_code' => fake()->numerify('########'),
            'phone' => fake()->numerify('###########'),
            'cnpj' => fake()->numerify('##############'),
        ];
    }
}
