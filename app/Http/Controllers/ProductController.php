<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $productsQuery = Product::query()
            ->with(['category', 'recipes.ingredient'])
            ->when($request->search, function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                });
            });

        if ($request->filled('category')) {
            $productsQuery->whereHas('category', fn ($query) => $query->where('name', $request->string('category')));
        }

        $products = (clone $productsQuery)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('products.index', [
            'products' => $products,
            'categories' => Category::query()->orderBy('name')->pluck('name'),
            'activeCategory' => (string) $request->string('category'),
            'totalActive' => Product::query()->count(),
            'lowStockCount' => Product::query()->with('recipes.ingredient')->get()->filter->isLowStock()->count(),
            'inventoryValue' => Product::query()->where('product_type', Product::TYPE_STOCK)->get()->sum(fn (Product $product) => $product->selling_price * $product->stock),
        ]);
    }

    public function create(): View
    {
        return view('products.form', [
            'product' => new Product,
            'categories' => Category::query()->orderBy('name')->get(),
            'ingredients' => Product::query()
                ->where('product_type', Product::TYPE_STOCK)
                ->orderBy('name')
                ->get(),
            'action' => route('products.store'),
            'method' => 'POST',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (! $request->user()->tenant?->canAddProduct()) {
            return back()
                ->withErrors(['plan' => 'Batas produk paket Anda sudah tercapai.'])
                ->withInput();
        }

        $data = $this->validated($request);
        $data['store_id'] = $this->currentStoreId();
        $data['category_id'] = $this->categoryId($request);
        $data['product_type'] = $data['product_type'] ?? Product::TYPE_STOCK;

        if ($data['product_type'] === Product::TYPE_MENU) {
            $data['stock'] = 0;
        }

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('products', 'public');
        }

        $product = Product::query()->create($data);
        $this->syncRecipe($request, $product);
        
        \App\Support\ActivityLogger::log('create_product', $product);

        return redirect()->route('products.index')->with('status', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product): View
    {
        return view('products.form', [
            'product' => $product->load('recipes.ingredient'),
            'categories' => Category::query()->orderBy('name')->get(),
            'ingredients' => Product::query()
                ->where('product_type', Product::TYPE_STOCK)
                ->whereKeyNot($product->id)
                ->orderBy('name')
                ->get(),
            'action' => route('products.update', $product),
            'method' => 'PUT',
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $this->validated($request);
        $data['category_id'] = $this->categoryId($request);
        $data['product_type'] = $data['product_type'] ?? Product::TYPE_STOCK;

        if ($data['product_type'] === Product::TYPE_MENU) {
            $data['stock'] = 0;
        }

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('products', 'public');
        }

        $product->update($data);
        $this->syncRecipe($request, $product);
        
        \App\Support\ActivityLogger::log('update_product', $product);

        return redirect()->route('products.index')->with('status', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        \App\Support\ActivityLogger::log('delete_product', $product);
        $product->delete();

        return redirect()->route('products.index')->with('status', 'Produk berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'sku' => ['nullable', 'string', 'max:80'],
            'product_type' => ['nullable', Rule::in([Product::TYPE_STOCK, Product::TYPE_MENU])],
            'unit' => ['required', 'string', 'max:30'],
            'cost_price' => ['required', 'integer', 'min:0'],
            'selling_price' => ['required', 'integer', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')->where('tenant_id', $request->user()->tenant_id)],
            'category_name' => ['nullable', 'string', 'max:80'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'recipe' => ['nullable', 'array'],
            'recipe.*.ingredient_product_id' => ['nullable', 'integer', Rule::exists('products', 'id')->where('tenant_id', $request->user()->tenant_id)],
            'recipe.*.quantity' => ['nullable', 'integer', 'min:1'],
        ]);
    }

    private function syncRecipe(Request $request, Product $product): void
    {
        $product->recipes()->delete();

        if (! $product->isMenu()) {
            return;
        }

        $recipes = collect($request->input('recipe', []))
            ->map(fn (array $item) => [
                'ingredient_product_id' => (int) ($item['ingredient_product_id'] ?? 0),
                'quantity' => (int) ($item['quantity'] ?? 0),
            ])
            ->filter(fn (array $item) => $item['ingredient_product_id'] > 0 && $item['quantity'] > 0)
            ->unique('ingredient_product_id')
            ->values();

        foreach ($recipes as $recipe) {
            $product->recipes()->create([
                'tenant_id' => $product->tenant_id,
                'ingredient_product_id' => $recipe['ingredient_product_id'],
                'quantity' => $recipe['quantity'],
            ]);
        }
    }

    private function categoryId(Request $request): ?int
    {
        if ($request->filled('category_id')) {
            return (int) $request->category_id;
        }

        if (! $request->filled('category_name')) {
            return null;
        }

        return Category::withoutGlobalScopes()->firstOrCreate(
            [
                'tenant_id' => $request->user()->tenant_id,
                'name' => trim((string) $request->category_name),
            ],
        )->id;
    }

    private function currentStoreId(): ?int
    {
        $sessionStoreId = session('store_id');

        if ($sessionStoreId && Store::query()->whereKey($sessionStoreId)->exists()) {
            return (int) $sessionStoreId;
        }

        return Store::query()->where('is_default', true)->value('id')
            ?? Store::query()->value('id');
    }
}
