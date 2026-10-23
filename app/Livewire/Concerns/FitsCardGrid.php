<?php

namespace App\Livewire\Concerns;

trait FitsCardGrid
{
    /**
     * Tarjetas por página para el grid repeat(auto-fill, minmax($minCardPx, 1fr)) con $gapPx de separación:
     * columnas × 2 filas, o 6 si entra una sola columna. Es la misma cuenta que hace la vista con Alpine,
     * pero con el ancho del contenido que guarda el navegador en la cookie grid_width (layouts/app.blade.php),
     * así la primera carga ya trae la cantidad justa y el paginador no aparece y desaparece.
     */
    protected function cardsPerPage(int $minCardPx, int $gapPx, int $default = 6): int
    {
        $width = (int) request()->cookie('grid_width');

        if ($width <= 0) {
            return $default;
        }

        // Por debajo de sm (640px de pantalla, ~608px de contenido) el grid es de una columna.
        $columns = $width < 608 ? 1 : max(1, intdiv($width + $gapPx, $minCardPx + $gapPx));

        return $columns === 1 ? 6 : min(40, $columns * 2);
    }
}
