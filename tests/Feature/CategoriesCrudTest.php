<?php

namespace Tests\Feature;

use App\Livewire\Categories\CategoriesTable;
use App\Livewire\Categories\CrudCategory;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CategoriesCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, PermissionSeeder::class]);
        $this->admin = User::factory()->create()->assignRole('Administrador');
    }

    public function test_pages_require_permission(): void
    {
        $cajero = User::factory()->create()->assignRole('Cajero');
        $category = Category::create(['name' => 'Carne']);

        $this->actingAs($cajero)->get('/categories')->assertForbidden();
        $this->actingAs($cajero)->get('/categories/create')->assertForbidden();

        $this->actingAs($this->admin)->get('/categories')->assertOk()->assertSee('Carne');
        $this->actingAs($this->admin)->get('/categories/create')->assertOk()->assertSee('Nueva categoría');
        $this->actingAs($this->admin)->get("/categories/{$category->id}/edit")->assertOk()->assertSee('Edición de Carne');
    }

    public function test_store_creates_category(): void
    {
        Livewire::actingAs($this->admin)->test(CrudCategory::class)
            ->assertSeeHtml('wire:submit="store"')
            ->set('name', 'Vegetarianas')
            ->call('store')
            ->assertHasNoErrors()
            ->assertDispatched('swalmodal');

        $this->assertDatabaseHas('categories', ['name' => 'Vegetarianas']);
    }

    public function test_store_validates_name(): void
    {
        Category::create(['name' => 'Carne']);

        Livewire::actingAs($this->admin)->test(CrudCategory::class)
            ->call('store')
            ->assertHasErrors(['name' => 'required'])
            ->set('name', 'Carne')
            ->call('store')
            ->assertHasErrors(['name' => 'unique']);
    }

    public function test_update_renames_category(): void
    {
        $category = Category::create(['name' => 'Polo']);

        Livewire::actingAs($this->admin)->test(CrudCategory::class, ['category' => $category])
            ->assertSeeHtml('wire:submit="update"')
            ->assertSet('name', 'Polo')
            ->set('name', 'Pollo')
            ->call('update')
            ->assertHasNoErrors();

        $this->assertSame('Pollo', $category->fresh()->name);
    }

    public function test_destroy_asks_confirmation_then_deletes(): void
    {
        $category = Category::create(['name' => 'Temporal']);

        Livewire::actingAs($this->admin)->test(CategoriesTable::class)
            ->call('destroyConfirm', $category->id)
            ->assertDispatched('swalConfirm')
            ->call('destroy', $category->id)
            ->assertDispatched('swalmodal');

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_cannot_delete_category_with_products(): void
    {
        $category = Category::create(['name' => 'Carne']);
        $category->products()->attach(Product::create(['name' => 'Empanada de carne', 'price' => 2.5]));

        Livewire::actingAs($this->admin)->test(CategoriesTable::class)
            ->call('destroyConfirm', $category->id)
            ->assertNotDispatched('swalConfirm')
            ->assertDispatched('swalmodal')
            ->call('destroy', $category->id);

        $this->assertNotNull($category->fresh());
    }

    public function test_search_filters_categories(): void
    {
        Category::create(['name' => 'Carne']);
        Category::create(['name' => 'Dulces']);

        Livewire::actingAs($this->admin)->test(CategoriesTable::class)
            ->set('search', 'dul')
            ->assertSee('Dulces')
            ->assertDontSee('Carne');
    }
}
