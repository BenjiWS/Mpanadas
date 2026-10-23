<?php

namespace App\Livewire\Offers;

use App\Livewire\Concerns\AuthorizesPermissions;
use App\Livewire\Concerns\ReportsErrors;
use App\Models\Offer;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Ofertas')]
class OffersTable extends Component
{
    use AuthorizesPermissions, ReportsErrors, WithPagination;

    public $search = '';

    /** todas | vigentes | finalizadas */
    public $filter = 'todas';

    /**
     * Ofertas por página. La vista lo ajusta a columnas × 2 filas según cuántas
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
        $this->filter = in_array($filter, ['todas', 'vigentes', 'finalizadas'], true) ? $filter : 'todas';
        $this->resetPage();
    }

    public function render()
    {
        $search = "%{$this->search}%";

        return view('livewire.offers.offers-table', [
            'offers' => Offer::with(['categories', 'products', 'combos'])
                ->where(function ($query) use ($search) {
                    $query->where('name', 'LIKE', $search)
                        ->orWhere('description', 'LIKE', $search)
                        ->orWhereHas('categories', fn ($q) => $q->where('name', 'LIKE', $search))
                        ->orWhereHas('products', fn ($q) => $q->where('name', 'LIKE', $search))
                        ->orWhereHas('combos', fn ($q) => $q->where('name', 'LIKE', $search));
                })
                ->when($this->filter === 'vigentes', fn ($query) => $query->running())
                ->when($this->filter === 'finalizadas', fn ($query) => $query->ended())
                // Primero las que terminan antes; las finalizadas al final.
                ->orderByRaw('ends_at < ?', [now()])
                ->orderBy('ends_at')
                ->orderBy('name')
                ->paginate($this->perPage),
        ]);
    }

    public function edit(Offer $offer)
    {
        $this->authorizePermission('edit offers');

        $this->redirectRoute('offers.edit', $offer);
    }

    /**
     * Pausa la oferta o la vuelve a activar sin cambiar su vigencia.
     */
    public function toggleActive($id)
    {
        $this->authorizePermission('edit offers');

        $offer = Offer::findOrFail($id);
        $offer->update(['active' => ! $offer->active]);
    }

    public function destroyConfirm($id)
    {
        $this->authorizePermission('delete offers');

        $this->dispatch('swalConfirm',
            type: 'error',
            title: __('¿Está seguro de eliminar esta oferta?'),
            text: __('¡No podrás revertir tu decisión!'),
            id: $id,
        );
    }

    #[On('destroy')]
    public function destroy($id)
    {
        $this->authorizePermission('delete offers');

        $offer = Offer::findOrFail($id);

        try {
            DB::beginTransaction();

            // Soft delete: las ventas que la aplicaron la siguen referenciando.
            $offer->delete();

            DB::commit();

            $this->dispatch('swalmodal',
                type: 'success',
                title: __('¡Eliminado!'),
                text: __('La oferta fue eliminada con éxito'),
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
