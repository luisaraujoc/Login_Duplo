<?php

namespace Database\Seeders;

use App\Enums\CashFlowType;
use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Default categories shared by every account (categories are global,
     * matching the legacy lc_cat table which has no per-account scoping).
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Salário', 'type' => CashFlowType::Credit],
            ['name' => 'Vendas', 'type' => CashFlowType::Credit],
            ['name' => 'Serviços Prestados', 'type' => CashFlowType::Credit],
            ['name' => 'Rendimentos', 'type' => CashFlowType::Credit],
            ['name' => 'Outras Receitas', 'type' => CashFlowType::Credit],
            ['name' => 'Aluguel', 'type' => CashFlowType::Debit],
            ['name' => 'Água/Luz/Internet', 'type' => CashFlowType::Debit],
            ['name' => 'Alimentação', 'type' => CashFlowType::Debit],
            ['name' => 'Transporte', 'type' => CashFlowType::Debit],
            ['name' => 'Saúde', 'type' => CashFlowType::Debit],
            ['name' => 'Impostos e Taxas', 'type' => CashFlowType::Debit],
            ['name' => 'Fornecedores', 'type' => CashFlowType::Debit],
            ['name' => 'Outras Despesas', 'type' => CashFlowType::Debit],
        ];

        foreach ($categories as $category) {
            Category::query()->firstOrCreate(
                ['name' => $category['name']],
                ['type' => $category['type']],
            );
        }
    }
}
