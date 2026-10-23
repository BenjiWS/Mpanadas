<?php

namespace App\Livewire\Categories;

use App\Livewire\Concerns\AuthorizesPermissions;
use App\Livewire\Concerns\WithSorting;
use App\Models\Category;
use App\Models\Product;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Categorías')]
class CategoriesTable extends Component
{
    use AuthorizesPermissions, WithPagination, WithSorting;

    public $search = '';

    /** todos | activos | inactivos */
    public $filter = 'todos';

    protected function sortableColumns(): array
    {
        return ['name', 'products_count', 'is_active'];
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
        return view('livewire.categories.categories-table', [
            'categories' => $this->applySorting(Category::withCount('products')
                ->where(fn ($query) => $query->where('name', 'LIKE', "%{$this->search}%")
                    ->orWhere('description', 'LIKE', "%{$this->search}%"))
                ->when($this->filter === 'activos', fn ($query) => $query->where('is_active', true))
                ->when($this->filter === 'inactivos', fn ($query) => $query->where('is_active', false)))
                ->paginate(10),
            'stats' => [
                'categories' => Category::count(),
                'products' => Product::count(),
                'active' => Category::where('is_active', true)->count(),
            ],
        ]);
    }

    public function edit(Category $category)
    {
        $this->authorizePermission('edit categories');

        $this->redirectRoute('categories.edit', $category);
    }
}
