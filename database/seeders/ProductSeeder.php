<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Productos de ejemplo con sus categorías, ingredientes por defecto y extras
     * (deben existir: CategorySeeder e IngredientSeeder).
     * En 'ingredients', true = se puede quitar al vender y false = obligatorio.
     * En 'extras', un precio null cobra el precio general del ingrediente.
     * Sin imagen: la carta muestra el ícono de empanada hasta que se suba una.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'Empanada de carne', 'price' => 2.50, 'categories' => ['Carne', 'Clásicas'],
                'ingredients' => ['Masa' => false, 'Carne picada' => false, 'Cebolla' => true, 'Huevo' => true, 'Aceitunas' => true],
                'extras' => ['Huevo' => null, 'Aceitunas' => null, 'Salsa picante' => null],
            ],
            [
                'name' => 'Carne picante', 'price' => 2.75, 'categories' => ['Carne'],
                'ingredients' => ['Masa' => false, 'Carne picada' => false, 'Cebolla' => true, 'Salsa picante' => true],
                'extras' => ['Queso' => null, 'Huevo' => null],
            ],
            [
                'name' => 'Carne cortada a cuchillo', 'price' => 3.10, 'categories' => ['Carne'],
                'ingredients' => ['Masa' => false, 'Carne picada' => false, 'Cebolla' => true, 'Morrón' => true],
                'extras' => ['Huevo' => null, 'Aceitunas' => null],
            ],
            [
                'name' => 'Empanada de pollo', 'price' => 2.50, 'categories' => ['Pollo', 'Clásicas'],
                'ingredients' => ['Masa' => false, 'Pollo' => false, 'Cebolla' => true, 'Morrón' => true],
                'extras' => ['Queso' => null, 'Salsa picante' => null],
            ],
            [
                'name' => 'Pollo al verdeo', 'price' => 2.80, 'categories' => ['Pollo'],
                'ingredients' => ['Masa' => false, 'Pollo' => false, 'Cebolla' => true],
                'extras' => ['Queso' => null, 'Panceta' => null],
            ],
            [
                'name' => 'Jamón y queso', 'price' => 2.25, 'categories' => ['Clásicas'],
                'ingredients' => ['Masa' => false, 'Jamón' => false, 'Queso' => false],
                'extras' => ['Queso' => null, 'Cebolla caramelizada' => null],
            ],
            [
                'name' => 'Humita', 'price' => 2.50, 'categories' => ['Vegetarianas'],
                'ingredients' => ['Masa' => false, 'Choclo' => false, 'Cebolla' => true],
                'extras' => ['Queso' => null],
            ],
            // El queso extra cuesta más en la caprese: precio propio en este producto.
            [
                'name' => 'Caprese', 'price' => 2.50, 'categories' => ['Vegetarianas'],
                'ingredients' => ['Masa' => false, 'Queso' => false],
                'extras' => ['Queso' => 0.80, 'Aceitunas' => null],
            ],
            [
                'name' => 'Espinaca y ricota', 'price' => 2.40, 'categories' => ['Vegetarianas'],
                'ingredients' => ['Masa' => false, 'Cebolla' => true],
                'extras' => ['Queso' => null, 'Cebolla caramelizada' => null],
            ],
            ['name' => 'Roquefort y nuez', 'price' => 2.90, 'categories' => ['Clásicas'], 'available' => false, 'ingredients' => ['Masa' => false]],
            ['name' => 'Dulce de leche', 'price' => 2.00, 'categories' => ['Dulces'], 'ingredients' => ['Masa' => false]],
            ['name' => 'Membrillo y queso', 'price' => 2.15, 'categories' => ['Dulces'], 'ingredients' => ['Masa' => false, 'Queso' => false]],
        ];

        $categories = Category::pluck('id', 'name');
        $ingredients = Ingredient::pluck('id', 'name');

        foreach ($products as $data) {
            $product = Product::firstOrCreate(
                ['name' => $data['name']],
                ['price' => $data['price'], 'available' => $data['available'] ?? true],
            );

            $product->categories()->sync($categories->only($data['categories'])->values());

            $product->ingredients()->sync(
                collect($data['ingredients'] ?? [])
                    ->mapWithKeys(fn ($removable, $name) => [$ingredients[$name] => ['is_removable' => $removable]])
                    ->all()
            );

            $product->extras()->sync(
                collect($data['extras'] ?? [])
                    ->mapWithKeys(fn ($price, $name) => [$ingredients[$name] => ['price' => $price]])
                    ->all()
            );
        }
    }
}
