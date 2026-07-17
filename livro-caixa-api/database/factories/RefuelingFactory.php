<?php

namespace Database\Factories;

use App\Models\FuelProduct;
use App\Models\FuelSupplier;
use App\Models\Refueling;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Refueling>
 */
class RefuelingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'vehicle_id' => Vehicle::factory(),
            'fuel_supplier_id' => FuelSupplier::factory(),
            'fuel_product_id' => FuelProduct::factory(),
            'nfe_link_id' => null,
            'invoice_number' => fake()->unique()->numerify('####'),
            'access_key' => null,
            'refueled_at' => fake()->dateTimeBetween('-6 months', 'now'),
            'odometer_km' => fake()->numberBetween(0, 200000),
            'quantity' => fake()->randomFloat(3, 5, 60),
            'unit_price' => fake()->randomFloat(4, 4, 7),
            'notes' => null,
            'target_efficiency_km_per_liter' => 14.10,
        ];
    }
}
