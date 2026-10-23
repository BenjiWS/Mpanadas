<?php

namespace App\Livewire\Offers;

use App\Livewire\Concerns\AuthorizesPermissions;
use App\Models\Offer;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Ofertas')]
class OffersTable extends Component
{
    use AuthorizesPermissions, WithPagination;

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
}
