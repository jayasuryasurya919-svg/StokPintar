<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductRecipe extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'menu_product_id',
        'ingredient_product_id',
        'quantity',
    ];

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'menu_product_id');
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'ingredient_product_id');
    }
}
