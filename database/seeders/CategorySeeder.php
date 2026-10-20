<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Categorías de ejemplo para la carta.
     */
    public function run(): void
    {
        $categories = [
            'Carne',
            'Pollo',
            'Clásicas',
            'Vegetarianas',
            'Dulces',
        ];

        foreach ($categories as $name) {
            Category::firstOrCreate(['name' => $name]);
        }
    }
}
