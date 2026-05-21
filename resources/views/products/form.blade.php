@extends('layouts.app')

@section('title', ($product->exists ? 'Edit Produk' : 'Tambah Produk').' - StokPintar')

@section('content')
@php
    $recipeRows = collect(old('recipe', $product->exists
        ? $product->recipes->map(fn ($recipe) => [
            'ingredient_product_id' => $recipe->ingredient_product_id,
            'quantity' => $recipe->quantity,
        ])->all()
        : []
    ));

    while ($recipeRows->count() < 4) {
        $recipeRows->push(['ingredient_product_id' => '', 'quantity' => 1]);
    }
@endphp
    <header class="topbar">
        <div>
            <h1>{{ $product->exists ? 'Edit Produk' : 'Tambah Produk Baru' }}</h1>
            <p class="subtitle">Pakai barang stok untuk retail, atau menu/racikan untuk F&B yang mengurangi bahan baku otomatis.</p>
        </div>
        <div class="actions">
            <a class="btn" href="{{ route('products.index') }}"><span class="material-symbols-outlined">arrow_back</span> Kembali</a>
        </div>
    </header>

    @if($errors->any())
        <div class="alert error" style="margin-bottom:16px">
            <strong>Terjadi kesalahan:</strong>
            <ul style="margin:6px 0 0;padding-left:20px">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="card form-shell">
        <form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="stack">
            @csrf
            @if($method === 'PUT')
                @method('PUT')
            @endif

            <div class="form-grid">
                <div class="field">
                    <label for="name">Nama Produk *</label>
                    <input id="name" name="name" value="{{ old('name', $product->name) }}" placeholder="Contoh: Kopi Susu Botol 350ml" required>
                </div>
                <div class="field">
                    <label for="sku">SKU / Kode Barcode</label>
                    <input id="sku" name="sku" value="{{ old('sku', $product->sku) }}" placeholder="Contoh: KSB-001 (opsional)">
                </div>
                <div class="field">
                    <label for="product_type">Tipe Produk *</label>
                    <select id="product_type" name="product_type" data-product-type required>
                        <option value="stock" @selected(old('product_type', $product->product_type ?: 'stock') === 'stock')>Barang Stok</option>
                        <option value="menu" @selected(old('product_type', $product->product_type) === 'menu')>Menu / Racikan</option>
                    </select>
                </div>
                <div class="field">
                    <label for="category_id">Kategori</label>
                    <select id="category_id" name="category_id">
                        <option value="">— Pilih kategori —</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="category_name">Atau Buat Kategori Baru</label>
                    <input id="category_name" name="category_name" value="{{ old('category_name') }}" placeholder="Ketik nama kategori baru">
                </div>
                <div class="field">
                    <label for="unit">Satuan *</label>
                    <input id="unit" name="unit" value="{{ old('unit', $product->unit ?: 'pcs') }}" placeholder="pcs, kg, liter, bungkus" required>
                </div>
                <div class="field">
                    <label for="cost_price">Harga Modal (Rp) *</label>
                    <input id="cost_price" name="cost_price" type="number" min="0" value="{{ old('cost_price', $product->cost_price ?? 0) }}" required>
                </div>
                <div class="field">
                    <label for="selling_price">Harga Jual (Rp) *</label>
                    <input id="selling_price" name="selling_price" type="number" min="0" value="{{ old('selling_price', $product->selling_price ?? 0) }}" required>
                </div>
                <div class="field">
                    <label>Margin Keuntungan</label>
                    <div id="margin-display" class="card compact" style="min-height:40px;display:flex;align-items:center;gap:8px;font-weight:700">
                        <span class="material-symbols-outlined" style="font-size:18px;color:var(--green)">trending_up</span>
                        <span id="margin-text">—</span>
                    </div>
                </div>
                <div class="field">
                    <label for="stock">Stok {{ $product->exists ? 'Saat Ini' : 'Awal' }} *</label>
                    <input id="stock" name="stock" type="number" min="0" value="{{ old('stock', $product->stock ?? 0) }}" required>
                    <span class="muted" data-menu-stock-note style="display:none;font-size:12px">Menu tidak menyimpan stok sendiri. Ketersediaan dihitung dari bahan baku.</span>
                </div>
                <div class="field">
                    <label for="minimum_stock">Stok Minimum (Alert) *</label>
                    <input id="minimum_stock" name="minimum_stock" type="number" min="0" value="{{ old('minimum_stock', $product->minimum_stock ?? 0) }}" required>
                </div>
                <div class="field" data-recipe-panel style="grid-column:1/-1;display:none">
                    <label>Resep / Komposisi Bahan</label>
                    <div class="card compact" style="display:grid;gap:10px">
                        @foreach($recipeRows as $recipeIndex => $recipe)
                            <div class="stock-form-row">
                                <div class="field">
                                    <label>Bahan</label>
                                    <select name="recipe[{{ $recipeIndex }}][ingredient_product_id]">
                                        <option value="">Pilih bahan</option>
                                        @foreach($ingredients as $ingredient)
                                            <option value="{{ $ingredient->id }}" @selected((string) ($recipe['ingredient_product_id'] ?? '') === (string) $ingredient->id)>
                                                {{ $ingredient->name }} (stok {{ $ingredient->stock }} {{ $ingredient->unit }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="field">
                                    <label>Dipakai per 1 menu</label>
                                    <input name="recipe[{{ $recipeIndex }}][quantity]" type="number" min="1" value="{{ $recipe['quantity'] ?? 1 }}">
                                </div>
                            </div>
                        @endforeach
                        <p class="muted" style="margin:0;font-size:12px">Contoh: Es Kopi Susu memakai Kopi 18 gram, Susu 150 ml, Gula Aren 30 ml, Cup 1 pcs.</p>
                    </div>
                </div>
                <div class="field" style="grid-column:1/-1">
                    <label for="photo">Foto Produk</label>
                    <input id="photo" name="photo" type="file" accept="image/*">
                    @if($product->photo_path)
                        <div style="margin-top:8px;display:flex;align-items:center;gap:12px">
                            <img src="{{ asset('storage/'.$product->photo_path) }}" alt="{{ $product->name }}" style="width:64px;height:64px;border-radius:8px;object-fit:cover;border:1px solid var(--line)">
                            <span class="muted" style="font-size:13px">Foto saat ini. Upload baru untuk mengganti.</span>
                        </div>
                    @endif
                    <div id="photo-preview" style="margin-top:8px;display:none">
                        <img id="photo-preview-img" src="" alt="Preview" style="width:80px;height:80px;border-radius:8px;object-fit:cover;border:2px solid var(--green)">
                    </div>
                </div>
            </div>

            <input type="hidden" name="is_active" value="1">
            <div class="form-actions">
                <a class="btn" href="{{ route('products.index') }}">Batal</a>
                <button class="btn primary" type="submit">
                    <span class="material-symbols-outlined">save</span>
                    {{ $product->exists ? 'Simpan Perubahan' : 'Tambah Produk' }}
                </button>
            </div>
        </form>
    </section>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Margin calculator
    const cost = document.getElementById('cost_price');
    const sell = document.getElementById('selling_price');
    const marginText = document.getElementById('margin-text');
    const productType = document.querySelector('[data-product-type]');
    const recipePanel = document.querySelector('[data-recipe-panel]');
    const stockInput = document.getElementById('stock');
    const menuStockNote = document.querySelector('[data-menu-stock-note]');
    function calcMargin() {
        const c = Number(cost.value) || 0, s = Number(sell.value) || 0;
        if (c <= 0 || s <= 0) { marginText.textContent = '—'; return; }
        const profit = s - c;
        const pct = ((profit / c) * 100).toFixed(1);
        marginText.textContent = `Rp ${profit.toLocaleString('id-ID')} per unit (${pct}%)`;
        marginText.style.color = profit >= 0 ? 'var(--green)' : 'var(--rose)';
    }
    cost.addEventListener('input', calcMargin);
    sell.addEventListener('input', calcMargin);
    calcMargin();

    function syncProductType() {
        const isMenu = productType.value === 'menu';
        recipePanel.style.display = isMenu ? 'block' : 'none';
        stockInput.readOnly = isMenu;
        if (isMenu) stockInput.value = 0;
        menuStockNote.style.display = isMenu ? 'block' : 'none';
    }
    productType.addEventListener('change', syncProductType);
    syncProductType();

    // Photo preview
    document.getElementById('photo').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const preview = document.getElementById('photo-preview');
        const img = document.getElementById('photo-preview-img');
        if (file) {
            const reader = new FileReader();
            reader.onload = ev => { img.src = ev.target.result; preview.style.display = 'block'; };
            reader.readAsDataURL(file);
        } else {
            preview.style.display = 'none';
        }
    });
});
</script>
@endsection
