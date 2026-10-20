<?php

namespace App\Livewire\Categories;

use App\Livewire\Concerns\AuthorizesPermissions;
use App\Livewire\Concerns\ReportsErrors;
use App\Livewire\Concerns\WithSorting;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Categorías')]
class CategoriesTable extends Component
{
    use AuthorizesPermissions, ReportsErrors, WithPagination, WithSorting;

    public $search = '';

    protected function sortableColumns(): array
    {
        return ['name', 'products_count', 'created_at'];
    }

    protected function defaultSort(): array
    {
        return ['name', 'asc'];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.categories.categories-table', [
            'categories' => $this->applySorting(Category::withCount('products')
                ->where('name', 'LIKE', "%{$this->search}%"))
                ->paginate(10),
        ]);
    }

    public function edit(Category $category)
    {
        $this->authorizePermission('edit categories');

        $this->redirectRoute('categories.edit', $category);
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
            text: __(':name tiene estos productos: :products. Muévalos primero a otra categoría.', [
                'name' => $category->name,
                'products' => $products->implode(', '),
            ]),
        );

        return true;
    }

    public function destroyConfirm($id)
    {
        $this->authorizePermission('delete categories');

        if ($this->blockedByProducts(Category::findOrFail($id))) {
            return;
        }

        $this->dispatch('swalConfirm',
            type: 'error',
            title: __('¿Está seguro de eliminar esta categoría?'),
            text: __('¡No podrás revertir tu decisión!'),
            id: $id,
        );
    }

    #[On('destroy')]
    public function destroy($id)
    {
        $this->authorizePermission('delete categories');

        // destroy se puede llamar sin pasar por destroyConfirm, así que se revisa otra vez.
        $category = Category::findOrFail($id);

        if ($this->blockedByProducts($category)) {
            return;
        }

        try {
            DB::beginTransaction();

            // Sin productos no hay nada que conservar (las relaciones se borran en cascada).
            $category->forceDelete();

            DB::commit();

            $this->dispatch('swalmodal',
                type: 'success',
                title: __('¡Eliminado!'),
                text: __('La categoría fue eliminada con éxito'),
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
