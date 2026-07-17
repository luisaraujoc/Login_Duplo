<?php

namespace Database\Factories;

use App\Enums\CashFlowType;
use App\Models\Book;
use App\Models\Category;
use App\Models\Movement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Movement>
 */
class MovementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'page_number' => fake()->numberBetween(1, 10),
            'type' => fake()->randomElement(CashFlowType::cases()),
            'description' => fake()->sentence(3),
            'amount' => fake()->randomFloat(2, 10, 5000),
            'movement_date' => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
        ];
    }

    /**
     * book_id/account_id are left out of definition() on purpose: a book
     * always belongs to a specific account, so the two must be created
     * together (or explicitly overridden by the caller) rather than each
     * spinning up its own unrelated Account::factory().
     */
    public function configure(): static
    {
        return $this->afterMaking(function (Movement $movement) {
            if (! $movement->book_id) {
                $movement->book_id = Book::factory()->create()->id;
            }

            if (! $movement->account_id) {
                $movement->account_id = Book::find($movement->book_id)->account_id;
            }
        });
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
