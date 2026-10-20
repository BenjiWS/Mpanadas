<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Product;
use Database\Seeders\CategorySeeder;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\IngredientSeeder;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogSeedersTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_categories_and_products(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(5, Category::count());
        $this->assertSame(12, Product::count());

        // Todos los productos quedan con al menos una categoría.
        $this->assertSame(0, Product::doesntHave('categories')->count());
        $this->assertEqualsCanonicalizing(
            ['Carne', 'Clásicas'],
            Product::where('name', 'Empanada de carne')->first()->categories->pluck('name')->all(),
        );
        $this->assertFalse(Product::where('name', 'Roquefort y nuez')->first()->available);

        // Extras: el queso de la caprese tiene precio propio; el de la humita usa el general.
        $this->assertSame(6, Ingredient::count());
        $queso = fn (string $product) => Product::where('name', $product)->first()->extras->firstWhere('name', 'Queso');
        $this->assertSame('0.80', $queso('Caprese')->priceAsExtra());
        $this->assertSame('0.50', $queso('Humita')->priceAsExtra());
    }

    public function test_catalog_seeders_can_run_twice_without_duplicates(): void
    {
        $seeders = [CategorySeeder::class, IngredientSeeder::class, ProductSeeder::class];
        $this->seed($seeders);
        $this->seed($seeders);

        $this->assertSame(5, Category::count());
        $this->assertSame(6, Ingredient::count());
        $this->assertSame(12, Product::count());
    }
}
