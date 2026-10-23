<?php

namespace App\Livewire\Offers;

use App\Enums\DiscountType;
use App\Livewire\Concerns\AuthorizesPermissions;
use App\Livewire\Concerns\ReportsErrors;
use App\Models\Category;
use App\Models\Combo;
use App\Models\Offer;
use App\Models\Product;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class CrudOffer extends Component
{
    use AuthorizesPermissions, ReportsErrors;

    #[Locked]
    public $action = 'store';

    public $title = 'Nueva oferta';

    public $name;

    public $description;

    /** percentage | fixed | two_for_one (App\Enums\DiscountType) */
    public $discount_type = 'percentage';

    public $discount_value;

    /** Vigencia por día: empieza a las 00:00 del primero y termina a las 23:59 del último */
    public $starts_at;

    public $ends_at;

    /** Días ISO en que aplica (1 = lunes); vacío = todos los días */
    public array $weekdays = [];

    /** Franja horaria opcional, formato H:i */
    public $start_time;

    public $end_time;

    public $active = true;

    public array $selectedCategories = [];

    /** ids de productos y combos a los que aplica */
    public array $selectedProducts = [];

    public array $selectedCombos = [];

    #[Locked]
    public $offer_id;

    protected function rules()
    {
        $twoForOne = $this->discount_type === DiscountType::TwoForOne->value;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('offers', 'name')->whereNull('deleted_at')->ignore($this->offer_id),
            ],
            'description' => ['nullable', 'string', 'max:500'],
            'discount_type' => ['required', Rule::enum(DiscountType::class)],
            'discount_value' => $twoForOne ? ['nullable'] : [
                'required',
                'numeric',
                'gt:0',
                $this->discount_type === DiscountType::Percentage->value ? 'max:100' : 'max:999999.99',
            ],
            'starts_at' => ['required', 'date_format:Y-m-d'],
            'ends_at' => ['required', 'date_format:Y-m-d', 'after_or_equal:starts_at'],
            'weekdays' => ['array'],
            'weekdays.*' => ['integer', 'between:1,7'],
            'start_time' => ['nullable', 'required_with:end_time', 'date_format:H:i'],
            'end_time' => ['nullable', 'required_with:start_time', 'date_format:H:i', 'after:start_time'],
            'active' => ['boolean'],
            'selectedCategories' => ['array'],
            'selectedCategories.*' => [Rule::exists('categories', 'id')->whereNull('deleted_at')],
            'selectedProducts' => ['array'],
            'selectedProducts.*' => ['distinct', Rule::exists('products', 'id')->whereNull('deleted_at')],
            'selectedCombos' => ['array'],
            'selectedCombos.*' => ['distinct', Rule::exists('combos', 'id')->whereNull('deleted_at')],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => __('Debe ingresar el nombre de la oferta'),
            'name.unique' => __('La oferta ya ha sido registrada'),
            'description.max' => __('La descripción no puede pasar de 500 caracteres'),
            'discount_value.required' => __('Debe ingresar el descuento'),
            'discount_value.numeric' => __('El descuento debe ser un número'),
            'discount_value.gt' => __('El descuento debe ser mayor a 0'),
            'discount_value.max' => $this->discount_type === DiscountType::Percentage->value
                ? __('El porcentaje no puede pasar de 100')
                : __('El descuento es demasiado alto'),
            'starts_at.required' => __('Indique desde cuándo'),
            'ends_at.required' => __('Indique hasta cuándo'),
            'ends_at.after_or_equal' => __('El fin no puede ser antes del inicio'),
            'start_time.required_with' => __('Indique la hora de inicio'),
            'end_time.required_with' => __('Indique la hora de fin'),
            'end_time.after' => __('La hora de fin debe ser después de la de inicio'),
        ];
    }

    public function mount(Offer $offer)
    {
        if ($offer->exists) {
            $this->action = 'edit';
            $this->offer_id = $offer->id;
            $this->name = $offer->name;
            $this->description = $offer->description;
            $this->discount_type = $offer->discount_type->value;
            $this->discount_value = $offer->discount_type === DiscountType::TwoForOne ? null : $offer->discount_value;
            $this->starts_at = $offer->starts_at->format('Y-m-d');
            $this->ends_at = $offer->ends_at->format('Y-m-d');
            $this->weekdays = $offer->weekdayList();
            $this->start_time = $offer->start_time ? substr($offer->start_time, 0, 5) : null;
            $this->end_time = $offer->end_time ? substr($offer->end_time, 0, 5) : null;
            $this->active = $offer->active ?? true;
            $this->selectedCategories = $offer->categories->pluck('id')->map(fn ($id) => (string) $id)->all();
            $this->selectedProducts = $offer->products->sortBy('name')->pluck('id')->values()->all();
            $this->selectedCombos = $offer->combos->sortBy('name')->pluck('id')->values()->all();
            $this->title = __('Edición de :name', ['name' => $offer->name]);
        } else {
            $this->starts_at = today()->format('Y-m-d');
            $this->ends_at = today()->addMonth()->format('Y-m-d');
        }
    }

    public function updatedDiscountType()
    {
        $this->resetValidation('discount_value');
    }

    /**
     * Los chips de días están enlazados con wire:model: llegan como texto y en el orden en que se marcaron.
     */
    public function updatedWeekdays()
    {
        $days = array_unique(array_map('intval', $this->weekdays));
        $days = array_filter($days, fn ($day) => isset(Offer::WEEKDAYS[$day]));
        sort($days);

        $this->weekdays = array_values($days);
    }

    /**
     * Atajos de días. "Todos" marca los 7 chips (al guardar equivale a todos los días, igual que ninguno).
     */
    public function setWeekdays(string $preset)
    {
        $this->weekdays = match ($preset) {
            'weekdays' => [1, 2, 3, 4, 5],
            'weekend' => [6, 7],
            default => [1, 2, 3, 4, 5, 6, 7],
        };
    }

    public function clearSchedule()
    {
        $this->reset('start_time', 'end_time');
        $this->resetValidation(['start_time', 'end_time']);
    }

    public function addProduct($id)
    {
        $id = (int) $id;

        if ($id > 0 && ! in_array($id, $this->selectedProducts) && Product::whereKey($id)->exists()) {
            $this->selectedProducts[] = $id;
            $this->resetValidation('targets');
        }
    }

    public function removeProduct(int $id)
    {
        $this->selectedProducts = array_values(array_diff($this->selectedProducts, [$id]));
    }

    public function addCombo($id)
    {
        $id = (int) $id;

        if ($id > 0 && ! in_array($id, $this->selectedCombos) && Combo::whereKey($id)->exists()) {
            $this->selectedCombos[] = $id;
            $this->resetValidation('targets');
        }
    }

    public function removeCombo(int $id)
    {
        $this->selectedCombos = array_values(array_diff($this->selectedCombos, [$id]));
    }

    public function updatedSelectedCategories()
    {
        $this->resetValidation('targets');
    }

    public function render()
    {
        $products = Product::orderBy('name')->get()->keyBy('id');
        $combos = Combo::orderBy('name')->get()->keyBy('id');

        return view('livewire.offers.crud-offer', [
            'categories' => Category::orderBy('name')->get(),
            'products' => $products,
            'combos' => $combos,
            'productOptions' => $products->except($this->selectedProducts),
            'comboOptions' => $combos->except($this->selectedCombos),
            'discountTypes' => DiscountType::cases(),
        ])->title($this->title);
    }

    /**
     * Valida el formulario y que la oferta aplique al menos a algo.
     */
    protected function validateOffer(): array
    {
        $data = $this->validate();

        if (empty($data['selectedCategories']) && empty($data['selectedProducts']) && empty($data['selectedCombos'])) {
            $this->addError('targets', __('Elija al menos una categoría, producto o combo'));

            return [];
        }

        return $data;
    }

    /**
     * Columnas de la oferta a partir de los datos validados.
     */
    protected function offerAttributes(array $data): array
    {
        $twoForOne = $data['discount_type'] === DiscountType::TwoForOne->value;
        $weekdays = array_unique(array_map('intval', $data['weekdays']));
        sort($weekdays);

        return [
            'name' => $data['name'],
            'description' => $data['description'] ?: null,
            'discount_type' => $data['discount_type'],
            'discount_value' => $twoForOne ? 0 : $data['discount_value'],
            'starts_at' => Carbon::parse($data['starts_at'])->startOfDay(),
            'ends_at' => Carbon::parse($data['ends_at'])->endOfDay(),
            // Los 7 días equivalen a "todos los días".
            'weekdays' => $weekdays && count($weekdays) < 7 ? implode(',', $weekdays) : null,
            'start_time' => $data['start_time'] ? $data['start_time'].':00' : null,
            'end_time' => $data['end_time'] ? $data['end_time'].':00' : null,
            'active' => $data['active'],
        ];
    }

    protected function syncTargets(Offer $offer, array $data): void
    {
        $offer->categories()->sync($data['selectedCategories']);
        $offer->products()->sync($data['selectedProducts']);
        $offer->combos()->sync($data['selectedCombos']);
    }

    public function store()
    {
        $this->authorizePermission('create offers');

        if (! $data = $this->validateOffer()) {
            return;
        }

        try {
            DB::beginTransaction();

            $offer = Offer::create($this->offerAttributes($data));
            $this->syncTargets($offer, $data);

            DB::commit();

            $this->dispatch('swalmodal',
                type: 'success',
                title: __('¡Completado!'),
                text: __('Oferta creada con éxito'),
                redirect: route('offers'),
            );

        } catch (\Exception $e) {
            DB::rollback();
            $this->dispatch('swalmodal',
                type: 'warning',
                title: __('¡Error!'),
                text: $this->errorMessage($e),
            );
        }
    }

    public function update()
    {
        $this->authorizePermission('edit offers');

        // Se usa la oferta que se abrió en mount(), no un id enviado desde el navegador.
        $offer = Offer::findOrFail($this->offer_id);

        if (! $data = $this->validateOffer()) {
            return;
        }

        try {
            DB::beginTransaction();

            $offer->update($this->offerAttributes($data));
            $this->syncTargets($offer, $data);

            DB::commit();

            $this->dispatch('swalmodal',
                type: 'success',
                title: __('¡Completado!'),
                text: __('Actualizado con éxito'),
                redirect: route('offers'),
            );

        } catch (\Exception $e) {
            DB::rollback();
            $this->dispatch('swalmodal',
                type: 'warning',
                title: __('¡Error!'),
                text: $this->errorMessage($e),
            );
        }
    }

    public function destroyConfirm()
    {
        $this->authorizePermission('delete offers');

        $this->dispatch('swalConfirm',
            type: 'error',
            title: __('¿Está seguro de eliminar esta oferta?'),
            text: __('¡No podrás revertir tu decisión!'),
            id: $this->offer_id,
        );
    }

    #[On('destroy')]
    public function destroy()
    {
        $this->authorizePermission('delete offers');

        // Se elimina la oferta abierta en mount(), no un id enviado desde el navegador.
        $offer = Offer::findOrFail($this->offer_id);

        try {
            // Soft delete: las ventas que la aplicaron la siguen referenciando.
            $offer->delete();

            $this->dispatch('swalmodal',
                type: 'success',
                title: __('¡Eliminado!'),
                text: __('La oferta fue eliminada con éxito'),
                redirect: route('offers'),
            );

        } catch (\Exception $e) {
            $this->dispatch('swalmodal',
                type: 'warning',
                title: __('¡Error!'),
                text: $this->errorMessage($e),
            );
        }
    }
}
