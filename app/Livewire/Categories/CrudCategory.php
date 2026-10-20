<?php

namespace App\Livewire\Categories;

use App\Livewire\Concerns\AuthorizesPermissions;
use App\Livewire\Concerns\ReportsErrors;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;

class CrudCategory extends Component
{
    use AuthorizesPermissions, ReportsErrors;

    #[Locked]
    public $action = 'store';

    public $title = 'Nueva categoría';

    public $name;

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
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => __('Debe ingresar el nombre de la categoría'),
            'name.unique' => __('La categoría ya ha sido registrada'),
        ];
    }

    public function mount(Category $category)
    {
        if ($category->exists) {
            $this->action = 'edit';
            $this->category_id = $category->id;
            $this->name = $category->name;
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

            Category::create($data);

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

            $category->update($data);

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
}
