<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use Illuminate\Database\Seeder;

class IngredientSeeder extends Seeder
{
    /**
     * Ingredientes de ejemplo con su precio general como extra.
     * Los de precio 0 se usan sobre todo como ingredientes por defecto.
     */
    public function run(): void
    {
        $ingredients = [
            ['name' => 'Masa', 'extra_price' => 0],
            ['name' => 'Carne picada', 'extra_price' => 0.90],
            ['name' => 'Pollo', 'extra_price' => 0.80],
            ['name' => 'Cebolla', 'extra_price' => 0.20],
            ['name' => 'Morrón', 'extra_price' => 0.25],
            ['name' => 'Choclo', 'extra_price' => 0.30],
            ['name' => 'Jamón', 'extra_price' => 0.60],
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
