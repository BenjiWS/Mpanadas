<?php

namespace App\Livewire;

use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Vista de ejemplo del catálogo de productos.
 * Los datos viven en memoria (sin base de datos) para poder revisar el diseño.
 */
#[Title('Productos')]
class Productos extends Component
{
    private const PER_PAGE = 8;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $categoria = '';

    #[Url(except: '')]
    public string $estado = '';

    #[Url(except: false)]
    public bool $soloDestacados = false;

    public int $page = 1;

    public array $productos = [];

    public function mount(): void
    {
        $this->productos = [
            ['id' => 1, 'nombre' => 'Empanada de carne', 'categoria' => 'Carne', 'precio' => 2.50, 'ingredientes' => 4, 'extras' => 3, 'destacado' => true, 'activo' => true],
            ['id' => 2, 'nombre' => 'Carne picante', 'categoria' => 'Carne', 'precio' => 2.75, 'ingredientes' => 5, 'extras' => 2, 'destacado' => false, 'activo' => true],
            ['id' => 3, 'nombre' => 'Pollo', 'categoria' => 'Pollo', 'precio' => 2.50, 'ingredientes' => 4, 'extras' => 3, 'destacado' => true, 'activo' => true],
            ['id' => 4, 'nombre' => 'Jamón y queso', 'categoria' => 'Clásicas', 'precio' => 2.25, 'ingredientes' => 2, 'extras' => 2, 'destacado' => true, 'activo' => true],
            ['id' => 5, 'nombre' => 'Humita', 'categoria' => 'Vegetarianas', 'precio' => 2.50, 'ingredientes' => 3, 'extras' => 1, 'destacado' => false, 'activo' => true],
            ['id' => 6, 'nombre' => 'Caprese', 'categoria' => 'Vegetarianas', 'precio' => 2.50, 'ingredientes' => 3, 'extras' => 2, 'destacado' => false, 'activo' => false],
            ['id' => 7, 'nombre' => 'Dulce de leche', 'categoria' => 'Dulces', 'precio' => 2.00, 'ingredientes' => 1, 'extras' => 0, 'destacado' => false, 'activo' => true],
            ['id' => 8, 'nombre' => 'Carne cortada a cuchillo', 'categoria' => 'Carne', 'precio' => 3.10, 'ingredientes' => 6, 'extras' => 2, 'destacado' => true, 'activo' => true],
            ['id' => 9, 'nombre' => 'Pollo al verdeo', 'categoria' => 'Pollo', 'precio' => 2.80, 'ingredientes' => 4, 'extras' => 1, 'destacado' => false, 'activo' => true],
            ['id' => 10, 'nombre' => 'Roquefort y nuez', 'categoria' => 'Clásicas', 'precio' => 2.90, 'ingredientes' => 3, 'extras' => 0, 'destacado' => false, 'activo' => false],
            ['id' => 11, 'nombre' => 'Espinaca y ricota', 'categoria' => 'Vegetarianas', 'precio' => 2.40, 'ingredientes' => 4, 'extras' => 2, 'destacado' => true, 'activo' => true],
            ['id' => 12, 'nombre' => 'Membrillo y queso', 'categoria' => 'Dulces', 'precio' => 2.15, 'ingredientes' => 2, 'extras' => 1, 'destacado' => false, 'activo' => true],
        ];
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'categoria', 'estado', 'soloDestacados'])) {
            $this->page = 1;
        }
    }

    #[Computed]
    public function categorias(): array
    {
        return collect($this->productos)->pluck('categoria')->unique()->sort()->values()->all();
    }

    #[Computed]
    public function resumen(): array
    {
        $productos = collect($this->productos);

        return [
            'total' => $productos->count(),
            'activos' => $productos->where('activo', true)->count(),
            'destacados' => $productos->where('destacado', true)->count(),
        ];
    }

    #[Computed]
    public function filtrados(): array
    {
        $search = mb_strtolower(trim($this->search));

        return collect($this->productos)
            ->when($search !== '', fn ($c) => $c->filter(fn ($p) => str_contains(mb_strtolower($p['nombre']), $search)))
            ->when($this->categoria !== '', fn ($c) => $c->where('categoria', $this->categoria))
            ->when($this->estado === 'activo', fn ($c) => $c->where('activo', true))
            ->when($this->estado === 'inactivo', fn ($c) => $c->where('activo', false))
            ->when($this->soloDestacados, fn ($c) => $c->where('destacado', true))
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

    public function toggleDestacado(int $id): void
    {
        $this->toggle($id, 'destacado');
    }

    public function toggleActivo(int $id): void
    {
        $this->toggle($id, 'activo');
    }

    public function eliminar(int $id): void
    {
        $this->productos = array_values(array_filter($this->productos, fn ($p) => $p['id'] !== $id));
        $this->page = min($this->page, $this->totalPaginas);
    }

    public function limpiarFiltros(): void
    {
        $this->reset('search', 'categoria', 'estado', 'soloDestacados', 'page');
    }

    private function toggle(int $id, string $campo): void
    {
        foreach ($this->productos as $i => $producto) {
            if ($producto['id'] === $id) {
                $this->productos[$i][$campo] = ! $producto[$campo];
            }
        }
    }

    public function render()
    {
        return view('livewire.productos');
    }
}
