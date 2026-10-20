<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Database\Seeders\CategorySeeder;
use Database\Seeders\DatabaseSeeder;
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
    }

    public function test_catalog_seeders_can_run_twice_without_duplicates(): void
    {
        $this->seed([CategorySeeder::class, ProductSeeder::class]);
        $this->seed([CategorySeeder::class, ProductSeeder::class]);

        $this->assertSame(5, Category::count());
        $this->assertSame(12, Product::count());
    }
}
