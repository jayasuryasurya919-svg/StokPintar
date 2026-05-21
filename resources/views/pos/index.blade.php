@extends('layouts.app')

@section('title', 'POS Kasir - StokPintar')

@section('content')
@php
    $defaultCart = $products->take(2)->values();
    $initialSubtotal = $defaultCart->sum(fn ($product, $index) => (int) $product->selling_price * ($index === 0 ? 2 : 1));
    $initialPaid = old('paid_amount', $initialSubtotal > 0 ? (int) ceil(($initialSubtotal + 10000) / 10000) * 10000 : 0);
@endphp

<div class="pos-workspace" data-pos-workspace>
    <header class="pos-register-bar">
        <div class="pos-search">
            <span class="material-symbols-outlined">search</span>
            <input data-pos-search placeholder="Cari produk, SKU, atau scan barcode lalu Enter..." type="text">
            <button class="btn small" data-open-barcode-scanner type="button" title="Scan barcode dengan kamera">
                <span class="material-symbols-outlined">qr_code_scanner</span>
            </button>
        </div>
        <div class="pos-register-actions">
            @if($activeStore)
                <span class="badge money">Toko: {{ $activeStore->name }}</span>
            @endif
            <div class="pos-segments" aria-label="Kategori produk">
                <button class="active" data-category-filter="all" type="button">Semua</button>
                @foreach($categories as $category)
                    <button data-category-filter="{{ $category }}" type="button">{{ $category }}</button>
                @endforeach
            </div>
            <button class="btn" type="button"><span class="material-symbols-outlined">save</span> Simpan Draft</button>
        </div>
    </header>

    <form method="POST" action="{{ route('pos.store') }}" id="pos-form" class="pos-register-body">
        @csrf
        <input type="hidden" name="payment_method" value="{{ old('payment_method', 'cash') }}" data-payment-method>
        <section class="pos-catalog">
            <div class="pos-catalog-grid">
                @forelse($products as $index => $product)
                    @php
                        $categoryName = $product->category?->name ?? '';
                        $availableStock = $product->availableForSale();
                        $defaultQuantity = min($availableStock, $index === 0 ? 2 : ($index === 1 ? 1 : 0));
                        $quantityValue = max(0, min($availableStock, (int) old("items.$index.quantity", $defaultQuantity)));
                    @endphp
                    <article
                        class="pos-product-card"
                        data-product-card
                        data-product-name="{{ strtolower($product->name.' '.$product->sku.' '.$categoryName) }}"
                        data-product-sku="{{ strtolower($product->sku ?? '') }}"
                        data-product-index="{{ $index }}"
                        data-category="{{ $categoryName }}"
                    >
                        <button
                            class="pos-product-add-area"
                            type="button"
                            data-pos-increment="{{ $index }}"
                            aria-label="Tambah {{ $product->name }}"
                        >
                            <div class="pos-product-media">
                                @if($product->photo_path)
                                    <img src="{{ asset('storage/'.$product->photo_path) }}" alt="{{ $product->name }}">
                                @else
                                    <span>{{ strtoupper(mb_substr($product->name, 0, 2)) }}</span>
                                @endif
                            </div>
                            <div class="pos-product-info">
                                <h3>{{ $product->name }}</h3>
                                <p>Rp {{ number_format($product->selling_price, 0, ',', '.') }}</p>
                            </div>
                        </button>
                        <div class="pos-product-stock">
                            @if($product->isLowStock())
                                <span class="badge low">{{ $product->isMenu() ? 'BAHAN TERBATAS' : 'STOK RENDAH' }}</span>
                            @else
                                <span>{{ $product->isMenu() ? 'Bisa dibuat' : 'Stok' }}: {{ $availableStock }} {{ $product->isMenu() ? 'porsi' : $product->unit }}</span>
                            @endif
                            <button class="pos-add-button" type="button" data-pos-increment="{{ $index }}">
                                <span class="material-symbols-outlined">add</span>
                            </button>
                        </div>
                        <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $product->id }}">
                        <input
                            data-pos-quantity="{{ $index }}"
                            data-product-label="{{ $product->name }}"
                            data-product-sku="{{ $product->sku ?: 'Tanpa SKU' }}"
                            data-product-price="{{ (int) $product->selling_price }}"
                            data-product-stock="{{ $availableStock }}"
                            name="items[{{ $index }}][quantity]"
                            type="hidden"
                            min="0"
                            max="{{ $availableStock }}"
                            value="{{ $quantityValue }}"
                        >
                    </article>
                @empty
                    <div class="pos-empty">
                        <span class="material-symbols-outlined">inventory_2</span>
                        <strong>Belum ada produk aktif</strong>
                        <p>Tambahkan produk terlebih dahulu agar POS bisa digunakan.</p>
                    </div>
                @endforelse
            </div>
        </section>

        <aside class="pos-checkout-panel">
            <div class="pos-panel-header">
                <div>
                    <h2>Keranjang</h2>
                    <p>Transaction ID: #SP-{{ now()->format('Ymd') }}</p>
                </div>
                <span class="badge ok" data-cart-count>0 item</span>
            </div>

            <div class="pos-cart-items" data-cart-items>
                <p class="muted">Pilih produk dari katalog untuk memulai transaksi.</p>
            </div>

            <div class="pos-payment-box">
                <div class="pos-totals">
                    <div>
                        <span>Subtotal</span>
                        <strong data-subtotal>Rp {{ number_format($initialSubtotal, 0, ',', '.') }}</strong>
                    </div>
                    <div>
                        <span>Pajak (0%)</span>
                        <strong>Rp 0</strong>
                    </div>
                    <div class="total">
                        <span>Total</span>
                        <strong data-total>Rp {{ number_format($initialSubtotal, 0, ',', '.') }}</strong>
                    </div>
                </div>

                <div>
                    <p class="pos-payment-title">Metode Pembayaran</p>
                    <div class="pos-payment-methods">
                        <button class="{{ old('payment_method', 'cash') === 'cash' ? 'active' : '' }}" data-payment-method-option="cash" type="button"><span class="material-symbols-outlined">payments</span>Tunai</button>
                        <button class="{{ old('payment_method') === 'qris' ? 'active' : '' }}" data-payment-method-option="qris" type="button"><span class="material-symbols-outlined">qr_code_2</span>QRIS</button>
                        <button class="{{ old('payment_method') === 'transfer' ? 'active' : '' }}" data-payment-method-option="transfer" type="button"><span class="material-symbols-outlined">sync_alt</span>Transfer</button>
                    </div>
                </div>

                <div class="field">
                    <label for="paid_amount">Uang Dibayar</label>
                    <div class="pos-money-field">
                        <span>Rp</span>
                        <input id="paid_amount" data-paid-amount name="paid_amount" type="number" min="0" value="{{ $initialPaid }}" required>
                    </div>
                </div>

                <div class="pos-change-row">
                    <span>Kembalian</span>
                    <strong data-change>Rp {{ number_format(max(0, $initialPaid - $initialSubtotal), 0, ',', '.') }}</strong>
                </div>

                <button class="btn transaction pos-process-button" type="submit">
                    <span class="material-symbols-outlined">check_circle</span>
                    Proses Transaksi
                </button>
            </div>
        </aside>
        <div class="pos-catalog-pagination" data-pos-pagination></div>
    </form>

    @if(auth()->user()->isCashier() && $recentSales->isNotEmpty())
        <section class="pos-recent-sales">
            <div class="section-title">
                <h2>Struk Terakhir</h2>
                <a class="btn small" href="{{ route('reports.index') }}">Lihat Laporan</a>
            </div>
            <div class="pos-recent-list">
                @foreach($recentSales->take(3) as $sale)
                    <article>
                        <div class="pos-recent-summary">
                            <div>
                                <strong>{{ $sale->invoice_number }}</strong>
                                <span>{{ $sale->created_at->format('d M Y H:i') }}</span>
                            </div>
                            <strong>Rp {{ number_format($sale->total, 0, ',', '.') }}</strong>
                        </div>
                        @if(auth()->user()->canPermission('sales.receipt'))
                            <a class="btn small" href="{{ route('reports.receipt', $sale) }}" target="_blank">Struk</a>
                        @endif
                    </article>
                @endforeach
            </div>
        </section>
    @endif
