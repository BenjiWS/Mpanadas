<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name'])]
class Category extends Model
{
    use SoftDeletes;

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'categories_products')->withTimestamps();
    }

    public function offers(): BelongsToMany
    {
        return $this->belongsToMany(Offer::class, 'offers_categories')->withTimestamps();
    }
}
