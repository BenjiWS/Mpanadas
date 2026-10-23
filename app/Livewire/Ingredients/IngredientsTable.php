<?php

namespace App\Livewire\Ingredients;

use App\Livewire\Concerns\AuthorizesPermissions;
use App\Livewire\Concerns\WithSorting;
use App\Models\Ingredient;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Ingredientes')]
class IngredientsTable extends Component
{
    use AuthorizesPermissions, WithPagination, WithSorting;

    public $search = '';

    /** todos | activos | inactivos */
    public $filter = 'todos';

    protected function sortableColumns(): array
    {
        return ['name', 'extra_price', 'extra_for_products_count', 'is_active'];
    }

    protected function defaultSort(): array
    {
        return ['name', 'asc'];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function setFilter(string $filter)
    {
        $this->filter = in_array($filter, ['todos', 'activos', 'inactivos'], true) ? $filter : 'todos';
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.ingredients.ingredients-table', [
            'ingredients' => $this->applySorting(Ingredient::withCount('extraForProducts')
                ->where(fn ($query) => $query->where('name', 'LIKE', "%{$this->search}%")
                    ->orWhere('description', 'LIKE', "%{$this->search}%"))
                ->when($this->filter === 'activos', fn ($query) => $query->where('is_active', true))
                ->when($this->filter === 'inactivos', fn ($query) => $query->where('is_active', false)))
                ->paginate(10),
            'stats' => [
                'ingredients' => Ingredient::count(),
                'extras' => Ingredient::has('extraForProducts')->count(),
                'active' => Ingredient::where('is_active', true)->count(),
            ],
        ]);
    }

    public function edit(Ingredient $ingredient)
    {
        $this->authorizePermission('edit ingredients');

        $this->redirectRoute('ingredients.edit', $ingredient);
    }
}
