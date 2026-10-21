<?php

namespace App\Livewire\Ingredients;

use App\Livewire\Concerns\AuthorizesPermissions;
use App\Livewire\Concerns\ReportsErrors;
use App\Models\Ingredient;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;

class CrudIngredient extends Component
{
    use AuthorizesPermissions, ReportsErrors;

    #[Locked]
    public $action = 'store';

    public $title = 'Nuevo ingrediente';

    public $name;

    public $description;

    public $extra_price;

    public $is_active = true;

    #[Locked]
    public $ingredient_id;

    protected function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('ingredients', 'name')->ignore($this->ingredient_id)],
            'description' => ['nullable', 'string', 'max:1000'],
            'extra_price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'is_active' => ['boolean'],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => __('Debe ingresar el nombre del ingrediente'),
            'name.unique' => __('El ingrediente ya ha sido registrado'),
            'extra_price.required' => __('Debe ingresar el precio como extra'),
            'extra_price.numeric' => __('El precio debe ser un número'),
            'extra_price.min' => __('El precio no puede ser negativo'),
            'extra_price.max' => __('El precio es demasiado alto'),
        ];
    }

    public function mount(Ingredient $ingredient)
    {
        if ($ingredient->exists) {
            $this->action = 'edit';
            $this->ingredient_id = $ingredient->id;
            $this->name = $ingredient->name;
            $this->description = $ingredient->description;
            $this->extra_price = $ingredient->extra_price;
            $this->is_active = $ingredient->is_active ?? true;
            $this->title = __('Edición de :name', ['name' => $ingredient->name]);
        }
    }

    public function render()
    {
        return view('livewire.ingredients.crud-ingredient')->title($this->title);
    }

    public function store()
    {
        $this->authorizePermission('create ingredients');

        $data = $this->validate();

        try {
            DB::beginTransaction();

            Ingredient::create($data);

            DB::commit();

            $this->dispatch('swalmodal',
                type: 'success',
                title: __('¡Completado!'),
                text: __('Ingrediente creado con éxito'),
                redirect: route('ingredients'),
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
        $this->authorizePermission('edit ingredients');

        // Se usa el ingrediente que se abrió en mount(), no un id enviado desde el navegador.
        $ingredient = Ingredient::findOrFail($this->ingredient_id);

        $data = $this->validate();

        try {
            DB::beginTransaction();

            $ingredient->update($data);

            DB::commit();

            $this->dispatch('swalmodal',
                type: 'success',
                title: __('¡Completado!'),
                text: __('Actualizado con éxito'),
                redirect: route('ingredients'),
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
