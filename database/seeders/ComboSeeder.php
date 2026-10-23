<?php

namespace Database\Seeders;

use App\Models\Combo;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ComboSeeder extends Seeder
{
    /**
     * Combos de ejemplo armados con los productos del ProductSeeder (debe existir).
     * En 'products' va el nombre del producto y cuántas unidades trae.
     * El precio queda por debajo de comprar los productos por separado.
     * Sin imagen: la carta muestra el ícono de combo hasta que se suba una.
     */
    public function run(): void
    {
        $combos = [
            [
                'name' => 'Combo clásico', 'price' => 12.90, 'description' => 'Las de siempre: carne y jamón y queso',
                'products' => ['Empanada de carne' => 3, 'Jamón y queso' => 3],
            ],
            [
                'name' => 'Combo para dos', 'price' => 13.50, 'description' => null,
                'products' => ['Empanada de carne' => 2, 'Empanada de pollo' => 2, 'Humita' => 2],
            ],
            [
                'name' => 'Combo familiar', 'price' => 26.00, 'description' => 'Una docena surtida para compartir',
                'products' => ['Empanada de carne' => 4, 'Empanada de pollo' => 4, 'Jamón y queso' => 2, 'Humita' => 2],
            ],
            [
                'name' => 'Combo veggie', 'price' => 13.20, 'description' => 'Sin carne, con todo el sabor',
                'products' => ['Humita' => 2, 'Caprese' => 2, 'Espinaca y ricota' => 2],
            ],
            [
                'name' => 'Combo picante', 'price' => 15.90, 'description' => null,
                'products' => ['Carne picante' => 3, 'Carne cortada a cuchillo' => 3],
            ],
            [
                'name' => 'Merienda dulce', 'price' => 10.90, 'description' => 'Para el café de la tarde',
                'products' => ['Dulce de leche' => 3, 'Membrillo y queso' => 3],
            ],
            // Inactivo: el roquefort está agotado.
            [
                'name' => 'Combo gourmet', 'price' => 10.80, 'description' => null, 'is_active' => false,
                'products' => ['Roquefort y nuez' => 2, 'Carne cortada a cuchillo' => 2],
            ],
        ];

        $products = Product::pluck('id', 'name');

        foreach ($combos as $data) {
            $combo = Combo::firstOrCreate(
                ['name' => $data['name']],
                ['price' => $data['price'], 'description' => $data['description'], 'is_active' => $data['is_active'] ?? true],
            );

            $combo->products()->sync(
                collect($data['products'])
                    ->mapWithKeys(fn ($quantity, $name) => [$products[$name] => ['quantity' => $quantity]])
                    ->all()
            );
        }
    }
}
