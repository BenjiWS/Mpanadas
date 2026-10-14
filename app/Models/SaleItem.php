<?php

namespace App\Models;

use App\Enums\IngredientChange;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['sale_id', 'product_id', 'offer_id', 'quantity', 'unit_price', 'extras_price', 'discount', 'subtotal', 'notes'])]
class SaleItem extends Model
{
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'extras_price' => 'decimal:2',
            'discount' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    /**
     * Ingredientes quitados y agregados en esta línea.
     */
    public function ingredientChanges(): HasMany
    {
        return $this->hasMany(SaleItemIngredient::class);
    }

    public function removedIngredients(): HasMany
    {
        return $this->ingredientChanges()->where('type', IngredientChange::Removed);
    }

    public function addedIngredients(): HasMany
    {
        return $this->ingredientChanges()->where('type', IngredientChange::Added);
    }
}
