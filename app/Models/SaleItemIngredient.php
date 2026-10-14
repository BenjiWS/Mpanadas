<?php

namespace App\Models;

use App\Enums\IngredientChange;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['sale_item_id', 'ingredient_id', 'type', 'price'])]
class SaleItemIngredient extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'type' => IngredientChange::class,
            'price' => 'decimal:2',
        ];
    }

    public function saleItem(): BelongsTo
    {
        return $this->belongsTo(SaleItem::class);
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }
}
