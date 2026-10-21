<?php

namespace Tests\Feature;

use App\Livewire\Products\CrudProduct;
use App\Livewire\Products\ProductsTable;
use App\Models\Category;
use App\Models\Combo;
use App\Models\Ingredient;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ProductsCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, PermissionSeeder::class]);
        $this->admin = User::factory()->create()->assignRole('Administrador');
        $this->category = Category::create(['name' => 'Carne']);
        Storage::fake('public');
    }

    private function product(string $name = 'Empanada de carne', array $attributes = []): Product
    {
        $product = Product::create(['name' => $name, 'price' => 2.5, ...$attributes]);
        $product->categories()->attach($this->category);

        return $product;
    }

    public function test_pages_require_permission(): void
    {
        $cajero = User::factory()->create()->assignRole('Cajero');
        $product = $this->product();

        $this->actingAs($cajero)->get('/products')->assertForbidden();
        $this->actingAs($cajero)->get('/products/create')->assertForbidden();

        $this->actingAs($this->admin)->get('/products')->assertOk()->assertSee('Empanada de carne')->assertSee('$ 2,50');
        $this->actingAs($this->admin)->get('/products/create')->assertOk()->assertSee('Nuevo producto');
        $this->actingAs($this->admin)->get("/products/{$product->id}/edit")->assertOk()->assertSee('Edición de Empanada de carne');
    }

    public function test_store_creates_product_with_categories_and_image(): void
    {
        $dulces = Category::create(['name' => 'Dulces']);

        Livewire::actingAs($this->admin)->test(CrudProduct::class)
            ->assertSeeHtml('wire:submit="store"')
            ->set('name', 'Empanada de humita')
            ->set('price', '2.80')
            ->set('selectedCategories', [(string) $this->category->id, (string) $dulces->id])
            ->set('image', UploadedFile::fake()->image('humita.jpg'))
            ->call('store')
            ->assertHasNoErrors()
            ->assertDispatched('swalmodal');

        $product = Product::where('name', 'Empanada de humita')->firstOrFail();
        $this->assertSame('2.80', $product->price);
        $this->assertTrue($product->available);
        $this->assertCount(2, $product->categories);
        Storage::disk('public')->assertExists($product->image);
    }

    public function test_store_validates_fields(): void
    {
        $this->product();

        Livewire::actingAs($this->admin)->test(CrudProduct::class)
            ->set('name', 'Empanada de carne')
            ->set('price', '-1')
            ->call('store')
            ->assertHasErrors(['name' => 'unique', 'price' => 'min', 'selectedCategories' => 'required']);

        Livewire::actingAs($this->admin)->test(CrudProduct::class)
            ->set('image', UploadedFile::fake()->create('menu.pdf', 100, 'application/pdf'))
            ->assertHasErrors(['image' => 'image']);
    }

    public function test_update_replaces_image_and_deletes_the_old_one(): void
    {
        Storage::disk('public')->put('products/vieja.jpg', 'x');
        $product = $this->product(attributes: ['image' => 'products/vieja.jpg']);

        Livewire::actingAs($this->admin)->test(CrudProduct::class, ['product' => $product])
            ->assertSeeHtml('wire:submit="update"')
            ->assertSet('name', 'Empanada de carne')
            ->set('price', '3.10')
            ->set('image', UploadedFile::fake()->image('nueva.jpg'))
            ->call('update')
            ->assertHasNoErrors();

        $product->refresh();
        $this->assertSame('3.10', $product->price);
        $this->assertNotSame('products/vieja.jpg', $product->image);
        Storage::disk('public')->assertMissing('products/vieja.jpg');
        Storage::disk('public')->assertExists($product->image);
    }

    public function test_update_can_remove_the_image(): void
    {
        Storage::disk('public')->put('products/vieja.jpg', 'x');
        $product = $this->product(attributes: ['image' => 'products/vieja.jpg']);

        Livewire::actingAs($this->admin)->test(CrudProduct::class, ['product' => $product])
            ->call('clearImage')
            ->call('update')
            ->assertHasNoErrors();

        $this->assertNull($product->fresh()->image);
        Storage::disk('public')->assertMissing('products/vieja.jpg');
    }

    public function test_extras_are_saved_with_optional_price(): void
    {
        $queso = Ingredient::create(['name' => 'Queso', 'extra_price' => 0.5]);
        $huevo = Ingredient::create(['name' => 'Huevo', 'extra_price' => 0.4]);
        $product = $this->product();

        Livewire::actingAs($this->admin)->test(CrudProduct::class, ['product' => $product])
            ->set('newExtra', $queso->id)
            ->call('addExtra')
            ->set('newExtra', $huevo->id)
            ->call('addExtra')
            ->set('extras.0.price', '0.80')
            ->call('update')
            ->assertHasNoErrors();

        $extras = $product->fresh()->extras;
        $this->assertCount(2, $extras);
        $this->assertSame('0.80', $extras->firstWhere('name', 'Queso')->priceAsExtra());
        // Sin precio propio se cobra el general.
        $this->assertNull($extras->firstWhere('name', 'Huevo')->pivot->price);
        $this->assertSame('0.40', $extras->firstWhere('name', 'Huevo')->priceAsExtra());

        // Al editar se cargan los extras (ordenados por nombre) y se pueden quitar.
        Livewire::actingAs($this->admin)->test(CrudProduct::class, ['product' => $product->fresh()])
            ->assertSet('extras.0.id', $huevo->id)
            ->assertSet('extras.1.id', $queso->id)
            ->call('removeExtra', 1)
            ->call('update');

        $this->assertSame(['Huevo'], $product->fresh()->extras->pluck('name')->all());
    }

    public function test_extra_price_must_be_valid(): void
    {
        $queso = Ingredient::create(['name' => 'Queso', 'extra_price' => 0.5]);

        Livewire::actingAs($this->admin)->test(CrudProduct::class, ['product' => $this->product()])
            ->set('newExtra', $queso->id)
            ->call('addExtra')
            ->set('extras.0.price', '-1')
            ->call('update')
            ->assertHasErrors('extras.0.price');
    }

    public function test_default_ingredients_are_saved_as_removable_or_required(): void
    {
        $masa = Ingredient::create(['name' => 'Masa', 'extra_price' => 0]);
        $cebolla = Ingredient::create(['name' => 'Cebolla', 'extra_price' => 0.2]);
        $product = $this->product();

        Livewire::actingAs($this->admin)->test(CrudProduct::class, ['product' => $product])
            ->set('newDefaultIngredient', $masa->id)
            ->call('addDefaultIngredient')
            ->set('newDefaultIngredient', $cebolla->id)
            ->call('addDefaultIngredient')
            // Se agrega como quitable; la masa se marca obligatoria.
            ->assertSet('defaultIngredients.0.removable', true)
            ->set('defaultIngredients.0.removable', '0')
            ->call('update')
            ->assertHasNoErrors();

        $ingredients = $product->fresh()->ingredients;
        $this->assertFalse((bool) $ingredients->firstWhere('name', 'Masa')->pivot->is_removable);
        $this->assertTrue((bool) $ingredients->firstWhere('name', 'Cebolla')->pivot->is_removable);

        Livewire::actingAs($this->admin)->test(CrudProduct::class, ['product' => $product->fresh()])
            ->assertSet('defaultIngredients.0.id', $cebolla->id)
            ->call('removeDefaultIngredient', 0)
            ->call('update');

        $this->assertSame(['Masa'], $product->fresh()->ingredients->pluck('name')->all());
    }

    public function test_add_menu_adds_the_chosen_ingredient(): void
    {
        $cebolla = Ingredient::create(['name' => 'Cebolla', 'extra_price' => 0.2]);

        Livewire::actingAs($this->admin)->test(CrudProduct::class)
            ->assertSeeHtml('wire:click="addDefaultIngredient('.$cebolla->id.')"')
            ->call('addDefaultIngredient', $cebolla->id)
            ->assertSet('defaultIngredients.0.id', $cebolla->id)
            // Una vez agregado ya no aparece en el menú de ingredientes por defecto.
            ->assertDontSeeHtml('wire:click="addDefaultIngredient('.$cebolla->id.')"')
            ->call('addExtra', $cebolla->id)
            ->assertSet('extras.0.id', $cebolla->id);
    }

    public function test_cannot_add_the_same_ingredient_twice_or_an_inactive_one(): void
    {
        $queso = Ingredient::create(['name' => 'Queso', 'extra_price' => 0.5]);
        $panceta = Ingredient::create(['name' => 'Panceta', 'extra_price' => 0.7, 'is_active' => false]);

        Livewire::actingAs($this->admin)->test(CrudProduct::class)
            ->assertDontSee('Panceta')
            ->set('newExtra', $queso->id)
            ->call('addExtra')
            ->set('newExtra', $queso->id)
            ->call('addExtra')
            ->set('newExtra', $panceta->id)
            ->call('addExtra')
            ->assertCount('extras', 1);
    }

    public function test_toggle_availability_and_filters(): void
    {
        $product = $this->product();
        $this->product('Empanada de pollo');

        Livewire::actingAs($this->admin)->test(ProductsTable::class)
            ->call('toggleAvailability', $product->id)
            ->call('setFilter', 'inactivos')
            ->assertSee('Empanada de carne')
            ->assertDontSee('Empanada de pollo')
            ->call('setFilter', 'activos')
            ->assertSee('Empanada de pollo')
            ->assertDontSee('Empanada de carne');

        $this->assertFalse($product->fresh()->available);
    }

    public function test_search_by_name_or_category(): void
    {
        $this->product();
        $dulce = Product::create(['name' => 'Empanada de dulce de leche', 'price' => 2]);
        $dulce->categories()->attach(Category::create(['name' => 'Postres']));

        Livewire::actingAs($this->admin)->test(ProductsTable::class)
            ->set('search', 'postres')
            ->assertSee('Empanada de dulce de leche')
            ->assertDontSee('Empanada de carne');
    }

    public function test_destroy_asks_confirmation_then_soft_deletes(): void
    {
        $product = $this->product();

        Livewire::actingAs($this->admin)->test(ProductsTable::class)
            ->call('destroyConfirm', $product->id)
            ->assertDispatched('swalConfirm')
            ->call('destroy', $product->id)
            ->assertDispatched('swalmodal');

        $this->assertSoftDeleted($product);
        $this->assertCount(0, $this->category->fresh()->products);
    }

    public function test_cannot_delete_product_in_a_combo(): void
    {
        $product = $this->product();
        $combo = Combo::forceCreate(['name' => 'Combo clásico', 'price' => 5]);
        $combo->products()->attach($product);

        Livewire::actingAs($this->admin)->test(ProductsTable::class)
            ->call('destroyConfirm', $product->id)
            ->assertNotDispatched('swalConfirm')
            ->call('destroy', $product->id);

        $this->assertNotSoftDeleted($product);
    }
}
