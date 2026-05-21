<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Demo StokPintar</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
:root{--green:#003527;--mint:#b0f0d6;--ink:#0d1a14;--muted:#5f746b;--line:#dce8e2;--soft:#f4faf6;--panel:#fff;--orange:#ff8c00;--red:#c0392b}
body{font-family:Inter,sans-serif;background:#f8fbf9;color:var(--ink);line-height:1.5}
a{text-decoration:none;color:inherit}
button,input,select{font:inherit}
.demo-shell{min-height:100vh;display:grid;grid-template-columns:260px 1fr}
.demo-sidebar{background:var(--green);color:white;padding:28px;display:flex;flex-direction:column;gap:28px;position:sticky;top:0;height:100vh}
.brand{display:flex;align-items:center;gap:12px;font-weight:900;font-size:20px}
.mark{width:42px;height:42px;border-radius:10px;background:var(--mint);color:var(--green);display:grid;place-items:center;font-weight:900}
.demo-badge{background:rgba(176,240,214,.12);border:1px solid rgba(176,240,214,.22);border-radius:12px;padding:14px;color:#d9fff0;font-size:13px}
.demo-badge strong{display:block;color:white;margin-bottom:4px}
.nav{display:grid;gap:8px}
.nav button{padding:12px 14px;border-radius:10px;color:#d9fff0;font-weight:800;background:transparent;border:0;text-align:left;cursor:pointer}
.nav button.active{background:rgba(255,255,255,.14);color:white}
.demo-main{padding:28px;display:grid;gap:20px}
.topbar{display:flex;justify-content:space-between;gap:16px;align-items:center}
h1{font-size:30px;line-height:36px}
h2{font-size:20px}
.subtitle{color:var(--muted);margin-top:4px}
.actions{display:flex;gap:10px;flex-wrap:wrap}
.btn{display:inline-flex;align-items:center;justify-content:center;padding:11px 16px;border-radius:10px;border:1px solid var(--line);background:white;font-weight:800;color:var(--green);cursor:pointer}
.btn.primary{background:var(--green);color:white;border-color:var(--green)}
.btn.small{padding:8px 11px;font-size:12px;border-radius:8px}
.btn.danger{color:var(--red);border-color:#f3c3bc}
.grid-4{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px}
.grid-3{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}
.grid-2{display:grid;grid-template-columns:1.2fr .8fr;gap:14px}
.card{background:white;border:1px solid var(--line);border-radius:12px;padding:18px}
.metric-label{font-size:12px;font-weight:800;text-transform:uppercase;color:var(--muted)}
.metric-value{font-size:26px;font-weight:900;color:var(--green);margin-top:6px}
.metric-note{font-size:12px;color:var(--muted);margin-top:4px}
.section-title{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:14px}
.badge{display:inline-flex;padding:6px 10px;border-radius:999px;background:var(--soft);color:var(--green);font-size:12px;font-weight:800;white-space:nowrap}
.badge.low{background:#fff1f0;color:var(--red)}
.notice{background:#fff7ed;border:1px solid #fed7aa;color:#9a3412;border-radius:12px;padding:14px;font-weight:700}
.view{display:none;gap:20px}
.view.active{display:grid}
.chart{height:220px;display:flex;align-items:flex-end;gap:10px;padding-top:20px}
.bar{flex:1;border-radius:8px 8px 0 0;background:var(--mint);min-height:36px}
.bar.active{background:var(--green)}
table{width:100%;border-collapse:collapse}
th,td{text-align:left;padding:12px;border-bottom:1px solid var(--line);font-size:14px;vertical-align:top}
th{font-size:12px;text-transform:uppercase;color:var(--muted)}
.price{font-weight:900;color:var(--green);text-align:right;white-space:nowrap}
.muted{color:var(--muted);font-size:12px}
.toolbar{display:flex;gap:10px;flex-wrap:wrap;align-items:end}
.field{display:grid;gap:6px}
.field label{font-size:12px;font-weight:800;color:var(--muted);text-transform:uppercase}
.field input,.field select{border:1px solid var(--line);border-radius:10px;padding:10px 12px;background:white;min-height:42px}
.product-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}
.product-card{border:1px solid var(--line);border-radius:12px;background:white;padding:14px;display:grid;gap:10px}
.product-head{display:flex;justify-content:space-between;gap:10px}
.product-thumb{width:42px;height:42px;border-radius:10px;background:var(--soft);display:grid;place-items:center;color:var(--green);font-weight:900}
.stock-actions{display:grid;grid-template-columns:1fr 1fr auto;gap:8px}
.pos-layout{display:grid;grid-template-columns:minmax(0,1fr)360px;gap:14px;align-items:start}
.pos-products{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}
.pos-product{border:1px solid var(--line);border-radius:12px;padding:14px;background:white;display:grid;gap:8px;text-align:left;cursor:pointer}
.pos-product:hover{border-color:var(--green)}
.cart{display:grid;gap:10px}
.cart-line{display:flex;justify-content:space-between;gap:12px;border-bottom:1px solid var(--line);padding:10px 0}
.cart-total{display:flex;justify-content:space-between;font-size:20px;font-weight:900;margin-top:12px;color:var(--green)}
.payment-methods{display:grid;grid-template-columns:repeat(3,1fr);gap:8px}
.payment-methods button.active{background:var(--green);color:white;border-color:var(--green)}
@media(max-width:980px){.demo-shell{grid-template-columns:1fr}.demo-sidebar{position:static;height:auto}.grid-4,.grid-3,.grid-2,.pos-layout{grid-template-columns:1fr}.product-grid,.pos-products{grid-template-columns:1fr 1fr}.topbar{align-items:flex-start;flex-direction:column}}
@media(max-width:620px){.demo-main,.demo-sidebar{padding:18px}.product-grid,.pos-products{grid-template-columns:1fr}.stock-actions{grid-template-columns:1fr}.payment-methods{grid-template-columns:1fr}}
</style>
</head>
<body>
<div class="demo-shell" data-demo>
  <aside class="demo-sidebar">
    <div class="brand"><div class="mark">SP</div><span>StokPintar</span></div>
    <div class="demo-badge">
      <strong>Mode Demo Publik</strong>
      <span>Bisa klik fitur tanpa login. Data hanya contoh dan reset saat halaman direfresh.</span>
    </div>
    <div class="nav" aria-label="Navigasi demo">
      <button class="active" type="button" data-demo-tab="dashboard">Dashboard</button>
      <button type="button" data-demo-tab="products">Produk & Stok</button>
      <button type="button" data-demo-tab="pos">POS Kasir</button>
      <button type="button" data-demo-tab="reports">Laporan</button>
    </div>
  </aside>

  <main class="demo-main">
    <header class="topbar">
      <div>
        <h1>Demo Operasional Toko</h1>
        <p class="subtitle">Coba alur stok, POS, dan laporan StokPintar tanpa membuat akun dulu.</p>
      </div>
      <div class="actions">
        <a class="btn" href="{{ route('home') }}">Kembali</a>
        <a class="btn primary" href="{{ route('register') }}">Aktifkan Full Setup</a>
      </div>
    </header>

    <div class="notice">Demo ini tidak menyimpan data ke database. Untuk transaksi nyata, role tim, dan laporan toko asli, buat akun toko full setup.</div>

    <section class="view active" data-demo-view="dashboard">
      <div class="grid-4">
        <div class="card"><p class="metric-label">Omzet Hari Ini</p><p class="metric-value" data-metric-revenue>Rp 0</p><p class="metric-note"><span data-metric-sales>0</span> transaksi selesai</p></div>
        <div class="card"><p class="metric-label">Produk Aktif</p><p class="metric-value" data-metric-products>0</p><p class="metric-note"><span data-metric-low-stock>0</span> stok kritis</p></div>
        <div class="card"><p class="metric-label">Item Terjual</p><p class="metric-value" data-metric-items>0</p><p class="metric-note">Dari demo POS</p></div>
        <div class="card"><p class="metric-label">Rata-rata Struk</p><p class="metric-value" data-metric-average>Rp 0</p><p class="metric-note">Nilai per transaksi</p></div>
      </div>
      <div class="grid-2">
        <div class="card">
          <div class="section-title"><h2>Omzet 7 Hari</h2><span class="badge">Dashboard</span></div>
          <div class="chart">
            <div class="bar" style="height:42%"></div><div class="bar" style="height:58%"></div><div class="bar" style="height:48%"></div><div class="bar" style="height:70%"></div><div class="bar" style="height:62%"></div><div class="bar" style="height:82%"></div><div class="bar active" style="height:100%"></div>
          </div>
        </div>
        <div class="card">
          <div class="section-title"><h2>Stok Kritis</h2><span class="badge low">Perlu Restock</span></div>
          <div data-low-stock-list></div>
        </div>
      </div>
    </section>

    <section class="view" data-demo-view="products">
      <div class="card">
        <div class="section-title">
          <h2>Produk & Stok</h2>
          <span class="badge">Demo Mutasi Stok</span>
        </div>
        <div class="toolbar">
          <div class="field">
            <label for="product-search">Cari Produk</label>
            <input id="product-search" data-product-search placeholder="Nama atau SKU">
          </div>
          <div class="field">
            <label for="category-filter">Kategori</label>
            <select id="category-filter" data-category-filter>
              <option value="all">Semua</option>
              <option value="Minuman">Minuman</option>
              <option value="Makanan">Makanan</option>
              <option value="Snack">Snack</option>
            </select>
          </div>
        </div>
      </div>
      <div class="product-grid" data-product-list></div>
    </section>

    <section class="view" data-demo-view="pos">
      <div class="pos-layout">
        <div class="card">
          <div class="section-title">
            <h2>POS Kasir</h2>
            <span class="badge">Klik Produk</span>
          </div>
          <div class="pos-products" data-pos-products></div>
        </div>
        <aside class="card">
          <div class="section-title"><h2>Keranjang</h2><span class="badge" data-cart-count>0 item</span></div>
          <div class="cart" data-cart-lines><p class="muted">Pilih produk dari katalog demo.</p></div>
          <div class="field" style="margin-top:12px">
            <label>Metode Bayar</label>
            <div class="payment-methods">
              <button class="btn small active" type="button" data-payment="cash">Tunai</button>
              <button class="btn small" type="button" data-payment="qris">QRIS</button>
              <button class="btn small" type="button" data-payment="transfer">Transfer</button>
            </div>
          </div>
          <div class="cart-total"><span>Total</span><span data-cart-total>Rp 0</span></div>
          <button class="btn primary" type="button" data-checkout style="width:100%;margin-top:14px">Proses Transaksi Demo</button>
        </aside>
      </div>
    </section>

    <section class="view" data-demo-view="reports">
      <div class="grid-3">
        <div class="card"><p class="metric-label">Total Pendapatan</p><p class="metric-value" data-report-revenue>Rp 0</p><p class="metric-note">Dari transaksi demo</p></div>
        <div class="card"><p class="metric-label">Transaksi</p><p class="metric-value" data-report-sales>0</p><p class="metric-note">Struk dibuat</p></div>
        <div class="card"><p class="metric-label">Metode Favorit</p><p class="metric-value" data-report-method>-</p><p class="metric-note">Tunai / QRIS / Transfer</p></div>
      </div>
      <div class="card">
        <div class="section-title"><h2>Laporan Transaksi</h2><span class="badge">Owner / Manager</span></div>
        <table>
          <thead><tr><th>Invoice</th><th>Kasir</th><th>Metode</th><th class="price">Total</th></tr></thead>
          <tbody data-report-table></tbody>
        </table>
      </div>
    </section>
  </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const formatter = new Intl.NumberFormat('id-ID');
  const rupiah = value => `Rp ${formatter.format(Math.max(0, Number(value) || 0))}`;
  const products = [
    { id: 1, name: 'Kopi Susu Botol', sku: 'KOPI-001', category: 'Minuman', stock: 42, min: 10, price: 18000 },
    { id: 2, name: 'Roti Cokelat', sku: 'ROTI-002', category: 'Makanan', stock: 4, min: 8, price: 9000 },
    { id: 3, name: 'Teh Lemon Cup', sku: 'TEH-003', category: 'Minuman', stock: 31, min: 12, price: 7000 },
    { id: 4, name: 'Keripik Pedas', sku: 'SNK-004', category: 'Snack', stock: 68, min: 15, price: 12000 },
    { id: 5, name: 'Mie Goreng Instan', sku: 'MIE-005', category: 'Makanan', stock: 15, min: 20, price: 4500 },
    { id: 6, name: 'Air Mineral', sku: 'AIR-006', category: 'Minuman', stock: 6, min: 24, price: 4000 },
  ];
  const cart = new Map();
  const sales = [
    { invoice: 'INV-DEMO-0003', cashier: 'Budi Santoso', method: 'QRIS', total: 116000, items: 5 },
    { invoice: 'INV-DEMO-0002', cashier: 'Sari Dewi', method: 'Tunai', total: 84500, items: 4 },
    { invoice: 'INV-DEMO-0001', cashier: 'Budi Santoso', method: 'Transfer', total: 57000, items: 3 },
  ];
  let paymentMethod = 'cash';

  const tabButtons = document.querySelectorAll('[data-demo-tab]');
  const views = document.querySelectorAll('[data-demo-view]');
  tabButtons.forEach(button => {
    button.addEventListener('click', () => {
      tabButtons.forEach(item => item.classList.remove('active'));
      views.forEach(view => view.classList.remove('active'));
      button.classList.add('active');
      document.querySelector(`[data-demo-view="${button.dataset.demoTab}"]`)?.classList.add('active');
    });
  });

  function renderDashboard() {
    const revenue = sales.reduce((sum, sale) => sum + sale.total, 0);
    const totalItems = sales.reduce((sum, sale) => sum + sale.items, 0);
    const lowStock = products.filter(product => product.stock <= product.min);
    document.querySelector('[data-metric-revenue]').textContent = rupiah(revenue);
    document.querySelector('[data-metric-sales]').textContent = sales.length;
    document.querySelector('[data-metric-products]').textContent = products.length;
    document.querySelector('[data-metric-low-stock]').textContent = lowStock.length;
    document.querySelector('[data-metric-items]').textContent = totalItems;
    document.querySelector('[data-metric-average]').textContent = rupiah(sales.length ? revenue / sales.length : 0);
    document.querySelector('[data-low-stock-list]').innerHTML = lowStock.map(product => `
      <div class="cart-line"><span><strong>${product.name}</strong><br><small class="muted">Min. ${product.min}</small></span><strong>${product.stock} pcs</strong></div>
    `).join('') || '<p class="muted">Semua stok aman.</p>';
  }

  function renderProducts() {
    const query = (document.querySelector('[data-product-search]').value || '').toLowerCase();
    const category = document.querySelector('[data-category-filter]').value;
    const filtered = products.filter(product => {
      const matchesText = `${product.name} ${product.sku}`.toLowerCase().includes(query);
      const matchesCategory = category === 'all' || product.category === category;
      return matchesText && matchesCategory;
    });

    document.querySelector('[data-product-list]').innerHTML = filtered.map(product => `
      <article class="product-card">
        <div class="product-head">
          <div>
            <strong>${product.name}</strong>
            <p class="muted">${product.sku} - ${product.category}</p>
          </div>
          <div class="product-thumb">${product.name.slice(0, 2).toUpperCase()}</div>
        </div>
        <div><span class="badge ${product.stock <= product.min ? 'low' : ''}">Stok ${product.stock} pcs</span></div>
        <div class="stock-actions">
          <button class="btn small" type="button" data-stock-out="${product.id}">-1</button>
          <button class="btn small" type="button" data-stock-in="${product.id}">+1</button>
          <strong class="price">${rupiah(product.price)}</strong>
        </div>
      </article>
    `).join('');
  }

  function renderPos() {
    document.querySelector('[data-pos-products]').innerHTML = products.map(product => `
      <button class="pos-product" type="button" data-add-cart="${product.id}" ${product.stock <= 0 ? 'disabled' : ''}>
        <strong>${product.name}</strong>
        <span>Stok ${product.stock} pcs</span>
        <b>${rupiah(product.price)}</b>
      </button>
    `).join('');

    const cartItems = Array.from(cart.entries()).map(([id, qty]) => {
      const product = products.find(item => item.id === id);
      return { product, qty };
    });
    const total = cartItems.reduce((sum, item) => sum + item.product.price * item.qty, 0);
    const count = cartItems.reduce((sum, item) => sum + item.qty, 0);
    document.querySelector('[data-cart-count]').textContent = `${count} item`;
    document.querySelector('[data-cart-total]').textContent = rupiah(total);
    document.querySelector('[data-cart-lines]').innerHTML = cartItems.length
      ? cartItems.map(item => `
        <div class="cart-line">
          <span>${item.qty}x ${item.product.name}</span>
          <strong>${rupiah(item.product.price * item.qty)}</strong>
        </div>
      `).join('')
      : '<p class="muted">Pilih produk dari katalog demo.</p>';
  }

  function renderReports() {
    const revenue = sales.reduce((sum, sale) => sum + sale.total, 0);
    const methods = sales.reduce((carry, sale) => {
      carry[sale.method] = (carry[sale.method] || 0) + 1;
      return carry;
    }, {});
    const favorite = Object.entries(methods).sort((a, b) => b[1] - a[1])[0]?.[0] || '-';
    document.querySelector('[data-report-revenue]').textContent = rupiah(revenue);
    document.querySelector('[data-report-sales]').textContent = sales.length;
    document.querySelector('[data-report-method]').textContent = favorite;
    document.querySelector('[data-report-table]').innerHTML = sales.map(sale => `
      <tr><td>${sale.invoice}</td><td>${sale.cashier}</td><td>${sale.method}</td><td class="price">${rupiah(sale.total)}</td></tr>
    `).join('');
  }

  function renderAll() {
    renderDashboard();
    renderProducts();
    renderPos();
    renderReports();
  }

  document.addEventListener('input', event => {
    if (event.target.matches('[data-product-search]')) renderProducts();
  });
  document.addEventListener('change', event => {
    if (event.target.matches('[data-category-filter]')) renderProducts();
  });
  document.addEventListener('click', event => {
    const stockIn = event.target.closest('[data-stock-in]');
    const stockOut = event.target.closest('[data-stock-out]');
    const addCart = event.target.closest('[data-add-cart]');
    const payment = event.target.closest('[data-payment]');
    const checkout = event.target.closest('[data-checkout]');

    if (stockIn || stockOut) {
      const id = Number((stockIn || stockOut).dataset.stockIn || (stockIn || stockOut).dataset.stockOut);
      const product = products.find(item => item.id === id);
      if (!product) return;
      product.stock = Math.max(0, product.stock + (stockIn ? 1 : -1));
      renderAll();
    }

    if (addCart) {
      const id = Number(addCart.dataset.addCart);
      const product = products.find(item => item.id === id);
      const qty = cart.get(id) || 0;
      if (!product || qty >= product.stock) return;
      cart.set(id, qty + 1);
      renderPos();
    }

    if (payment) {
      paymentMethod = payment.dataset.payment;
      document.querySelectorAll('[data-payment]').forEach(button => button.classList.remove('active'));
      payment.classList.add('active');
    }

    if (checkout) {
      const cartItems = Array.from(cart.entries()).map(([id, qty]) => ({ product: products.find(item => item.id === id), qty }));
      if (cartItems.length === 0) return;
      const total = cartItems.reduce((sum, item) => sum + item.product.price * item.qty, 0);
      cartItems.forEach(item => {
        item.product.stock = Math.max(0, item.product.stock - item.qty);
      });
      sales.unshift({
        invoice: `INV-DEMO-${String(sales.length + 1).padStart(4, '0')}`,
        cashier: 'Kasir Demo',
        method: paymentMethod.toUpperCase(),
        total,
        items: cartItems.reduce((sum, item) => sum + item.qty, 0),
      });
      cart.clear();
      renderAll();
      document.querySelector('[data-demo-tab="reports"]').click();
    }
  });

  renderAll();
});
</script>
</body>
</html>
