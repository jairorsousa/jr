<?php

namespace Database\Seeders;

use App\Enums\TransactionType;
use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $expenses = [
            ['name' => 'Moradia', 'icon' => 'home', 'color' => '#5C6BC0'],
            ['name' => 'Alimentacao', 'icon' => 'restaurant', 'color' => '#EF5350'],
            ['name' => 'Transporte', 'icon' => 'directions_car', 'color' => '#42A5F5'],
            ['name' => 'Saude', 'icon' => 'local_hospital', 'color' => '#66BB6A'],
            ['name' => 'Educacao', 'icon' => 'school', 'color' => '#AB47BC'],
            ['name' => 'Lazer', 'icon' => 'sports_esports', 'color' => '#FFA726'],
            ['name' => 'Assinaturas', 'icon' => 'subscriptions', 'color' => '#26C6DA'],
            ['name' => 'Vestuario', 'icon' => 'checkroom', 'color' => '#EC407A'],
            ['name' => 'Pets', 'icon' => 'pets', 'color' => '#8D6E63'],
            ['name' => 'Impostos', 'icon' => 'receipt_long', 'color' => '#78909C'],
            ['name' => 'Outros', 'icon' => 'more_horiz', 'color' => '#BDBDBD'],
        ];

        $incomes = [
            ['name' => 'Salario', 'icon' => 'payments', 'color' => '#15a96f'],
            ['name' => 'Freelance', 'icon' => 'work', 'color' => '#26A69A'],
            ['name' => 'Investimentos', 'icon' => 'trending_up', 'color' => '#FFA000'],
            ['name' => 'Cashback', 'icon' => 'currency_exchange', 'color' => '#7E57C2'],
            ['name' => 'Outros', 'icon' => 'more_horiz', 'color' => '#BDBDBD'],
        ];

        foreach ($expenses as $expense) {
            Category::firstOrCreate(
                ['name' => $expense['name'], 'type' => TransactionType::Expense],
                ['icon' => $expense['icon'], 'color' => $expense['color']],
            );
        }

        foreach ($incomes as $income) {
            Category::firstOrCreate(
                ['name' => $income['name'], 'type' => TransactionType::Income],
                ['icon' => $income['icon'], 'color' => $income['color']],
            );
        }
    }
}
