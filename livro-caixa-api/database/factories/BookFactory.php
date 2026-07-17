<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Book;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Book>
 */
class BookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'number' => fake()->unique()->numberBetween(1, 1000),
            'label' => null,
        ];
    }
}
