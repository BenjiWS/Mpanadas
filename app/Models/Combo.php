<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'description', 'image', 'price', 'is_active'])]
class Combo extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Productos que trae el combo; el pivot quantity indica cuántas unidades de cada uno.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'combos_products')->withPivot('quantity')->withTimestamps();
    }

    public function offers(): BelongsToMany
    {
        return $this->belongsToMany(Offer::class, 'offers_combos')->withTimestamps();
    }

    /**
     * Resumen de lo que trae, p. ej. "3 Empanada de carne + 1 Gaseosa".
     */
    public function contents(): string
    {
        return $this->products
            ->map(fn (Product $product) => $product->pivot->quantity.' '.$product->name)
            ->implode(' + ');
    }

    /**
     * Combos activos y sin ningún producto agotado.
     */
    #[Scope]
    protected function available(Builder $query): void
    {
        $query->where('is_active', true)
            ->whereDoesntHave('products', fn (Builder $product) => $product->where('available', false));
    }
}
