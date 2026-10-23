<?php

namespace App\Livewire\Categories;

use App\Livewire\Concerns\AuthorizesPermissions;
use App\Livewire\Concerns\ReportsErrors;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class CrudCategory extends Component
{
    use AuthorizesPermissions, ReportsErrors;

    #[Locked]
    public $action = 'store';

    public $title = 'Nueva categoría';

    public $name;

    public $description;

    public $is_active = true;

    #[Locked]
    public $category_id;

    protected function rules()
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->whereNull('deleted_at')->ignore($this->category_id),
            ],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => __('Debe ingresar el nombre de la categoría'),
            'name.unique' => __('La categoría ya ha sido registrada'),
            'description.max' => __('La descripción no puede pasar de 255 caracteres'),
        ];
    }

    public function mount(Category $category)
    {
        if ($category->exists) {
            $this->action = 'edit';
            $this->category_id = $category->id;
            $this->name = $category->name;
            $this->description = $category->description;
            $this->is_active = $category->is_active ?? true;
            $this->title = __('Edición de :name', ['name' => $category->name]);
        }
    }

    public function render()
    {
        return view('livewire.categories.crud-category')->title($this->title);
    }

    public function store()
    {
        $this->authorizePermission('create categories');

        $data = $this->validate();

        try {
            DB::beginTransaction();

            Category::create([...$data, 'description' => $data['description'] ?: null]);

            DB::commit();

            $this->dispatch('swalmodal',
                type: 'success',
                title: __('¡Completado!'),
                text: __('Categoría creada con éxito'),
                redirect: route('categories'),
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
        $this->authorizePermission('edit categories');

        // Se usa la categoría que se abrió en mount(), no un id enviado desde el navegador.
        $category = Category::findOrFail($this->category_id);

        $data = $this->validate();

        try {
            DB::beginTransaction();

            $category->update([...$data, 'description' => $data['description'] ?: null]);

            DB::commit();

            $this->dispatch('swalmodal',
                type: 'success',
                title: __('¡Completado!'),
                text: __('Actualizado con éxito'),
                redirect: route('categories'),
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

    /**
     * Una categoría con productos no se elimina: la carta se arma por categorías,
     * así que sus productos dejarían de mostrarse. Avisa cuáles son y devuelve true si se bloqueó.
     */
    protected function blockedByProducts(Category $category): bool
    {
        $products = $category->products()->orderBy('name')->pluck('name');

        if ($products->isEmpty()) {
            return false;
        }

        $this->dispatch('swalmodal',
            type: 'warning',
            title: __('No se puede eliminar'),
            text: __(':name tiene estos productos: :products. Muévalos primero a otra categoría o desactívela.', [
                'name' => $category->name,
                'products' => $products->implode(', '),
            ]),
        );

        return true;
    }

    public function destroyConfirm()
    {
        $this->authorizePermission('delete categories');

        if ($this->blockedByProducts(Category::findOrFail($this->category_id))) {
            return;
        }

        $this->dispatch('swalConfirm',
            type: 'error',
            title: __('¿Está seguro de eliminar esta categoría?'),
            text: __('¡No podrás revertir tu decisión!'),
            id: $this->category_id,
        );
    }

    #[On('destroy')]
    public function destroy()
    {
        $this->authorizePermission('delete categories');

        // Se elimina la categoría abierta en mount(), no un id enviado desde el navegador;
        // destroy se puede llamar sin pasar por destroyConfirm, así que se revisa otra vez.
        $category = Category::findOrFail($this->category_id);

        if ($this->blockedByProducts($category)) {
            return;
        }

        try {
            // Sin productos no hay nada que conservar (las relaciones se borran en cascada).
            $category->forceDelete();

            $this->dispatch('swalmodal',
                type: 'success',
                title: __('¡Eliminado!'),
                text: __('La categoría fue eliminada con éxito'),
                redirect: route('categories'),
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
