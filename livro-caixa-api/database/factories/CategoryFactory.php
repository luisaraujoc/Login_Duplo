<?php

namespace Database\Factories;

use App\Enums\CashFlowType;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'type' => fake()->randomElement(CashFlowType::cases()),
        ];
    }

    public function credit(): static
    {
        return $this->state(['type' => CashFlowType::Credit]);
    }

    public function debit(): static
    {
        return $this->state(['type' => CashFlowType::Debit]);
    }
}
