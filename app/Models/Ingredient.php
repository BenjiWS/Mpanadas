<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'description', 'extra_price', 'is_active'])]
class Ingredient extends Model
{
    protected function casts(): array
    {
        return [
            'extra_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Productos que lo llevan por defecto.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)->withPivot('is_removable');
    }

    /**
     * Productos a los que se puede agregar como extra.
     */
    public function extraForProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_extras');
    }

    /**
     * Precio que se cobra al agregarlo como extra. Cargado desde $product->extras, usa el
     * precio propio de ese producto si tiene uno; si no, el extra_price general.
     */
    public function priceAsExtra(): string
    {
        // El pivot no tiene cast: se normaliza a 2 decimales como extra_price.
        return number_format((float) ($this->pivot?->price ?? $this->extra_price), 2, '.', '');
    }

    public function saleItemIngredients(): HasMany
    {
        return $this->hasMany(SaleItemIngredient::class);
    }

    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