</div>

<div class="modal-overlay" data-barcode-modal style="display:none">
    <div class="receipt-card">
        <div class="receipt-header">
            <h2>Scan Barcode</h2>
            <p>Arahkan kamera ke barcode produk</p>
        </div>
        <div class="receipt-body">
            <video data-barcode-video autoplay playsinline muted style="width:100%;aspect-ratio:4/3;background:#111;border-radius:8px;object-fit:cover"></video>
            <p class="muted" data-barcode-status style="margin:12px 0 0;font-size:13px">Menyiapkan kamera...</p>
        </div>
        <div class="receipt-footer">
            <button type="button" class="btn" data-close-barcode-scanner style="width:100%;justify-content:center">Tutup</button>
        </div>
    </div>
</div>

<style>
    .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 9999; backdrop-filter: blur(2px); }
    .receipt-card { background: white; width: 100%; max-width: 380px; border-radius: 16px; overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.2); animation: slideUp 0.3s ease; }
    .receipt-header { background: var(--green-soft); padding: 24px; text-align: center; color: var(--green-container); }
    .receipt-header h2 { margin: 0 0 4px; font-size: 20px; }
    .receipt-header p { margin: 0; font-weight: 700; opacity: 0.8; }
    .receipt-body { padding: 24px; }
    .receipt-footer { padding: 16px 24px 24px; background: var(--surface-low); }
    @media print { body * { visibility: hidden; } .receipt-card, .receipt-card * { visibility: visible; } .receipt-card { position: absolute; left: 0; top: 0; box-shadow: none; max-width: 100%; } .receipt-footer { display: none; } }
