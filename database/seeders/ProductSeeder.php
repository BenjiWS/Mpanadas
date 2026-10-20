<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Productos de ejemplo, cada uno con sus categorías (deben existir: CategorySeeder).
     * Sin imagen: la carta muestra el ícono de empanada hasta que se suba una.
     */
    public function run(): void
    {
        $products = [
            ['name' => 'Empanada de carne', 'price' => 2.50, 'categories' => ['Carne', 'Clásicas']],
            ['name' => 'Carne picante', 'price' => 2.75, 'categories' => ['Carne']],
            ['name' => 'Carne cortada a cuchillo', 'price' => 3.10, 'categories' => ['Carne']],
            ['name' => 'Empanada de pollo', 'price' => 2.50, 'categories' => ['Pollo', 'Clásicas']],
            ['name' => 'Pollo al verdeo', 'price' => 2.80, 'categories' => ['Pollo']],
            ['name' => 'Jamón y queso', 'price' => 2.25, 'categories' => ['Clásicas']],
            ['name' => 'Humita', 'price' => 2.50, 'categories' => ['Vegetarianas']],
            ['name' => 'Caprese', 'price' => 2.50, 'categories' => ['Vegetarianas']],
            ['name' => 'Espinaca y ricota', 'price' => 2.40, 'categories' => ['Vegetarianas']],
            ['name' => 'Roquefort y nuez', 'price' => 2.90, 'categories' => ['Clásicas'], 'available' => false],
            ['name' => 'Dulce de leche', 'price' => 2.00, 'categories' => ['Dulces']],
            ['name' => 'Membrillo y queso', 'price' => 2.15, 'categories' => ['Dulces']],
        ];

        $categories = Category::pluck('id', 'name');

        foreach ($products as $data) {
            $product = Product::firstOrCreate(
                ['name' => $data['name']],
                ['price' => $data['price'], 'available' => $data['available'] ?? true],
            );

            $product->categories()->sync($categories->only($data['categories'])->values());
        }
    }
}
