<?php

namespace App\Livewire\Ingredients;

use App\Livewire\Concerns\AuthorizesPermissions;
use App\Livewire\Concerns\ReportsErrors;
use App\Livewire\Concerns\WithSorting;
use App\Models\Ingredient;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Ingredientes')]
class IngredientsTable extends Component
{
    use AuthorizesPermissions, ReportsErrors, WithPagination, WithSorting;

    public $search = '';

    protected function sortableColumns(): array
    {
        return ['name', 'extra_price', 'extra_for_products_count'];
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
        return view('livewire.ingredients.ingredients-table', [
            'ingredients' => $this->applySorting(Ingredient::withCount('extraForProducts')
                ->where('name', 'LIKE', "%{$this->search}%"))
                ->paginate(10),
        ]);
    }

    public function edit(Ingredient $ingredient)
    {
        $this->authorizePermission('edit ingredients');

        $this->redirectRoute('ingredients.edit', $ingredient);
    }

    /**
     * Activa o desactiva el ingrediente: desactivado deja de ofrecerse como extra.
     */
    public function toggleActive($id)
    {
        $this->authorizePermission('edit ingredients');

        $ingredient = Ingredient::findOrFail($id);
        $ingredient->update(['is_active' => ! $ingredient->is_active]);
    }

    /**
     * Un ingrediente usado en productos o en ventas no se elimina (la base no lo permite y
     * las ventas lo necesitan para el historial). Se avisa y se sugiere desactivarlo.
     */
    protected function cannotDelete(Ingredient $ingredient): ?string
    {
        if ($ingredient->products()->exists() || $ingredient->extraForProducts()->exists()) {
            return __(':name se usa en productos. Quítalo de esos productos o desactívalo.', ['name' => $ingredient->name]);
        }

        if ($ingredient->saleItemIngredients()->exists()) {
            return __(':name aparece en ventas registradas. Desactívalo en lugar de eliminarlo.', ['name' => $ingredient->name]);
        }

        return null;
    }

    protected function notAllowed(string $reason): void
    {
        $this->dispatch('swalmodal',
            type: 'warning',
            title: __('No se puede eliminar'),
            text: $reason,
        );
    }

    public function destroyConfirm($id)
    {
        $this->authorizePermission('delete ingredients');

        if ($reason = $this->cannotDelete(Ingredient::findOrFail($id))) {
            $this->notAllowed($reason);

            return;
        }

        $this->dispatch('swalConfirm',
            type: 'error',
            title: __('¿Está seguro de eliminar este ingrediente?'),
            text: __('¡No podrás revertir tu decisión!'),
            id: $id,
        );
    }

    #[On('destroy')]
    public function destroy($id)
    {
        $this->authorizePermission('delete ingredients');

        // destroy se puede llamar sin pasar por destroyConfirm, así que se revisa otra vez.
        $ingredient = Ingredient::findOrFail($id);

        if ($reason = $this->cannotDelete($ingredient)) {
            $this->notAllowed($reason);

            return;
        }

        try {
            DB::beginTransaction();

            $ingredient->delete();

            DB::commit();

            $this->dispatch('swalmodal',
                type: 'success',
                title: __('¡Eliminado!'),
                text: __('El ingrediente fue eliminado con éxito'),
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
