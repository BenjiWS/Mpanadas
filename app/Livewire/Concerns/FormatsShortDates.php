<?php

namespace App\Livewire\Concerns;

use Carbon\CarbonInterface;

trait FormatsShortDates
{
    /**
     * Fecha corta en español para los datos de los formularios, p. ej. "23 oct 2026"
     * (sin el punto de las abreviaturas: "oct." → "oct").
     */
    protected function shortDate(?CarbonInterface $date): string
    {
        return $date ? str_replace('.', '', $date->locale('es')->translatedFormat('j M Y')) : '';
    }
}
