<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CategorySeeder::class,
            FuelProductSeeder::class,
        ]);

        $user = User::factory()->create([
            'name' => 'Usuário Demo',
            'email' => 'demo@example.com',
            'password' => bcrypt('password'),
        ]);

        $account = Account::query()->create([
            'name' => 'Conta Principal',
            'owner_name' => $user->name,
        ]);

        $user->accounts()->attach($account, ['role' => 'owner']);

        $book = Book::query()->create([
            'account_id' => $account->id,
            'number' => 1,
            'label' => 'Livro 1',
        ]);

        $salary = Category::query()->where('name', 'Salário')->first();
        $food = Category::query()->where('name', 'Alimentação')->first();

        $account->movements()->createMany([
            [
                'book_id' => $book->id,
                'category_id' => $salary?->id,
                'page_number' => 1,
                'type' => 'credit',
                'description' => 'Lançamento inicial de demonstração',
                'amount' => 1500,
                'movement_date' => now()->startOfMonth(),
            ],
            [
                'book_id' => $book->id,
                'category_id' => $food?->id,
                'page_number' => 1,
                'type' => 'debit',
                'description' => 'Compras do mês (demonstração)',
                'amount' => 320.50,
                'movement_date' => now()->startOfMonth()->addDays(3),
            ],
        ]);
    }
}
