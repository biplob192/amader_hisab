<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Food and Grocery', 'color' => '#dbdb7a'],
            ['name' => 'Health and Medicine', 'color' => '#c67d76'],
            ['name' => 'Education', 'color' => '#a18ac9'],
            ['name' => 'House Rent', 'color' => '#927eb5'],
            ['name' => 'Water', 'color' => '#7b91b1'],
            ['name' => 'Electricity', 'color' => '#cc9645'],
            ['name' => 'Transport', 'color' => '#d3a45f'],
            ['name' => 'Internet', 'color' => '#3f4d61'],
            ['name' => 'Mobile', 'color' => '#6ba7a0'],
            ['name' => 'Shopping', 'color' => '#b7608e'],
            ['name' => 'Entertainment', 'color' => '#c47ca2'],
            ['name' => 'Family', 'color' => '#7cc4bf'],
            ['name' => 'Farming', 'color' => '#238340'],
            ['name' => 'Donation', 'color' => '#c67d76'],
            ['name' => 'Development', 'color' => '#927eb5'],
            ['name' => 'Other', 'color' => '#9b9f8d'],
        ];

        foreach ($categories as $category) {
            ExpenseCategory::firstOrCreate(
                ['name' => $category['name']],
                ['color' => $category['color']],
            );
        }
    }
}
