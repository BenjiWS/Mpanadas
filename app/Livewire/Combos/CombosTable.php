<?php

namespace App\Livewire\Combos;

use App\Livewire\Concerns\AuthorizesPermissions;
use App\Livewire\Concerns\ReportsErrors;
use App\Models\Combo;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Combos')]
class CombosTable extends Component
{
    use AuthorizesPermissions, ReportsErrors, WithPagination;

    public $search = '';

    /** todos | activos | inactivos */
    public $filter = 'todos';

    /**
     * Combos por página. La vista lo ajusta a columnas × 2 filas según cuántas
     * tarjetas caben a lo ancho: la página cabe en pantalla y no queda con huecos.
     */
    public $perPage = 6;

    public function updatedPerPage()
    {
        $this->perPage = max(2, min(40, (int) $this->perPage));
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
        return view('livewire.combos.combos-table', [
            'combos' => Combo::with(['products' => fn ($query) => $query->orderBy('name')])
                ->where(function ($query) {
                    $query->where('name', 'LIKE', "%{$this->search}%")
                        ->orWhere('description', 'LIKE', "%{$this->search}%")
                        ->orWhereHas('products', function ($q) {
                            $q->where('name', 'LIKE', "%{$this->search}%");
                        });
                })
                ->when($this->filter === 'activos', fn ($query) => $query->where('is_active', true))
                ->when($this->filter === 'inactivos', fn ($query) => $query->where('is_active', false))
                ->orderBy('name')
                ->paginate($this->perPage),
        ]);
    }

    public function edit(Combo $combo)
    {
        $this->authorizePermission('edit combos');

        $this->redirectRoute('combos.edit', $combo);
    }

    /**
     * Saca el combo de la carta o lo vuelve a ofrecer sin borrarlo.
     */
    public function toggleActive($id)
    {
        $this->authorizePermission('edit combos');

        $combo = Combo::findOrFail($id);
        $combo->update(['is_active' => ! $combo->is_active]);
    }

    public function destroyConfirm($id)
    {
        $this->authorizePermission('delete combos');

        $this->dispatch('swalConfirm',
            type: 'error',
            title: __('¿Está seguro de eliminar este combo?'),
            text: __('¡No podrás revertir tu decisión!'),
            id: $id,
        );
    }

    #[On('destroy')]
    public function destroy($id)
    {
        $this->authorizePermission('delete combos');

        $combo = Combo::findOrFail($id);

        try {
            DB::beginTransaction();

            // Soft delete: los productos que trae se conservan para poder consultarlo después,
            // pero ya no cuenta como combo al intentar eliminar uno de esos productos.
            $combo->delete();

            DB::commit();

            $this->dispatch('swalmodal',
                type: 'success',
                title: __('¡Eliminado!'),
                text: __('El combo fue eliminado con éxito'),
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
