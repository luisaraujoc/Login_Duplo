<?php

namespace Database\Factories;

use App\Models\NfeLink;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NfeLink>
 */
class NfeLinkFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'url' => fake()->url(),
        ];
    }
}
