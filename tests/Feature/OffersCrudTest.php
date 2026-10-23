<?php

namespace Tests\Feature;

use App\Enums\DiscountType;
use App\Livewire\Offers\CrudOffer;
use App\Livewire\Offers\OffersTable;
use App\Models\Category;
use App\Models\Combo;
use App\Models\Offer;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\ComboSeeder;
use Database\Seeders\IngredientSeeder;
use Database\Seeders\OfferSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\ProductSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class OffersCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Category $carne;

    private Product $empanada;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, PermissionSeeder::class]);
        $this->admin = User::factory()->create()->assignRole('Administrador');
        $this->carne = Category::create(['name' => 'Carne']);
        $this->empanada = Product::create(['name' => 'Empanada de carne', 'price' => 2.5]);
    }

    private function offer(string $name = 'Tardes con sabor', array $attributes = []): Offer
    {
        $offer = Offer::create([
            'name' => $name,
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonth(),
            ...$attributes,
        ]);
        $offer->categories()->attach($this->carne);

        return $offer;
    }

    public function test_pages_require_permission(): void
    {
        $cajero = User::factory()->create()->assignRole('Cajero');
        $offer = $this->offer(attributes: ['weekdays' => '1,2,3,4,5', 'start_time' => '15:00:00', 'end_time' => '17:00:00']);

        $this->actingAs($cajero)->get('/offers')->assertForbidden();
        $this->actingAs($cajero)->get('/offers/create')->assertForbidden();

        $this->actingAs($this->admin)->get('/offers')->assertOk()
            ->assertSee('Tardes con sabor')
            ->assertSee('10%')
            ->assertSee('Lunes a viernes · 15:00–17:00')
            ->assertSee('Vigente');
        $this->actingAs($this->admin)->get('/offers/create')->assertOk()->assertSee('Nueva oferta');
        $this->actingAs($this->admin)->get("/offers/{$offer->id}/edit")->assertOk()->assertSee('Edición de Tardes con sabor');
    }

    public function test_store_creates_two_for_one_with_schedule_and_targets(): void
    {
        $combo = Combo::create(['name' => 'Combo clásico', 'price' => 8]);

        Livewire::actingAs($this->admin)->test(CrudOffer::class)
            ->assertSeeHtml('wire:submit="store"')
            ->set('name', 'Tardes con sabor')
            ->set('discount_type', 'two_for_one')
            ->set('starts_at', '2026-11-01')
            ->set('ends_at', '2026-12-31')
            ->call('setWeekdays', 'weekdays')
            ->set('start_time', '15:00')
            ->set('end_time', '17:00')
            ->set('selectedCategories', [(string) $this->carne->id])
            ->call('addProduct', $this->empanada->id)
            ->call('addCombo', $combo->id)
            ->call('store')
            ->assertHasNoErrors()
            ->assertDispatched('swalmodal');

        $offer = Offer::where('name', 'Tardes con sabor')->firstOrFail();
        $this->assertSame(DiscountType::TwoForOne, $offer->discount_type);
        $this->assertSame('2 × 1', $offer->headline());
        $this->assertSame('2026-11-01 00:00:00', $offer->starts_at->format('Y-m-d H:i:s'));
        $this->assertSame('2026-12-31 23:59:59', $offer->ends_at->format('Y-m-d H:i:s'));
        $this->assertSame('Lunes a viernes · 15:00–17:00', $offer->scheduleLabel());
        $this->assertCount(1, $offer->categories);
        $this->assertCount(1, $offer->products);
        $this->assertCount(1, $offer->combos);
        $this->assertSame(1.25, $offer->discountFor(2.5));
    }

    public function test_store_validates_fields(): void
    {
        $this->offer();

        Livewire::actingAs($this->admin)->test(CrudOffer::class)
            ->set('name', 'Tardes con sabor')
            ->set('discount_value', '150')
            ->set('starts_at', '2026-12-31')
            ->set('ends_at', '2026-12-01')
            ->set('start_time', '18:00')
            ->set('end_time', '17:00')
            ->call('store')
            ->assertHasErrors(['name' => 'unique', 'discount_value' => 'max', 'ends_at' => 'after_or_equal', 'end_time' => 'after']);

        // Sin categorías, productos ni combos no se guarda.
        Livewire::actingAs($this->admin)->test(CrudOffer::class)
            ->set('name', 'Sin destino')
            ->set('discount_type', 'fixed')
            ->set('discount_value', '0.50')
            ->call('store')
            ->assertHasErrors('targets');

        $this->assertDatabaseMissing('offers', ['name' => 'Sin destino']);
    }

    public function test_update_loads_and_saves_changes(): void
    {
        $offer = $this->offer(attributes: ['weekdays' => '6,7']);
        $offer->products()->attach($this->empanada);

        Livewire::actingAs($this->admin)->test(CrudOffer::class, ['offer' => $offer])
            ->assertSeeHtml('wire:submit="update"')
            ->assertSet('weekdays', [6, 7])
            ->assertSet('selectedProducts', [$this->empanada->id])
            ->set('discount_type', 'fixed')
            ->set('discount_value', '0.75')
            // Así llegan desde los chips: como texto y en el orden en que se marcaron.
            ->set('weekdays', ['6', '7', '5'])
            ->assertSet('weekdays', [5, 6, 7])
            ->call('removeProduct', $this->empanada->id)
            ->call('update')
            ->assertHasNoErrors();

        $offer->refresh();
        $this->assertSame(DiscountType::Fixed, $offer->discount_type);
        $this->assertSame('$ 0,75', $offer->headline());
        $this->assertSame('5,6,7', $offer->weekdays);
        $this->assertSame('Viernes a domingo', $offer->scheduleLabel());
        $this->assertCount(0, $offer->products);
    }

    public function test_all_days_preset_marks_every_day_and_saves_as_all_days(): void
    {
        $offer = $this->offer();

        // Lun a vie a mano, luego el atajo Todos: quedan los 7 días marcados (antes quedaba vacío y
        // al completar la semana a mano se guardaban solo los días tocados después).
        Livewire::actingAs($this->admin)->test(CrudOffer::class, ['offer' => $offer])
            ->set('weekdays', ['1', '2', '3', '4', '5'])
            ->call('setWeekdays', 'all')
            ->assertSet('weekdays', [1, 2, 3, 4, 5, 6, 7])
            ->assertSee('Todos los días')
            ->call('update')
            ->assertHasNoErrors();

        // Los 7 días se guardan como "todos los días".
        $this->assertNull($offer->fresh()->weekdays);
        $this->assertSame('Todos los días', $offer->fresh()->scheduleLabel());
    }

    public function test_current_scope_checks_dates_days_and_hours(): void
    {
        Carbon::setTestNow('2026-10-21 16:00:00'); // miércoles

        $this->offer('Tarde', ['weekdays' => '1,2,3,4,5', 'start_time' => '15:00:00', 'end_time' => '17:00:00']);
        $this->offer('Noche', ['start_time' => '20:00:00', 'end_time' => '23:00:00']);
        $this->offer('Finde', ['weekdays' => '6,7']);
        $this->offer('Pausada', ['active' => false]);
        $this->offer('Vieja', ['starts_at' => now()->subMonths(2), 'ends_at' => now()->subMonth()]);

        $this->assertSame(['Tarde'], Offer::current()->pluck('name')->all());

        Carbon::setTestNow();
    }

    public function test_filters_and_paused_offers(): void
    {
        $this->offer();
        $this->offer('Vieja', ['starts_at' => now()->subMonths(2), 'ends_at' => now()->subMonth()]);
        $pausada = $this->offer('Pausada', ['active' => false]);

        Livewire::actingAs($this->admin)->test(OffersTable::class)
            ->call('setFilter', 'finalizadas')
            ->assertSee('Vieja')
            ->assertDontSee('Tardes con sabor')
            ->call('setFilter', 'vigentes')
            ->assertSee('Tardes con sabor')
            ->assertDontSee('Vieja')
            // Una pausada no está vigente aunque esté dentro de sus fechas.
            ->assertDontSee('Pausada');

        $this->assertSame('pausada', $pausada->status());
    }

    public function test_card_shows_period_when_there_is_no_schedule(): void
    {
        $this->offer('Todo el mes', ['starts_at' => '2026-10-01', 'ends_at' => '2026-12-31 23:59:59']);
        $this->offer('Solo tardes', ['start_time' => '15:00:00', 'end_time' => '17:00:00']);

        Livewire::actingAs($this->admin)->test(OffersTable::class)
            ->assertSee('1 oct – 31 dic 2026')
            ->assertSee('Todos los días · 15:00–17:00');
    }

    public function test_search_by_name_or_target(): void
    {
        $this->offer();
        $dulce = $this->offer('Semana dulce');
        $dulce->categories()->sync(Category::create(['name' => 'Postres']));

        Livewire::actingAs($this->admin)->test(OffersTable::class)
            ->set('search', 'postres')
            ->assertSee('Semana dulce')
            ->assertDontSee('Tardes con sabor');
    }

    public function test_destroy_asks_confirmation_then_soft_deletes(): void
    {
        $offer = $this->offer();

        // Se elimina desde el formulario de edición.
        Livewire::actingAs($this->admin)->test(CrudOffer::class, ['offer' => $offer])
            ->assertSeeHtml('wire:click="destroyConfirm"')
            ->call('destroyConfirm')
            ->assertDispatched('swalConfirm')
            ->call('destroy')
            ->assertDispatched('swalmodal');

        $this->assertSoftDeleted($offer);

        // Sin permiso no aparece el botón ni se puede llamar.
        $supervisor = User::factory()->create()->assignRole('Supervisor');
        $supervisor->roles->first()->revokePermissionTo('delete offers');
        $otra = $this->offer('Otra');

        Livewire::actingAs($supervisor)->test(CrudOffer::class, ['offer' => $otra])
            ->assertDontSeeHtml('wire:click="destroyConfirm"')
            ->call('destroy')
            ->assertForbidden();

        $this->assertNotSoftDeleted($otra);
    }

    public function test_seeder_creates_example_offers(): void
    {
        $this->seed([CategorySeeder::class, IngredientSeeder::class, ProductSeeder::class, ComboSeeder::class, OfferSeeder::class]);
        $this->seed(OfferSeeder::class);

        $this->assertSame(5, Offer::count());
        $this->assertSame(3, Offer::running()->count());
        $this->assertSame(['Semana dulce'], Offer::ended()->pluck('name')->all());
        $this->assertSame('programada', Offer::where('name', 'Lanzamiento veggie')->first()->status());
        $this->assertSame(0, Offer::doesntHave('categories')->doesntHave('products')->doesntHave('combos')->count());
    }
}
