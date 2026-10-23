<?php

namespace App\Livewire\Combos;

use App\Livewire\Concerns\AuthorizesPermissions;
use App\Livewire\Concerns\ReportsErrors;
use App\Models\Combo;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

class CrudCombo extends Component
{
    use AuthorizesPermissions, ReportsErrors, WithFileUploads;

    #[Locked]
    public $action = 'store';

    public $title = 'Nuevo combo';

    public $name;

    public $description;

    public $price;

    public $is_active = true;

    /** Productos que trae: [['id' => product_id, 'quantity' => int], ...] */
    public array $items = [];

    /** Imagen nueva subida desde el formulario */
    public $image;

    /** Ruta de la imagen que ya tenía el combo (disco public) */
    #[Locked]
    public $currentImage;

    /** Marca que se quiere quitar la imagen actual sin subir otra */
    public $removeImage = false;

    #[Locked]
    public $combo_id;

    protected function rules()
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('combos', 'name')->whereNull('deleted_at')->ignore($this->combo_id),
            ],
            'description' => ['nullable', 'string', 'max:500'],
            'price' => [
                'required',
                'numeric',
                'min:0',
                'max:999999.99', // columna decimal(8,2)
            ],
            'is_active' => ['boolean'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['distinct', Rule::exists('products', 'id')->whereNull('deleted_at')],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:999'],
            'image' => ['nullable', 'image', 'max:2048'],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => __('Debe ingresar el nombre del combo'),
            'name.unique' => __('El combo ya ha sido registrado'),
            'description.max' => __('La descripción no puede pasar de 500 caracteres'),
            'price.required' => __('Debe ingresar el precio'),
            'price.numeric' => __('El precio debe ser un número'),
            'price.min' => __('El precio no puede ser negativo'),
            'price.max' => __('El precio es demasiado alto'),
            'items.required' => __('Debe agregar al menos un producto'),
            'items.min' => __('Debe agregar al menos un producto'),
            'items.*.id.distinct' => __('Ese producto ya está en el combo'),
            'items.*.quantity.required' => __('Indique la cantidad'),
            'items.*.quantity.integer' => __('La cantidad debe ser un número entero'),
            'items.*.quantity.min' => __('La cantidad mínima es 1'),
            'items.*.quantity.max' => __('La cantidad es demasiado alta'),
            'image.image' => __('El archivo debe ser una imagen'),
            'image.max' => __('La imagen no puede pesar más de 2 MB'),
        ];
    }

    public function mount(Combo $combo)
    {
        if ($combo->exists) {
            $this->action = 'edit';
            $this->combo_id = $combo->id;
            $this->name = $combo->name;
            $this->description = $combo->description;
            $this->price = $combo->price;
            $this->is_active = $combo->is_active ?? true;
            $this->items = $combo->products->sortBy('name')
                ->map(fn ($product) => ['id' => $product->id, 'quantity' => (int) $product->pivot->quantity])
                ->values()->all();
            $this->currentImage = $combo->image;
            $this->title = __('Edición de :name', ['name' => $combo->name]);
        }
    }

    public function updatedImage()
    {
        try {
            $this->validateOnly('image');
        } catch (ValidationException $e) {
            // Un archivo que no es imagen no se puede previsualizar: se descarta y se muestra el error.
            $this->reset('image');

            throw $e;
        }

        $this->removeImage = false;
    }

    /**
     * Descarta la imagen recién subida o marca la actual para quitarla al guardar.
     */
    public function clearImage()
    {
        $this->reset('image');
        $this->removeImage = (bool) $this->currentImage;
    }

    /**
     * Agrega un producto disponible que todavía no esté en el combo, con cantidad 1.
     */
    public function addItem($id)
    {
        $id = (int) $id;

        if ($id > 0
            && ! in_array($id, array_column($this->items, 'id'))
            && Product::whereKey($id)->where('available', true)->exists()) {
            $this->items[] = ['id' => $id, 'quantity' => 1];
            $this->resetValidation('items');
        }
    }

    public function removeItem(int $index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
        $this->resetValidation('items.*');
    }

    /**
     * Suma o resta unidades de un producto sin bajar de 1.
     */
    public function changeQuantity(int $index, int $step)
    {
        if (isset($this->items[$index])) {
            $this->items[$index]['quantity'] = max(1, min(999, (int) $this->items[$index]['quantity'] + $step));
        }
    }

    public function render()
    {
        $itemIds = array_column($this->items, 'id');

        // Los del combo (aunque estén agotados, para mostrar su nombre) y los disponibles para agregar.
        $products = Product::where('available', true)
            ->orWhereIn('id', $itemIds)
            ->orderBy('name')
            ->get()
            ->keyBy('id');

        return view('livewire.combos.crud-combo', [
            'products' => $products,
            'productOptions' => $products->where('available', true)->except($itemIds),
        ])->title($this->title);
    }

    /**
     * Productos para sync(): [product_id => ['quantity' => int]].
     */
    protected function itemsForSync(): array
    {
        return collect($this->items)
            ->mapWithKeys(fn ($row) => [$row['id'] => ['quantity' => (int) $row['quantity']]])
            ->all();
    }

    /**
     * Guarda la imagen subida y devuelve la ruta que debe quedar en el combo.
     */
    protected function resolveImage(): ?string
    {
        if ($this->image) {
            return $this->image->store('combos', 'public');
        }

        return $this->removeImage ? null : $this->currentImage;
    }

    public function store()
    {
        $this->authorizePermission('create combos');

        $data = $this->validate();

        try {
            DB::beginTransaction();

            $combo = Combo::create([
                'name' => $data['name'],
                'description' => $data['description'] ?: null,
                'price' => $data['price'],
                'is_active' => $data['is_active'],
                'image' => $this->resolveImage(),
            ]);
            $combo->products()->sync($this->itemsForSync());

            DB::commit();

            $this->dispatch('swalmodal',
                type: 'success',
                title: __('¡Completado!'),
                text: __('Combo creado con éxito'),
                redirect: route('combos'),
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
        $this->authorizePermission('edit combos');

        // Se usa el combo que se abrió en mount(), no un id enviado desde el navegador.
        $combo = Combo::findOrFail($this->combo_id);

        $data = $this->validate();

        try {
            DB::beginTransaction();

            $image = $this->resolveImage();

            $combo->update([
                'name' => $data['name'],
                'description' => $data['description'] ?: null,
                'price' => $data['price'],
                'is_active' => $data['is_active'],
                'image' => $image,
            ]);
            $combo->products()->sync($this->itemsForSync());

            DB::commit();

            // La imagen anterior se borra solo cuando el cambio ya quedó guardado.
            if ($this->currentImage && $this->currentImage !== $image) {
                Storage::disk('public')->delete($this->currentImage);
            }

            $this->dispatch('swalmodal',
                type: 'success',
                title: __('¡Completado!'),
                text: __('Actualizado con éxito'),
                redirect: route('combos'),
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
}
