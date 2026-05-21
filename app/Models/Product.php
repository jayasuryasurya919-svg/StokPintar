<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use BelongsToTenant;

    public const TYPE_STOCK = 'stock';
    public const TYPE_MENU = 'menu';

    protected $fillable = [
        'tenant_id',
        'store_id',
        'category_id',
        'name',
        'sku',
        'product_type',
        'unit',
        'cost_price',
        'selling_price',
        'stock',
        'minimum_stock',
        'photo_path',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function stockMutations(): HasMany
    {
        return $this->hasMany(StockMutation::class);
    }

    public function recipes(): HasMany
    {
        return $this->hasMany(ProductRecipe::class, 'menu_product_id');
    }

    public function usedInRecipes(): HasMany
    {
        return $this->hasMany(ProductRecipe::class, 'ingredient_product_id');
    }

    public function isMenu(): bool
    {
        return $this->product_type === self::TYPE_MENU;
    }

    public function isStockProduct(): bool
    {
        return $this->product_type !== self::TYPE_MENU;
    }

    public function availableForSale(): int
    {
        if (! $this->isMenu()) {
            return max(0, (int) $this->stock);
        }

        $recipes = $this->relationLoaded('recipes')
            ? $this->recipes
            : $this->recipes()->with('ingredient')->get();

        if ($recipes->isEmpty()) {
            return 0;
        }

        return (int) $recipes
            ->filter(fn (ProductRecipe $recipe) => $recipe->quantity > 0 && $recipe->ingredient)
            ->map(fn (ProductRecipe $recipe) => intdiv(max(0, (int) $recipe->ingredient->stock), max(1, (int) $recipe->quantity)))
            ->min();
    }

    public function isLowStock(): bool
    {
        if ($this->isMenu()) {
            return $this->availableForSale() <= $this->minimum_stock;
        }

        return $this->stock <= $this->minimum_stock;
    }
}
