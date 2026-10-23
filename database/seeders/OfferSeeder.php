<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Combo;
use App\Models\Offer;
use App\Models\Product;
use Illuminate\Database\Seeder;

class OfferSeeder extends Seeder
{
    /**
     * Ofertas de ejemplo (deben existir: CategorySeeder, ProductSeeder y ComboSeeder).
     * Las fechas son relativas a hoy para que siempre haya vigentes, una finalizada y una programada.
     * 'weekdays' son días ISO (1 = lunes); null = todos los días.
     */
    public function run(): void
    {
        $offers = [
            [
                'name' => 'Tardes con sabor', 'description' => 'Empanadas seleccionadas',
                'discount_type' => 'two_for_one', 'discount_value' => 0,
                'starts_at' => now()->startOfMonth(), 'ends_at' => now()->addMonths(3)->endOfMonth(),
                'weekdays' => '1,2,3,4,5', 'start_time' => '15:00:00', 'end_time' => '17:00:00',
                'products' => ['Empanada de carne', 'Empanada de pollo', 'Jamón y queso'],
            ],
            [
                'name' => 'Tu combo favorito', 'description' => 'Descuento en combos',
                'discount_type' => 'percentage', 'discount_value' => 10,
                'starts_at' => now()->startOfMonth(), 'ends_at' => now()->addMonths(2)->endOfMonth(),
                'combos' => ['Combo clásico', 'Combo para dos', 'Combo familiar'],
            ],
            [
                'name' => 'Para compartir', 'description' => 'En las clásicas de siempre',
                'discount_type' => 'percentage', 'discount_value' => 15,
                'starts_at' => now()->startOfMonth(), 'ends_at' => now()->addMonth()->endOfMonth(),
                'weekdays' => '6,7',
                'categories' => ['Clásicas'],
            ],
            [
                'name' => 'Semana dulce', 'description' => null,
                'discount_type' => 'fixed', 'discount_value' => 0.50,
                'starts_at' => now()->subMonths(2)->startOfMonth(), 'ends_at' => now()->subMonth()->endOfMonth(),
                'categories' => ['Dulces'],
            ],
            [
                'name' => 'Lanzamiento veggie', 'description' => 'Para probar las nuevas',
                'discount_type' => 'percentage', 'discount_value' => 20,
                'starts_at' => now()->addMonth()->startOfMonth(), 'ends_at' => now()->addMonths(2)->endOfMonth(),
                'categories' => ['Vegetarianas'], 'combos' => ['Combo veggie'],
            ],
        ];

        $categories = Category::pluck('id', 'name');
        $products = Product::pluck('id', 'name');
        $combos = Combo::pluck('id', 'name');

        foreach ($offers as $data) {
            $offer = Offer::firstOrCreate(
                ['name' => $data['name']],
                [
                    'description' => $data['description'],
                    'discount_type' => $data['discount_type'],
                    'discount_value' => $data['discount_value'],
                    'starts_at' => $data['starts_at']->startOfDay(),
                    'ends_at' => $data['ends_at']->endOfDay(),
                    'weekdays' => $data['weekdays'] ?? null,
                    'start_time' => $data['start_time'] ?? null,
                    'end_time' => $data['end_time'] ?? null,
                ],
            );

            $offer->categories()->sync($categories->only($data['categories'] ?? [])->values());
            $offer->products()->sync($products->only($data['products'] ?? [])->values());
            $offer->combos()->sync($combos->only($data['combos'] ?? [])->values());
        }
    }
}
