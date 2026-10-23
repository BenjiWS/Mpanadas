<?php

namespace Tests\Feature;

use App\Livewire\Combos\CombosTable;
use App\Livewire\Combos\CrudCombo;
use App\Livewire\Products\ProductsTable;
use App\Models\Combo;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class CombosCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Product $carne;

    private Product $gaseosa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, PermissionSeeder::class]);
        $this->admin = User::factory()->create()->assignRole('Administrador');
        $this->carne = Product::create(['name' => 'Empanada de carne', 'price' => 2.5]);
        $this->gaseosa = Product::create(['name' => 'Gaseosa', 'price' => 1.5]);
        Storage::fake('public');
    }

    private function combo(string $name = 'Combo clásico', array $attributes = []): Combo
    {
        $combo = Combo::create(['name' => $name, 'price' => 8, ...$attributes]);
        $combo->products()->attach([
            $this->carne->id => ['quantity' => 3],
            $this->gaseosa->id => ['quantity' => 1],
        ]);

        return $combo;
    }

    public function test_pages_require_permission(): void
    {
        $cajero = User::factory()->create()->assignRole('Cajero');
        $combo = $this->combo();

        $this->actingAs($cajero)->get('/combos')->assertForbidden();
        $this->actingAs($cajero)->get('/combos/create')->assertForbidden();

        $this->actingAs($this->admin)->get('/combos')->assertOk()
            ->assertSee('Combo clásico')
            ->assertSee('3 Empanada de carne + 1 Gaseosa')
            ->assertSee('$ 8,00');
        $this->actingAs($this->admin)->get('/combos/create')->assertOk()->assertSee('Nuevo combo');
        $this->actingAs($this->admin)->get("/combos/{$combo->id}/edit")->assertOk()->assertSee('Edición de Combo clásico');
    }

    public function test_store_creates_combo_with_products_and_image(): void
    {
        Livewire::actingAs($this->admin)->test(CrudCombo::class)
            ->assertSeeHtml('wire:submit="store"')
            ->set('name', 'Combo para dos')
            ->set('description', 'Empanadas + bebida')
            ->set('price', '12.50')
            ->call('addItem', $this->carne->id)
            ->call('addItem', $this->gaseosa->id)
            ->call('changeQuantity', 0, 5)
            ->set('items.1.quantity', '2')
            ->set('image', UploadedFile::fake()->image('combo.jpg'))
            ->call('store')
            ->assertHasNoErrors()
            ->assertDispatched('swalmodal');

        $combo = Combo::where('name', 'Combo para dos')->firstOrFail();
        $this->assertSame('12.50', $combo->price);
        $this->assertTrue($combo->is_active);
        $this->assertSame('Empanadas + bebida', $combo->description);
        $this->assertSame(6, $combo->products->firstWhere('id', $this->carne->id)->pivot->quantity);
        $this->assertSame(2, $combo->products->firstWhere('id', $this->gaseosa->id)->pivot->quantity);
        Storage::disk('public')->assertExists($combo->image);
    }

    public function test_store_validates_fields(): void
    {
        $this->combo();

        Livewire::actingAs($this->admin)->test(CrudCombo::class)
            ->set('name', 'Combo clásico')
            ->set('price', '-1')
            ->call('store')
            ->assertHasErrors(['name' => 'unique', 'price' => 'min', 'items' => 'required']);

        Livewire::actingAs($this->admin)->test(CrudCombo::class)
            ->set('name', 'Combo nuevo')
            ->set('price', '5')
            ->call('addItem', $this->carne->id)
            ->set('items.0.quantity', '0')
            ->call('store')
            ->assertHasErrors('items.0.quantity');

        Livewire::actingAs($this->admin)->test(CrudCombo::class)
            ->set('image', UploadedFile::fake()->create('menu.pdf', 100, 'application/pdf'))
            ->assertHasErrors(['image' => 'image']);
    }

    public function test_cannot_add_the_same_product_twice_or_a_sold_out_one(): void
    {
        $agotada = Product::create(['name' => 'Humita', 'price' => 2.5, 'available' => false]);

        Livewire::actingAs($this->admin)->test(CrudCombo::class)
            ->assertDontSee('Humita')
            ->call('addItem', $this->carne->id)
            ->call('addItem', $this->carne->id)
            ->call('addItem', $agotada->id)
            ->assertCount('items', 1)
            // La cantidad no baja de 1.
            ->call('changeQuantity', 0, -1)
            ->assertSet('items.0.quantity', 1);
    }

    public function test_update_syncs_products_and_replaces_image(): void
    {
        Storage::disk('public')->put('combos/vieja.jpg', 'x');
        $combo = $this->combo(attributes: ['image' => 'combos/vieja.jpg']);

        // Al editar se cargan los productos ordenados por nombre.
        Livewire::actingAs($this->admin)->test(CrudCombo::class, ['combo' => $combo])
            ->assertSeeHtml('wire:submit="update"')
            ->assertSet('items.0.id', $this->carne->id)
            ->assertSet('items.0.quantity', 3)
            ->call('removeItem', 1)
            ->set('price', '7.00')
            ->set('image', UploadedFile::fake()->image('nueva.jpg'))
            ->call('update')
            ->assertHasNoErrors();

        $combo->refresh();
        $this->assertSame('7.00', $combo->price);
        $this->assertSame(['Empanada de carne'], $combo->products->pluck('name')->all());
        Storage::disk('public')->assertMissing('combos/vieja.jpg');
        Storage::disk('public')->assertExists($combo->image);
    }

    public function test_update_can_remove_the_image(): void
    {
        Storage::disk('public')->put('combos/vieja.jpg', 'x');
        $combo = $this->combo(attributes: ['image' => 'combos/vieja.jpg']);

        Livewire::actingAs($this->admin)->test(CrudCombo::class, ['combo' => $combo])
            ->call('clearImage')
            ->call('update')
            ->assertHasNoErrors();

        $this->assertNull($combo->fresh()->image);
        Storage::disk('public')->assertMissing('combos/vieja.jpg');
    }

    public function test_toggle_active_and_filters(): void
    {
        $combo = $this->combo();
        $this->combo('Combo familiar');

        Livewire::actingAs($this->admin)->test(CombosTable::class)
            ->call('toggleActive', $combo->id)
            ->call('setFilter', 'inactivos')
            ->assertSee('Combo clásico')
            ->assertDontSee('Combo familiar')
            ->call('setFilter', 'activos')
            ->assertSee('Combo familiar')
            ->assertDontSee('Combo clásico');

        $this->assertFalse($combo->fresh()->is_active);
        $this->assertSame(['Combo familiar'], Combo::available()->pluck('name')->all());
    }

    public function test_search_by_name_or_product(): void
    {
        $this->combo();
        $veggie = Combo::create(['name' => 'Veggie', 'price' => 6]);
        $veggie->products()->attach(Product::create(['name' => 'Caprese', 'price' => 2.5]), ['quantity' => 2]);

        Livewire::actingAs($this->admin)->test(CombosTable::class)
            ->set('search', 'caprese')
            ->assertSee('Veggie')
            ->assertDontSee('Combo clásico');
    }

    public function test_destroy_asks_confirmation_then_soft_deletes(): void
    {
        $combo = $this->combo();

        Livewire::actingAs($this->admin)->test(CombosTable::class)
            ->call('destroyConfirm', $combo->id)
            ->assertDispatched('swalConfirm')
            ->call('destroy', $combo->id)
            ->assertDispatched('swalmodal');

        $this->assertSoftDeleted($combo);

        // Con el combo eliminado, sus productos ya se pueden eliminar.
        Livewire::actingAs($this->admin)->test(ProductsTable::class)
            ->call('destroy', $this->carne->id);

        $this->assertSoftDeleted($this->carne);
    }

    public function test_actions_require_permission(): void
    {
        $supervisor = User::factory()->create()->assignRole('Supervisor');
        $supervisor->roles->first()->revokePermissionTo('delete combos');
        $combo = $this->combo();

        Livewire::actingAs($supervisor)->test(CombosTable::class)
            ->assertDontSeeHtml('wire:click="destroyConfirm('.$combo->id.')"')
            ->call('destroy', $combo->id)
            ->assertForbidden();

        $this->assertNotSoftDeleted($combo);
    }
}
