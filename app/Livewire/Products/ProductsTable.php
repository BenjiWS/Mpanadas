<?php

namespace App\Livewire\Products;

use App\Livewire\Concerns\AuthorizesPermissions;
use App\Livewire\Concerns\ReportsErrors;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Productos')]
class ProductsTable extends Component
{
    use AuthorizesPermissions, ReportsErrors, WithPagination;

    public $search = '';

    /** todos | activos | inactivos */
    public $filter = 'todos';

    /**
     * Productos por página. La vista lo ajusta a columnas × 3 filas según cuántas
     * tarjetas caben a lo ancho, para que las páginas no queden con huecos.
     */
    public $perPage = 9;

    public function updatedPerPage()
    {
        $this->perPage = max(3, min(60, (int) $this->perPage));
        $this->resetPage();
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
        return view('livewire.products.products-table', [
            'products' => Product::with('categories')
                ->where(function ($query) {
                    $query->where('name', 'LIKE', "%{$this->search}%")
                        ->orWhereHas('categories', function ($q) {
                            $q->where('name', 'LIKE', "%{$this->search}%");
                        });
                })
                ->when($this->filter === 'activos', fn ($query) => $query->where('available', true))
                ->when($this->filter === 'inactivos', fn ($query) => $query->where('available', false))
                ->orderBy('name')
                ->paginate($this->perPage),
        ]);
    }

    public function edit(Product $product)
    {
        $this->authorizePermission('edit products');

        $this->redirectRoute('products.edit', $product);
    }

    /**
     * Marca el producto como agotado o disponible sin borrarlo.
     */
    public function toggleAvailability($id)
    {
        $this->authorizePermission('edit products');

        $product = Product::findOrFail($id);
        $product->update(['available' => ! $product->available]);
    }

    /**
     * Un producto que forma parte de combos no se elimina: el combo seguiría a la venta al mismo
     * precio sin ese producto. Avisa en qué combos está y devuelve true si se bloqueó.
     */
    protected function blockedByCombos(Product $product): bool
    {
        $combos = $product->combos()->orderBy('name')->pluck('name');

        if ($combos->isEmpty()) {
            return false;
        }

        $this->dispatch('swalmodal',
            type: 'warning',
            title: __('No se puede eliminar'),
            text: __(':name está en estos combos: :combos. Quítelo primero de esos combos o márquelo como inactivo.', [
                'name' => $product->name,
                'combos' => $combos->implode(', '),
            ]),
        );

        return true;
    }

    public function destroyConfirm($id)
    {
        $this->authorizePermission('delete products');

        if ($this->blockedByCombos(Product::findOrFail($id))) {
            return;
        }

        $this->dispatch('swalConfirm',
            type: 'error',
            title: __('¿Está seguro de eliminar este producto?'),
            text: __('¡No podrás revertir tu decisión!'),
            id: $id,
        );
    }

    #[On('destroy')]
    public function destroy($id)
    {
        $this->authorizePermission('delete products');

        // destroy se puede llamar sin pasar por destroyConfirm, así que se revisa otra vez.
        $product = Product::findOrFail($id);

        if ($this->blockedByCombos($product)) {
            return;
        }

        try {
            DB::beginTransaction();

            // Se borra con soft delete para no romper las ventas que ya lo incluyen;
            // la relación con categorías ya no aporta nada.
            $product->categories()->detach();
            $product->delete();

            DB::commit();

            $this->dispatch('swalmodal',
                type: 'success',
                title: __('¡Eliminado!'),
                text: __('El producto fue eliminado con éxito'),
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
