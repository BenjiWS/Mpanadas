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
            'Carne' => 'Picada, picante o cortada a cuchillo',
            'Pollo' => 'Para los que prefieren algo más liviano',
            'Clásicas' => 'Las de siempre, las que más se piden',
            'Vegetarianas' => 'Sin carne, con todo el sabor',
            'Dulces' => 'Para cerrar con algo rico',
        ];

        foreach ($categories as $name => $description) {
            Category::firstOrCreate(['name' => $name], ['description' => $description]);
        }
    }
}
