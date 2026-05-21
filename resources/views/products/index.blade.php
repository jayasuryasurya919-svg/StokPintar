@extends('layouts.app')

@section('title', 'Produk & Stok - StokPintar')

@section('content')
    <header class="topbar">
        <div>
            <h1>Produk & Stok</h1>
            <p class="subtitle">Kelola barang retail, bahan baku, dan menu racikan dari satu daftar yang sama.</p>
        </div>
        @if(auth()->user()->canPermission('products.manage'))
            <div class="actions">
                <a href="{{ route('products.create') }}" class="btn primary"><span class="material-symbols-outlined">add</span> Tambah Produk</a>
            </div>
        @endif
    </header>

    <div class="page-stack">
        <section class="card compact">
            <form method="GET" action="{{ route('products.index') }}" class="filter-grid">
                <div class="field">
                    <label for="search">Cari Produk</label>
                    <input id="search" name="search" value="{{ request('search') }}" placeholder="Nama produk atau SKU">
                </div>
                <div class="field">
                    <label for="category">Kategori</label>
                    <select id="category" name="category">
                        <option value="">Semua kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" @selected(request('category') === $cat)>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <button class="btn primary" type="submit"><span class="material-symbols-outlined">search</span> Terapkan</button>
                </div>
            </form>
        </section>

        <section class="card flush">
            <div class="panel-header">
                <h2>Daftar Produk</h2>
                <span class="badge">{{ $products->total() }} item</span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Ketersediaan</th>
                            <th>Harga</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                            <tr>
                                <td>
                                    <div class="product-name-cell">
                                        <span class="product-thumb">{{ strtoupper(mb_substr($product->name, 0, 2)) }}</span>
                                        <div>
                                            <strong>{{ $product->name }}</strong>
                                            <div class="muted">
                                                {{ $product->sku ?: 'Tanpa SKU' }} -
                                                {{ $product->category?->name ?? 'Tanpa kategori' }} -
                                                {{ $product->isMenu() ? 'Menu/Racikan' : 'Barang Stok' }}
                                            </div>
                                            @if($product->isMenu() && $product->recipes->isNotEmpty())
                                                <div class="muted" style="font-size:12px">
                                                    Bahan: {{ $product->recipes->map(fn ($recipe) => $recipe->ingredient?->name.' '.$recipe->quantity.' '.$recipe->ingredient?->unit)->join(', ') }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($product->isMenu())
                                        <strong class="{{ $product->isLowStock() ? 'price' : '' }}">{{ $product->availableForSale() }} porsi</strong>
                                        <div class="muted">Dihitung dari stok bahan</div>
                                    @else
                                        <strong class="{{ $product->isLowStock() ? 'price' : '' }}">{{ $product->stock }} {{ $product->unit }}</strong>
                                        <div class="muted">Min. {{ $product->minimum_stock }}</div>
                                    @endif
                                </td>
                                <td>Rp {{ number_format($product->selling_price, 0, ',', '.') }}</td>
                                <td>
                                    <div class="action-row">
                                        @if(auth()->user()->canPermission('products.manage'))
                                            <a class="btn small" href="{{ route('products.edit', $product) }}">Edit</a>
                                        @endif
                                        @if(auth()->user()->canPermission('stock.mutate') && ! $product->isMenu())
                                            <details>
                                                <summary class="btn small primary">Tambah/Kurang Stok</summary>
                                                <form method="POST" action="{{ route('products.stock.store', $product) }}" class="stock-form-row" style="margin-top:8px;">
                                                    @csrf
                                                    <div class="field">
                                                        <label>Tipe</label>
                                                        <select name="type">
                                                            <option value="in">Masuk</option>
                                                            <option value="out">Keluar</option>
                                                        </select>
                                                    </div>
                                                    <div class="field">
                                                        <label>Qty</label>
                                                        <input name="quantity" type="number" min="1" value="1">
                                                    </div>
                                                    <div class="field">
                                                        <label>Catatan</label>
                                                        <input name="notes" placeholder="Opsional">
                                                    </div>
                                                    <button class="btn small primary" type="submit">Simpan</button>
                                                </form>
                                            </details>
                                        @endif
                                        @if(auth()->user()->canPermission('products.manage'))
                                            <form class="inline-form" method="POST" action="{{ route('products.destroy', $product) }}" onsubmit="return confirm('Hapus produk ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn small danger" type="submit">Hapus</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="empty-cell">Belum ada produk.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="table-footer">
                {{ $products->links() }}
            </div>
        </section>
    </div>
@endsection
