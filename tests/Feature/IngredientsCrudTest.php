<?php

namespace Tests\Feature;

use App\Livewire\Ingredients\CrudIngredient;
use App\Livewire\Ingredients\IngredientsTable;
use App\Models\Ingredient;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class IngredientsCrudTest extends TestCase
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
        $ingredient = Ingredient::create(['name' => 'Queso', 'extra_price' => 0.5]);

        $this->actingAs($cajero)->get('/ingredients')->assertForbidden();
        $this->actingAs($cajero)->get('/ingredients/create')->assertForbidden();

        $this->actingAs($this->admin)->get('/ingredients')->assertOk()->assertSee('Queso')->assertSee('$ 0,50');
        $this->actingAs($this->admin)->get('/ingredients/create')->assertOk()->assertSee('Nuevo ingrediente');
        $this->actingAs($this->admin)->get("/ingredients/{$ingredient->id}/edit")->assertOk()->assertSee('Edición de Queso');
    }

    public function test_store_and_update_ingredient(): void
    {
        Livewire::actingAs($this->admin)->test(CrudIngredient::class)
            ->assertSeeHtml('wire:submit="store"')
            ->set('name', 'Huevo')
            ->set('extra_price', '0.40')
            ->set('description', 'Huevo duro picado')
            ->call('store')
            ->assertHasNoErrors()
            ->assertDispatched('swalmodal');

        $ingredient = Ingredient::where('name', 'Huevo')->firstOrFail();
        $this->assertSame('0.40', $ingredient->extra_price);

        Livewire::actingAs($this->admin)->test(CrudIngredient::class, ['ingredient' => $ingredient])
            ->assertSeeHtml('wire:submit="update"')
            ->set('extra_price', '0.45')
            ->set('is_active', false)
            ->call('update')
            ->assertHasNoErrors();

        $this->assertSame('0.45', $ingredient->fresh()->extra_price);
        $this->assertFalse($ingredient->fresh()->is_active);
    }

    public function test_store_validates_fields(): void
    {
        Ingredient::create(['name' => 'Queso', 'extra_price' => 0.5]);

        Livewire::actingAs($this->admin)->test(CrudIngredient::class)
            ->set('name', 'Queso')
            ->set('extra_price', '-1')
            ->call('store')
            ->assertHasErrors(['name' => 'unique', 'extra_price' => 'min']);
    }

    public function test_destroy_asks_confirmation_then_deletes(): void
    {
        $ingredient = Ingredient::create(['name' => 'Temporal', 'extra_price' => 0]);

        // Se elimina desde el formulario de edición.
        Livewire::actingAs($this->admin)->test(CrudIngredient::class, ['ingredient' => $ingredient])
            ->assertSeeHtml('wire:click="destroyConfirm"')
            ->call('destroyConfirm')
            ->assertDispatched('swalConfirm')
            ->call('destroy')
            ->assertDispatched('swalmodal');

        $this->assertModelMissing($ingredient);
    }

    public function test_cannot_delete_ingredient_used_as_extra(): void
    {
        $ingredient = Ingredient::create(['name' => 'Queso', 'extra_price' => 0.5]);
        Product::create(['name' => 'Humita', 'price' => 2.5])->extras()->attach($ingredient);

        Livewire::actingAs($this->admin)->test(CrudIngredient::class, ['ingredient' => $ingredient])
            ->call('destroyConfirm')
            ->assertNotDispatched('swalConfirm')
            ->call('destroy');

        $this->assertModelExists($ingredient);
    }

    public function test_edit_shows_where_it_is_used_and_why_it_cannot_be_deleted(): void
    {
        $queso = Ingredient::create(['name' => 'Queso', 'extra_price' => 0.5]);
        Product::create(['name' => 'Caprese', 'price' => 2.5])->ingredients()->attach($queso, ['is_removable' => false]);
        Product::create(['name' => 'Humita', 'price' => 2.5])->extras()->attach($queso, ['price' => 0.8]);

        Livewire::actingAs($this->admin)->test(CrudIngredient::class, ['ingredient' => $queso])
            ->assertSee('Así se ofrece en caja')
            ->assertSee('Lo llevan por defecto')
            ->assertSee('Caprese')
            ->assertSee('Obligatorio')
            ->assertSee('Se ofrece como extra en')
            ->assertSee('+ $ 0,80')
            // La zona para eliminar explica antes por qué no se puede.
            ->assertSee('Queso se usa en productos.');

        // Sin uso se puede eliminar y lo dice.
        $huevo = Ingredient::create(['name' => 'Huevo', 'extra_price' => 0.4]);
        Livewire::actingAs($this->admin)->test(CrudIngredient::class, ['ingredient' => $huevo])
            ->assertSee('No se usa en productos ni en ventas, así que se puede eliminar.');
    }

    public function test_filters_and_stats(): void
    {
        $queso = Ingredient::create(['name' => 'Queso', 'extra_price' => 0.5, 'description' => 'Mozzarella rallada']);
        Product::create(['name' => 'Humita', 'price' => 2.5])->extras()->attach($queso);
        Ingredient::create(['name' => 'Panceta', 'extra_price' => 0.7, 'is_active' => false]);

        Livewire::actingAs($this->admin)->test(IngredientsTable::class)
            ->assertViewHas('stats', ['ingredients' => 2, 'extras' => 1, 'active' => 1])
            ->assertSee('Mozzarella rallada')
            ->assertSee('1 producto')
            ->call('setFilter', 'inactivos')
            ->assertSee('Panceta')
            ->assertDontSee('Queso')
            ->call('setFilter', 'activos')
            ->assertSee('Queso')
            ->assertDontSee('Panceta');
    }
}
