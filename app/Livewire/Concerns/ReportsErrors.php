<?php

namespace App\Livewire\Concerns;

use Throwable;

trait ReportsErrors
{
    /**
     * Registra el error en el log y devuelve un mensaje genérico para el usuario:
     * el mensaje original puede incluir SQL o rutas internas.
     */
    protected function errorMessage(Throwable $e): string
    {
        report($e);

        return __('Ocurrió un error inesperado. Intenta de nuevo y, si continúa, avisa al administrador.');
    }
}
