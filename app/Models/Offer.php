<?php

namespace App\Models;

use App\Enums\DiscountType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'description', 'image', 'discount_type', 'discount_value', 'starts_at', 'ends_at', 'active'])]
class Offer extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'discount_type' => DiscountType::class,
            'discount_value' => 'decimal:2',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'active' => 'boolean',
        ];
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'offers_products')->withTimestamps();
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'offers_categories')->withTimestamps();
    }

    public function combos(): BelongsToMany
    {
        return $this->belongsToMany(Combo::class, 'offers_combos')->withTimestamps();
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * Ofertas activas cuya vigencia incluye el momento actual.
     */
    #[Scope]
    protected function current(Builder $query): void
    {
        $query->where('active', true)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now());
    }

    /**
     * Descuento por unidad que aplica esta oferta a un precio dado.
     */
    public function discountFor(float $price): float
    {
        $discount = match ($this->discount_type) {
            DiscountType::Percentage => $price * (float) $this->discount_value / 100,
            DiscountType::Fixed => (float) $this->discount_value,
        };

        return round(min($discount, $price), 2);
    }
}
