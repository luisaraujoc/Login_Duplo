<?php

namespace Database\Seeders;

use App\Models\FuelProduct;
use Illuminate\Database\Seeder;

class FuelProductSeeder extends Seeder
{
    /**
     * Default fuel types, global catalog (mirrors legacy "produtos").
     */
    public function run(): void
    {
        $products = [
            ['code' => '1', 'name' => 'Gasolina Comum', 'unit' => 'L'],
            ['code' => '2', 'name' => 'Gasolina Aditivada', 'unit' => 'L'],
            ['code' => '3', 'name' => 'Etanol', 'unit' => 'L'],
            ['code' => '4', 'name' => 'Diesel S10', 'unit' => 'L'],
            ['code' => '5', 'name' => 'GNV', 'unit' => 'M3'],
        ];

        foreach ($products as $product) {
            FuelProduct::query()->firstOrCreate(
                ['code' => $product['code']],
                ['name' => $product['name'], 'unit' => $product['unit']],
            );
        }
    }
}
