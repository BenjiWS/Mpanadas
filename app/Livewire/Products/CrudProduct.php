<?php

namespace App\Livewire\Products;

use App\Livewire\Concerns\AuthorizesPermissions;
use App\Livewire\Concerns\ReportsErrors;
use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

class CrudProduct extends Component
{
    use AuthorizesPermissions, ReportsErrors, WithFileUploads;

    #[Locked]
    public $action = 'store';

    public $title = 'Nuevo producto';

    public $name;

    public $price;

    public $available = true;

    public array $selectedCategories = [];

    /** Ingredientes que lleva por defecto: [['id' => ingredient_id, 'removable' => bool], ...] */
    public array $defaultIngredients = [];

    /** Ingrediente elegido en "Agregar ingrediente" */
    public $newDefaultIngredient = '';

    /** Extras que se pueden agregar: [['id' => ingredient_id, 'price' => precio propio o null], ...] */
    public array $extras = [];

    /** Ingrediente elegido en "Agregar extra" */
    public $newExtra = '';

    /** Imagen nueva subida desde el formulario */
    public $image;

    /** Ruta de la imagen que ya tenía el producto (disco public) */
    #[Locked]
    public $currentImage;

    /** Marca que se quiere quitar la imagen actual sin subir otra */
    public $removeImage = false;

    #[Locked]
    public $product_id;

