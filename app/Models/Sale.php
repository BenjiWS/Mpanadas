<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\SaleStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id', 'payment_method', 'status', 'subtotal', 'extras_total', 'discount_total', 'total',
    'amount_received', 'change_amount', 'cancellation_reason', 'cancelled_at',
])]
class Sale extends Model
{
    protected function casts(): array
    {
        return [
            'payment_method' => PaymentMethod::class,
            'status' => SaleStatus::class,
            'subtotal' => 'decimal:2',
            'extras_total' => 'decimal:2',
            'discount_total' => 'decimal:2',
            'total' => 'decimal:2',
            'amount_received' => 'decimal:2',
            'change_amount' => 'decimal:2',
            'cancelled_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }
}
