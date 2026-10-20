<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use Illuminate\Database\Seeder;

class IngredientSeeder extends Seeder
{
    /**
     * Ingredientes de ejemplo con su precio general como extra.
     */
    public function run(): void
    {
        $ingredients = [
            ['name' => 'Queso', 'extra_price' => 0.50],
            ['name' => 'Huevo', 'extra_price' => 0.40],
            ['name' => 'Aceitunas', 'extra_price' => 0.30],
            ['name' => 'Cebolla caramelizada', 'extra_price' => 0.45],
            ['name' => 'Panceta', 'extra_price' => 0.70],
            ['name' => 'Salsa picante', 'extra_price' => 0.20],
        ];

        foreach ($ingredients as $data) {
            Ingredient::firstOrCreate(['name' => $data['name']], ['extra_price' => $data['extra_price']]);
        }
    }
}