</style>

@if(!empty($receipt))
    <div class="modal-overlay" id="receipt-modal">
        <div class="receipt-card">
            <div class="receipt-header">
                <h2>Transaksi Berhasil!</h2>
                <p>#{{ $receipt['invoice_number'] }}</p>
            </div>
            <div class="receipt-body">
                <p style="text-align:center; color:var(--muted); font-size:12px; margin-bottom:12px;">{{ $receipt['sold_at'] }} | Kasir: {{ $receipt['cashier'] }}</p>
                <table style="width:100%; font-size:13px; margin-bottom:16px;">
                    @foreach($receipt['items'] as $item)
                        <tr>
                            <td style="padding:4px 0;"><strong>{{ $item['name'] }}</strong><br><span style="color:var(--muted)">{{ $item['quantity'] }} x Rp {{ number_format($item['unit_price'], 0, ',', '.') }}</span></td>
                            <td style="text-align:right; padding:4px 0;">Rp {{ number_format($item['line_total'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </table>
                <div style="border-top:1px dashed var(--line); padding-top:12px; margin-bottom:12px;">
                    <div style="display:flex; justify-content:space-between; margin-bottom:4px;"><span>Total</span><strong>Rp {{ number_format($receipt['total'], 0, ',', '.') }}</strong></div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:4px;"><span>Tunai ({{ $receipt['payment_method'] }})</span><span>Rp {{ number_format($receipt['paid_amount'], 0, ',', '.') }}</span></div>
                    <div style="display:flex; justify-content:space-between; font-size:16px; margin-top:8px; padding-top:8px; border-top:1px solid var(--line);"><span>Kembali</span><strong style="color:var(--green)">Rp {{ number_format($receipt['change_amount'], 0, ',', '.') }}</strong></div>
                </div>
            </div>
            <div class="receipt-footer">
                <button type="button" class="btn primary" onclick="document.getElementById('receipt-modal').style.display='none'" style="width:100%; justify-content:center;">
                    Tutup & Lanjut Transaksi
                </button>
                <button type="button" class="btn" style="width:100%; justify-content:center; margin-top:8px;" onclick="window.print()">
                    <span class="material-symbols-outlined">print</span> Cetak Struk
                </button>
            </div>
        </div>
    </div>
@endif

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const workspace = document.querySelector('[data-pos-workspace]');
        if (!workspace) return;

        const formatter = new Intl.NumberFormat('id-ID');
        const quantityInputs = [...workspace.querySelectorAll('[data-pos-quantity]')];
        const cartItems = workspace.querySelector('[data-cart-items]');
        const cartCount = workspace.querySelector('[data-cart-count]');
        const subtotalNode = workspace.querySelector('[data-subtotal]');
        const totalNode = workspace.querySelector('[data-total]');
        const paidInput = workspace.querySelector('[data-paid-amount]');
        const changeNode = workspace.querySelector('[data-change]');
        const searchInput = workspace.querySelector('[data-pos-search]');
        const paginationNode = workspace.querySelector('[data-pos-pagination]');
        const paymentMethodInput = workspace.querySelector('[data-payment-method]');
        const productCards = [...workspace.querySelectorAll('[data-product-card]')];
        const barcodeModal = document.querySelector('[data-barcode-modal]');
        const barcodeVideo = document.querySelector('[data-barcode-video]');
        const barcodeStatus = document.querySelector('[data-barcode-status]');
        const productsPerPage = 6;
        let currentCategory = 'all';
        let currentProductPage = 1;
        let barcodeStream = null;
        let barcodeLoopActive = false;
        let zxingReader = null;
        let zxingControls = null;

        const rupiah = value => `Rp ${formatter.format(Math.max(0, Number(value) || 0))}`;
        const escapeHtml = value => String(value)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');

        function syncCart() {
            const selected = quantityInputs
                .map((input, index) => ({
                    index,
                    input,
                    quantity: Number(input.value) || 0,
                    stock: Number(input.dataset.productStock) || 0,
                    price: Number(input.dataset.productPrice) || 0,
                    label: input.dataset.productLabel,
                    sku: input.dataset.productSku,
                }))
                .filter(item => item.quantity > 0);

            const subtotal = selected.reduce((sum, item) => sum + item.price * item.quantity, 0);
            const paid = Number(paidInput.value) || 0;

            subtotalNode.textContent = rupiah(subtotal);
            totalNode.textContent = rupiah(subtotal);
            changeNode.textContent = rupiah(paid - subtotal);
            cartCount.textContent = `${selected.reduce((sum, item) => sum + item.quantity, 0)} item`;

            if (selected.length === 0) {
                cartItems.innerHTML = '<p class="muted">Pilih produk dari katalog untuk memulai transaksi.</p>';
                return;
            }

            cartItems.innerHTML = selected.map(item => `
                <div class="pos-cart-line">
                    <div class="pos-cart-line-info">
                        <strong>${escapeHtml(item.label)}</strong>
                        <span>@ ${rupiah(item.price)} - ${escapeHtml(item.sku)}</span>
                    </div>
                    <div class="pos-cart-stepper">
                        <button type="button" data-pos-decrement="${item.index}"><span class="material-symbols-outlined">remove</span></button>
                        <span>${item.quantity}</span>
                        <button type="button" data-pos-increment="${item.index}"><span class="material-symbols-outlined">add</span></button>
                    </div>
                    <div class="pos-cart-line-total">
                        <strong>${rupiah(item.price * item.quantity)}</strong>
                        <button type="button" data-pos-clear="${item.index}"><span class="material-symbols-outlined">delete</span></button>
                    </div>
                </div>
            `).join('');
        }

        function updateQuantity(index, delta, clear = false) {
            const input = quantityInputs[index];
            if (!input) return;

            const current = Number(input.value) || 0;
            const max = Math.max(0, Number(input.dataset.productStock) || 0);
            input.value = clear ? 0 : Math.min(max, Math.max(0, current + delta));
            syncCart();
        }

        function addByBarcode(rawValue) {
            const code = String(rawValue || '').trim().toLowerCase();
            if (!code) return false;

            const card = productCards.find(item => item.dataset.productSku === code);

            if (!card) {
                searchInput.value = rawValue;
                currentProductPage = 1;
                filterCatalog();
                return false;
            }

            updateQuantity(Number(card.dataset.productIndex), 1);
            searchInput.value = '';
            currentCategory = 'all';
            workspace.querySelectorAll('[data-category-filter]').forEach(button => {
                button.classList.toggle('active', button.dataset.categoryFilter === 'all');
            });
            currentProductPage = 1;
            filterCatalog();
            return true;
        }

        function filterCatalog() {
            const query = (searchInput.value || '').toLowerCase();
            const matches = productCards.filter(card => {
                const matchesText = card.dataset.productName.includes(query);
                const matchesCategory = currentCategory === 'all' || card.dataset.category === currentCategory;
                return matchesText && matchesCategory;
            });

            const pageCount = Math.max(1, Math.ceil(matches.length / productsPerPage));
            currentProductPage = Math.min(currentProductPage, pageCount);
            const pageStart = (currentProductPage - 1) * productsPerPage;
            const visibleCards = new Set(matches.slice(pageStart, pageStart + productsPerPage));

            productCards.forEach(card => {
                card.hidden = !visibleCards.has(card);
            });

            if (!paginationNode) return;

            if (matches.length <= productsPerPage) {
                paginationNode.innerHTML = '';
                return;
            }

            const buttons = Array.from({ length: pageCount }, (_, index) => {
                const page = index + 1;
                return `<button class="${page === currentProductPage ? 'active' : ''}" data-pos-page="${page}" type="button">${page}</button>`;
            }).join('');

            paginationNode.innerHTML = `
                <span>${pageStart + 1}-${Math.min(pageStart + productsPerPage, matches.length)} dari ${matches.length} produk</span>
                <div>${buttons}</div>
            `;
        }

        workspace.addEventListener('click', event => {
            const increment = event.target.closest('[data-pos-increment]');
            const decrement = event.target.closest('[data-pos-decrement]');
            const clear = event.target.closest('[data-pos-clear]');
            const category = event.target.closest('[data-category-filter]');
            const payment = event.target.closest('[data-payment-method-option]');
            const page = event.target.closest('[data-pos-page]');

            if (increment) updateQuantity(Number(increment.dataset.posIncrement), 1);
            if (decrement) updateQuantity(Number(decrement.dataset.posDecrement), -1);
            if (clear) updateQuantity(Number(clear.dataset.posClear), 0, true);
            if (page) {
                currentProductPage = Number(page.dataset.posPage) || 1;
                filterCatalog();
            }
            if (category) {
                workspace.querySelectorAll('[data-category-filter]').forEach(button => button.classList.remove('active'));
                category.classList.add('active');
                currentCategory = category.dataset.categoryFilter;
                currentProductPage = 1;
                filterCatalog();
            }
            if (payment) {
                workspace.querySelectorAll('.pos-payment-methods button').forEach(button => button.classList.remove('active'));
                payment.classList.add('active');
                if (paymentMethodInput) paymentMethodInput.value = payment.dataset.paymentMethodOption;
            }
        });

        paidInput.addEventListener('input', syncCart);
        searchInput.addEventListener('input', () => {
            currentProductPage = 1;
            filterCatalog();
        });
        searchInput.addEventListener('keydown', event => {
            if (event.key !== 'Enter') return;
            event.preventDefault();

            if (!addByBarcode(searchInput.value)) {
                barcodeStatus?.replaceChildren(document.createTextNode('Barcode tidak ditemukan di SKU produk.'));
            }
        });

        async function closeBarcodeScanner() {
            barcodeLoopActive = false;
            if (zxingControls?.stop) {
                zxingControls.stop();
                zxingControls = null;
            }
            if (zxingReader?.reset) {
                zxingReader.reset();
                zxingReader = null;
            }
            if (barcodeStream) {
                barcodeStream.getTracks().forEach(track => track.stop());
                barcodeStream = null;
            }
            if (barcodeModal) barcodeModal.style.display = 'none';
        }

        function loadZxingLibrary() {
            if (window.ZXingBrowser || window.ZXing) {
                return Promise.resolve();
            }

            return new Promise((resolve, reject) => {
                const existing = document.querySelector('script[data-zxing-fallback]');
                if (existing) {
                    existing.addEventListener('load', resolve, { once: true });
                    existing.addEventListener('error', reject, { once: true });
                    return;
                }

                const script = document.createElement('script');
                script.src = 'https://unpkg.com/@zxing/library@0.21.3/umd/index.min.js';
                script.async = true;
                script.dataset.zxingFallback = 'true';
                script.onload = resolve;
                script.onerror = reject;
                document.head.appendChild(script);
            });
        }

        async function scanWithZxingFallback() {
            barcodeStatus.textContent = 'Memuat scanner kamera alternatif...';
            await loadZxingLibrary();

            const zxing = window.ZXingBrowser || window.ZXing;

            if (!zxing?.BrowserMultiFormatReader) {
                throw new Error('ZXing fallback is not available.');
            }

            zxingReader = new zxing.BrowserMultiFormatReader();
            barcodeStatus.textContent = 'Kamera aktif. Arahkan ke barcode produk.';

            const controls = await zxingReader.decodeFromVideoDevice(null, barcodeVideo, async (result) => {
                if (!result) return;

                const value = result.getText ? result.getText() : result.text;
                const found = addByBarcode(value);
                barcodeStatus.textContent = found
                    ? `Produk ${value} masuk keranjang.`
                    : `Barcode ${value} tidak ditemukan di SKU produk.`;

                if (found) {
                    await closeBarcodeScanner();
                }
            });

            if (controls?.stop) {
                zxingControls = controls;
            }
        }

        async function openBarcodeScanner() {
            if (!barcodeModal || !barcodeVideo || !barcodeStatus) return;

            barcodeModal.style.display = 'flex';
            barcodeStatus.textContent = 'Menyiapkan kamera...';

            if (!('BarcodeDetector' in window)) {
                try {
                    await scanWithZxingFallback();
                } catch (error) {
                    barcodeStatus.textContent = 'Scanner kamera alternatif gagal dimuat. Pakai scanner USB/HP lalu scan ke kolom pencarian dan tekan Enter.';
                }
                return;
            }

            try {
                barcodeStream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'environment' },
                    audio: false,
                });
                barcodeVideo.srcObject = barcodeStream;
                await barcodeVideo.play();

                const detector = new BarcodeDetector({
                    formats: ['ean_13', 'ean_8', 'code_128', 'code_39', 'qr_code'],
                });
                barcodeLoopActive = true;
                barcodeStatus.textContent = 'Kamera aktif. Arahkan ke barcode produk.';

                const scanFrame = async () => {
                    if (!barcodeLoopActive) return;

                    try {
                        const codes = await detector.detect(barcodeVideo);
                        if (codes.length > 0) {
                            const value = codes[0].rawValue;
                            const found = addByBarcode(value);
                            barcodeStatus.textContent = found
                                ? `Produk ${value} masuk keranjang.`
                                : `Barcode ${value} tidak ditemukan di SKU produk.`;

                            if (found) {
                                await closeBarcodeScanner();
                                return;
                            }
                        }
                    } catch (error) {
                        barcodeStatus.textContent = 'Scan belum berhasil, coba dekatkan barcode ke kamera.';
                    }

                    requestAnimationFrame(scanFrame);
                };

                requestAnimationFrame(scanFrame);
            } catch (error) {
                barcodeStatus.textContent = 'Kamera tidak bisa diakses. Izinkan kamera browser, atau pakai scanner USB ke kolom pencarian.';
            }
        }

        document.querySelector('[data-open-barcode-scanner]')?.addEventListener('click', openBarcodeScanner);
        document.querySelector('[data-close-barcode-scanner]')?.addEventListener('click', closeBarcodeScanner);
        filterCatalog();
        syncCart();
    });
</script>
@endsection
