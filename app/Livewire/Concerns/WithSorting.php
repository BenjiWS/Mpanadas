<?php

namespace App\Livewire\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Ordenamiento por columna para las tablas de Livewire.
 *
 * El componente define qué columnas se pueden ordenar (sortableColumns)
 * y el orden inicial (defaultSort). Se usa junto con <x-sortable-th>.
 */
trait WithSorting
{
    public string $sortField = '';

    public string $sortDirection = 'asc';

    /**
     * Columnas permitidas para ordenar.
     *
     * @return array<int, string>
     */
    abstract protected function sortableColumns(): array;

    /**
     * Orden inicial de la tabla: [columna, 'asc'|'desc'].
     *
     * @return array{0: string, 1: string}
     */
    abstract protected function defaultSort(): array;

    public function mountWithSorting(): void
    {
        [$this->sortField, $this->sortDirection] = $this->defaultSort();
    }

    public function sortBy(string $field): void
    {
        if (! in_array($field, $this->sortableColumns(), true)) {
            return;
        }

        $this->sortDirection = $this->sortField === $field && $this->sortDirection === 'asc' ? 'desc' : 'asc';
        $this->sortField = $field;

        if (method_exists($this, 'resetPage')) {
            $this->resetPage();
        }
    }

    protected function applySorting(Builder $query): Builder
    {
        [$field, $direction] = in_array($this->sortField, $this->sortableColumns(), true)
            ? [$this->sortField, $this->sortDirection]
            : $this->defaultSort();

        return $query
            ->orderBy($field, $direction === 'desc' ? 'desc' : 'asc')
            // Desempate para que el orden sea estable entre páginas.
            ->orderBy($query->getModel()->getQualifiedKeyName(), $direction === 'desc' ? 'desc' : 'asc');
    }
}
