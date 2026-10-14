<?php

namespace App\Livewire;

use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Vista de ejemplo de combos.
 * Los datos viven en memoria (sin base de datos) para poder revisar el diseño.
 */
#[Title('Combos')]
class Combos extends Component
{
    private const PER_PAGE = 6;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    public int $page = 1;

    /** IDs de los combos marcados para descartar */
    public array $seleccionados = [];

    public array $combos = [];

    public function mount(): void
    {
        $this->combos = [
            ['id' => 1, 'nombre' => 'Combo clásico', 'contenido' => ['3 empanadas de carne', '1 bebida 500 ml'], 'precio' => 5.04, 'suelto' => 6.00],
            ['id' => 2, 'nombre' => 'Para dos', 'contenido' => ['6 empanadas a elección', '2 bebidas 500 ml'], 'precio' => 10.50, 'suelto' => 12.40],
            ['id' => 3, 'nombre' => 'Docena surtida', 'contenido' => ['12 empanadas a elección'], 'precio' => 24.90, 'suelto' => 28.20],
            ['id' => 4, 'nombre' => 'Veggie', 'contenido' => ['2 humitas', '2 caprese', '1 limonada'], 'precio' => 9.80, 'suelto' => 11.30],
            ['id' => 5, 'nombre' => 'Picante', 'contenido' => ['4 carne picante', '1 cerveza'], 'precio' => 11.25, 'suelto' => 12.75],
            ['id' => 6, 'nombre' => 'Merienda dulce', 'contenido' => ['3 dulce de leche', '1 café'], 'precio' => 6.40, 'suelto' => 7.50],
            ['id' => 7, 'nombre' => 'Familiar', 'contenido' => ['18 empanadas a elección', '1 gaseosa 2,25 L'], 'precio' => 37.60, 'suelto' => 43.10],
            ['id' => 8, 'nombre' => 'Almuerzo rápido', 'contenido' => ['2 pollo', '1 agua 500 ml'], 'precio' => 5.90, 'suelto' => 6.50],
            ['id' => 9, 'nombre' => 'Clásicas', 'contenido' => ['3 jamón y queso', '3 carne'], 'precio' => 13.20, 'suelto' => 14.25],
            ['id' => 10, 'nombre' => 'Oficina', 'contenido' => ['24 empanadas a elección', '4 bebidas'], 'precio' => 52.00, 'suelto' => 61.80],
            ['id' => 11, 'nombre' => 'Mitad y mitad', 'contenido' => ['3 pollo', '3 espinaca y ricota'], 'precio' => 13.70, 'suelto' => 14.70],
            ['id' => 12, 'nombre' => 'Postre', 'contenido' => ['2 membrillo y queso', '2 dulce de leche'], 'precio' => 7.35, 'suelto' => 8.30],
            ['id' => 13, 'nombre' => 'Partido', 'contenido' => ['12 carne cortada a cuchillo', '2 cervezas'], 'precio' => 39.90, 'suelto' => 45.20],
            ['id' => 14, 'nombre' => 'Individual', 'contenido' => ['2 empanadas a elección', '1 bebida 500 ml'], 'precio' => 4.60, 'suelto' => 5.25],
            ['id' => 15, 'nombre' => 'Cumpleaños', 'contenido' => ['36 empanadas a elección', '3 gaseosas 2,25 L'], 'precio' => 74.50, 'suelto' => 86.40],
            ['id' => 16, 'nombre' => 'Noche de pelis', 'contenido' => ['8 empanadas a elección', '2 gaseosas 500 ml'], 'precio' => 18.40, 'suelto' => 20.90],
            ['id' => 17, 'nombre' => 'Sin carne', 'contenido' => ['3 humitas', '3 caprese'], 'precio' => 13.30, 'suelto' => 15.00],
            ['id' => 18, 'nombre' => 'Desayuno', 'contenido' => ['2 jamón y queso', '1 café con leche'], 'precio' => 5.70, 'suelto' => 6.35],
            ['id' => 19, 'nombre' => 'Degustación', 'contenido' => ['1 de cada sabor (12)'], 'precio' => 27.10, 'suelto' => 30.95],
            ['id' => 20, 'nombre' => 'Pollo doble', 'contenido' => ['4 pollo', '2 pollo al verdeo'], 'precio' => 14.90, 'suelto' => 15.60],
        ];
    }

    public function updatedSearch(): void
    {
        $this->page = 1;
    }

    #[Computed]
    public function filtrados(): array
    {
        $search = mb_strtolower(trim($this->search));

        return collect($this->combos)
            ->when($search !== '', fn ($c) => $c->filter(fn ($combo) => str_contains(
                mb_strtolower($combo['nombre'].' '.implode(' ', $combo['contenido'])), $search
            )))
            ->values()
            ->all();
    }

    #[Computed]
    public function totalPaginas(): int
    {
        return max(1, (int) ceil(count($this->filtrados) / self::PER_PAGE));
    }

    #[Computed]
    public function pagina(): array
    {
        $page = min($this->page, $this->totalPaginas);

        return array_slice($this->filtrados, ($page - 1) * self::PER_PAGE, self::PER_PAGE);
    }

    public function irAPagina(int $page): void
    {
        $this->page = max(1, min($page, $this->totalPaginas));
    }

    public function toggleSeleccion(int $id): void
    {
        $this->seleccionados = in_array($id, $this->seleccionados)
            ? array_values(array_diff($this->seleccionados, [$id]))
            : [...$this->seleccionados, $id];
    }

    public function descartar(): void
    {
        $this->combos = array_values(array_filter($this->combos, fn ($c) => ! in_array($c['id'], $this->seleccionados)));
        $this->seleccionados = [];
        unset($this->filtrados, $this->totalPaginas, $this->pagina);
        $this->page = min($this->page, $this->totalPaginas);
    }

    public function render()
    {
        return view('livewire.combos');
    }
}
