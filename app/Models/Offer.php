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

#[Fillable(['name', 'description', 'image', 'discount_type', 'discount_value', 'starts_at', 'ends_at', 'weekdays', 'start_time', 'end_time', 'active'])]
class Offer extends Model
{
    use SoftDeletes;

    /** Días ISO (1 = lunes) con su nombre completo y abreviado */
    public const WEEKDAYS = [
        1 => ['Lunes', 'Lun'],
        2 => ['Martes', 'Mar'],
        3 => ['Miércoles', 'Mié'],
        4 => ['Jueves', 'Jue'],
        5 => ['Viernes', 'Vie'],
        6 => ['Sábado', 'Sáb'],
        7 => ['Domingo', 'Dom'],
    ];

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
     * Ofertas activas que aplican en este momento: dentro de la vigencia, en uno de sus días
     * y dentro de su franja horaria.
     */
    #[Scope]
    protected function current(Builder $query): void
    {
        $now = now();

        $query->where('active', true)
            ->where('starts_at', '<=', $now)
            ->where('ends_at', '>=', $now)
            ->where(fn (Builder $q) => $q->whereNull('weekdays')->orWhere('weekdays', 'like', '%'.$now->dayOfWeekIso.'%'))
            ->where(fn (Builder $q) => $q->whereNull('start_time')->orWhere('start_time', '<=', $now->format('H:i:s')))
            ->where(fn (Builder $q) => $q->whereNull('end_time')->orWhere('end_time', '>=', $now->format('H:i:s')));
    }

    /**
     * Activas y con la fecha de hoy dentro de la vigencia (sin mirar días ni horario).
     */
    #[Scope]
    protected function running(Builder $query): void
    {
        $query->where('active', true)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now());
    }

    #[Scope]
    protected function ended(Builder $query): void
    {
        $query->where('ends_at', '<', now());
    }

    /**
     * Descuento por unidad que aplica esta oferta a un precio dado.
     * En el 2 × 1 es la mitad del precio: una de cada dos unidades sale gratis.
     */
    public function discountFor(float $price): float
    {
        $discount = match ($this->discount_type) {
            DiscountType::Percentage => $price * (float) $this->discount_value / 100,
            DiscountType::Fixed => (float) $this->discount_value,
            DiscountType::TwoForOne => $price / 2,
        };

        return round(min($discount, $price), 2);
    }

    /**
     * El descuento como se muestra en la carta: "10%", "$ 1,50" o "2 × 1".
     */
    public function headline(): string
    {
        return match ($this->discount_type) {
            DiscountType::Percentage => rtrim(rtrim(number_format((float) $this->discount_value, 2, ',', '.'), '0'), ',').'%',
            DiscountType::Fixed => '$ '.number_format((float) $this->discount_value, 2, ',', '.'),
            DiscountType::TwoForOne => '2 × 1',
        };
    }

    /**
     * vigente | programada | finalizada | pausada
     */
    public function status(): string
    {
        return match (true) {
            ! $this->active => 'pausada',
            $this->ends_at->isPast() => 'finalizada',
            $this->starts_at->isFuture() => 'programada',
            default => 'vigente',
        };
    }

    /**
     * @return array<int, int>
     */
    public function weekdayList(): array
    {
        return $this->weekdays ? array_map('intval', explode(',', $this->weekdays)) : [];
    }

    /**
     * Días y horario, p. ej. "Lunes a viernes · 15:00–17:00".
     */
    public function scheduleLabel(): string
    {
        $parts = [static::describeWeekdays($this->weekdayList())];

        if ($this->start_time || $this->end_time) {
            $parts[] = substr($this->start_time ?? '00:00', 0, 5).'–'.substr($this->end_time ?? '23:59', 0, 5);
        }

        return implode(' · ', $parts);
    }

    /**
     * Fechas de la vigencia, p. ej. "1 oct – 31 dic 2026".
     */
    public function periodLabel(): string
    {
        $start = $this->starts_at->locale('es');
        $end = $this->ends_at->locale('es');

        // Sin el punto de las abreviaturas en español ("oct." → "oct").
        return str_replace('.', '', $start->translatedFormat($start->year === $end->year ? 'j M' : 'j M Y').' – '.$end->translatedFormat('j M Y'));
    }

    /**
     * "Todos los días", "Lunes a viernes", "Fines de semana" o la lista abreviada ("Lun, Mié, Vie").
     *
     * @param  array<int, int>  $days
     */
    public static function describeWeekdays(array $days): string
    {
        $days = array_values(array_unique(array_filter($days, fn ($day) => isset(static::WEEKDAYS[$day]))));
        sort($days);

        if ($days === [] || count($days) === 7) {
            return 'Todos los días';
        }

        if ($days === [6, 7]) {
            return 'Fines de semana';
        }

        if (count($days) === 1) {
            return static::WEEKDAYS[$days[0]][0];
        }

        // Días seguidos: "Lunes a viernes".
        if (count($days) > 2 && end($days) - $days[0] === count($days) - 1) {
            return static::WEEKDAYS[$days[0]][0].' a '.mb_strtolower(static::WEEKDAYS[end($days)][0]);
        }

        return implode(', ', array_map(fn ($day) => static::WEEKDAYS[$day][1], $days));
    }

    /**
     * A qué aplica, p. ej. "Carne · Combo clásico" o "5 productos · 2 combos".
     */
    public function targetsLabel(): string
    {
        $names = $this->categories->pluck('name')
            ->merge($this->products->pluck('name'))
            ->merge($this->combos->pluck('name'));

        if ($names->count() <= 3) {
            return $names->implode(' · ');
        }

        return collect([
            [$this->categories->count(), 'categoría', 'categorías'],
            [$this->products->count(), 'producto', 'productos'],
            [$this->combos->count(), 'combo', 'combos'],
        ])->filter(fn ($row) => $row[0] > 0)
            ->map(fn ($row) => $row[0].' '.($row[0] === 1 ? $row[1] : $row[2]))
            ->implode(' · ');
    }
}
