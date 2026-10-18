<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'price', 'image', 'available'])]
class Product extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'available' => 'boolean',
        ];
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'categories_products')->withTimestamps();
    }

    /**
     * Ingredientes que lleva por defecto; el pivot indica si se pueden quitar.
     */
    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class)->withPivot('is_removable');
    }

    /**
     * Ingredientes extra que se le pueden agregar.
     */
    public function extras(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class, 'product_extras');
    }

    public function combos(): BelongsToMany
    {
        return $this->belongsToMany(Combo::class, 'combos_products')->withTimestamps();
    }

    public function offers(): BelongsToMany
    {
        return $this->belongsToMany(Offer::class, 'offers_products')->withTimestamps();
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * Productos que no están agotados.
     */
    #[Scope]
    protected function available(Builder $query): void
    {
        $query->where('available', true);
    }
}
