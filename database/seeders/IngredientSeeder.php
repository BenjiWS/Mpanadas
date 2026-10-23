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
            ['name' => 'Masa', 'extra_price' => 0, 'description' => 'Hojaldrada, hecha en casa'],
            ['name' => 'Carne picada', 'extra_price' => 0.90, 'description' => 'Carne vacuna con comino y pimentón'],
            ['name' => 'Pollo', 'extra_price' => 0.80, 'description' => 'Pechuga desmenuzada'],
            ['name' => 'Cebolla', 'extra_price' => 0.20, 'description' => 'Rehogada'],
            ['name' => 'Morrón', 'extra_price' => 0.25, 'description' => 'Rojo, asado'],
            ['name' => 'Choclo', 'extra_price' => 0.30, 'description' => 'Cremoso, para la humita'],
            ['name' => 'Jamón', 'extra_price' => 0.60, 'description' => 'Cocido, en cubos'],
            ['name' => 'Queso', 'extra_price' => 0.50, 'description' => 'Mozzarella'],
            ['name' => 'Huevo', 'extra_price' => 0.40, 'description' => 'Duro, picado'],
            ['name' => 'Aceitunas', 'extra_price' => 0.30, 'description' => 'Verdes, en rodajas'],
            ['name' => 'Cebolla caramelizada', 'extra_price' => 0.45, 'description' => 'Cocida lento con azúcar'],
            ['name' => 'Panceta', 'extra_price' => 0.70, 'description' => 'Ahumada, crocante'],
            ['name' => 'Salsa picante', 'extra_price' => 0.20, 'description' => 'De ají molido'],
        ];

        foreach ($ingredients as $data) {
            Ingredient::firstOrCreate(['name' => $data['name']], ['extra_price' => $data['extra_price'], 'description' => $data['description']]);
        }
    }
}
