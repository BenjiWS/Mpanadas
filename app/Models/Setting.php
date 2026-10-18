<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * Datos públicos del negocio. La tabla tiene una sola fila.
 */
#[Fillable([
    'name', 'tagline_es', 'tagline_en', 'address', 'city', 'phone', 'whatsapp',
    'email', 'instagram', 'facebook', 'hours', 'receipt_width', 'receipt_footer',
])]
class Setting extends Model
{
    /**
     * Mismos valores por defecto que la tabla, para el registro recién creado.
     */
    protected $attributes = [
        'receipt_width' => 80,
    ];

    protected function casts(): array
    {
        return [
            'hours' => 'array',
            'receipt_width' => 'integer',
        ];
    }

    /**
     * Ajustes vigentes, o null si todavía no se han guardado.
     */
    public static function current(): ?self
    {
        return static::query()->first();
    }
}