    protected function rules()
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'name')->whereNull('deleted_at')->ignore($this->product_id),
            ],
            'price' => [
                'required',
                'numeric',
                'min:0',
                'max:999999.99', // columna decimal(8,2)
            ],
            'available' => ['boolean'],
            'selectedCategories' => ['required', 'array', 'min:1'],
            'selectedCategories.*' => [Rule::exists('categories', 'id')->whereNull('deleted_at')],
            'defaultIngredients' => ['array'],
            'defaultIngredients.*.id' => ['distinct', Rule::exists('ingredients', 'id')],
            'defaultIngredients.*.removable' => ['boolean'],
            'extras' => ['array'],
            'extras.*.id' => ['distinct', Rule::exists('ingredients', 'id')],
            'extras.*.price' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'image' => ['nullable', 'image', 'max:2048'],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => __('Debe ingresar el nombre del producto'),
            'name.unique' => __('El producto ya ha sido registrado'),
            'price.required' => __('Debe ingresar el precio'),
            'price.numeric' => __('El precio debe ser un número'),
            'price.min' => __('El precio no puede ser negativo'),
            'price.max' => __('El precio es demasiado alto'),
            'selectedCategories.required' => __('Debe seleccionar al menos una categoría'),
            'selectedCategories.min' => __('Debe seleccionar al menos una categoría'),
            'image.image' => __('El archivo debe ser una imagen'),
            'image.max' => __('La imagen no puede pesar más de 2 MB'),
            'defaultIngredients.*.id.distinct' => __('Ese ingrediente ya está en la lista'),
            'extras.*.id.distinct' => __('Ese extra ya está en la lista'),
            'extras.*.price.numeric' => __('El precio del extra debe ser un número'),
            'extras.*.price.min' => __('El precio del extra no puede ser negativo'),
            'extras.*.price.max' => __('El precio del extra es demasiado alto'),
        ];
    }

    public function mount(Product $product)
    {
        if ($product->exists) {
            $this->action = 'edit';
            $this->product_id = $product->id;
            $this->name = $product->name;
            $this->price = $product->price;
            $this->available = $product->available ?? true;
            $this->selectedCategories = $product->categories->pluck('id')->map(fn ($id) => (string) $id)->all();
            $this->defaultIngredients = $product->ingredients->sortBy('name')
                ->map(fn ($ingredient) => ['id' => $ingredient->id, 'removable' => (bool) $ingredient->pivot->is_removable])
                ->values()->all();
            $this->extras = $product->extras->sortBy('name')
                ->map(fn ($extra) => ['id' => $extra->id, 'price' => $extra->pivot->price])
                ->values()->all();
            $this->currentImage = $product->image;
            $this->title = __('Edición de :name', ['name' => $product->name]);
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

    public function addDefaultIngredient($id = null)
    {
        $id = (int) ($id ?? $this->newDefaultIngredient);

        if ($this->canAdd($id, $this->defaultIngredients)) {
            // Por defecto se puede quitar al vender; se marca Obligatorio si no.
            $this->defaultIngredients[] = ['id' => $id, 'removable' => true];
        }

        $this->reset('newDefaultIngredient');
    }

    public function removeDefaultIngredient(int $index)
    {
        unset($this->defaultIngredients[$index]);
        $this->defaultIngredients = array_values($this->defaultIngredients);
    }

    public function addExtra($id = null)
    {
        $id = (int) ($id ?? $this->newExtra);

        if ($this->canAdd($id, $this->extras)) {
            $this->extras[] = ['id' => $id, 'price' => null];
        }

        $this->reset('newExtra');
    }

    public function removeExtra(int $index)
    {
        unset($this->extras[$index]);
        $this->extras = array_values($this->extras);
        $this->resetValidation('extras.*');
    }

    /**
     * Un ingrediente se agrega si es activo y todavía no está en esa lista.
     */
    protected function canAdd(int $id, array $rows): bool
    {
        return $id > 0
            && ! in_array($id, array_column($rows, 'id'))
            && Ingredient::whereKey($id)->where('is_active', true)->exists();
    }

    public function render()
    {
        $defaultIds = array_column($this->defaultIngredients, 'id');
        $extraIds = array_column($this->extras, 'id');

        // Los de las listas (aunque estén inactivos, para mostrar su nombre) y los activos para agregar.
        $ingredients = Ingredient::where('is_active', true)
            ->orWhereIn('id', [...$defaultIds, ...$extraIds])
            ->orderBy('name')
            ->get()
            ->keyBy('id');

        return view('livewire.products.crud-product', [
            'categories' => Category::orderBy('name')->get(),
            'ingredients' => $ingredients,
            'defaultOptions' => $ingredients->where('is_active', true)->except($defaultIds),
            'extraOptions' => $ingredients->where('is_active', true)->except($extraIds),
        ])->title($this->title);
    }

    /**
     * Ingredientes por defecto para sync(): [ingredient_id => ['is_removable' => bool]].
     */
    protected function defaultIngredientsForSync(): array
    {
        return collect($this->defaultIngredients)
            ->mapWithKeys(fn ($row) => [$row['id'] => ['is_removable' => (bool) $row['removable']]])
            ->all();
    }

    /**
     * Extras para sync(): [ingredient_id => ['price' => precio propio o null]].
     */
    protected function extrasForSync(): array
    {
        return collect($this->extras)
            ->mapWithKeys(fn ($row) => [$row['id'] => ['price' => blank($row['price'] ?? null) ? null : $row['price']]])
            ->all();
    }

    /**
     * Guarda la imagen subida y devuelve la ruta que debe quedar en el producto.
     */
    protected function resolveImage(): ?string
    {
        if ($this->image) {
            return $this->image->store('products', 'public');
        }

        return $this->removeImage ? null : $this->currentImage;
    }

    public function store()
    {
        $this->authorizePermission('create products');

        $data = $this->validate();

        try {
            DB::beginTransaction();

            $product = Product::create([
                'name' => $data['name'],
                'price' => $data['price'],
                'available' => $data['available'],
                'image' => $this->resolveImage(),
            ]);
            $product->categories()->sync($data['selectedCategories']);
            $product->ingredients()->sync($this->defaultIngredientsForSync());
            $product->extras()->sync($this->extrasForSync());

            DB::commit();

            $this->dispatch('swalmodal',
                type: 'success',
                title: __('¡Completado!'),
                text: __('Producto creado con éxito'),
                redirect: route('products'),
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
        $this->authorizePermission('edit products');

        // Se usa el producto que se abrió en mount(), no un id enviado desde el navegador.
        $product = Product::findOrFail($this->product_id);

        $data = $this->validate();

        try {
            DB::beginTransaction();

            $image = $this->resolveImage();

            $product->update([
                'name' => $data['name'],
                'price' => $data['price'],
                'available' => $data['available'],
                'image' => $image,
            ]);
            $product->categories()->sync($data['selectedCategories']);
            $product->ingredients()->sync($this->defaultIngredientsForSync());
            $product->extras()->sync($this->extrasForSync());

            DB::commit();

            // La imagen anterior se borra solo cuando el cambio ya quedó guardado.
            if ($this->currentImage && $this->currentImage !== $image) {
                Storage::disk('public')->delete($this->currentImage);
            }

            $this->dispatch('swalmodal',
                type: 'success',
                title: __('¡Completado!'),
                text: __('Actualizado con éxito'),
                redirect: route('products'),
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
