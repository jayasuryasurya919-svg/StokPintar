<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>StokPintar — SaaS Stok & Kasir untuk UMKM Indonesia</title>
<meta name="description" content="Kelola stok, POS, laporan, barcode, cabang, tim, dan resep F&B UMKM Anda dari browser. Semua fitur aktif.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
:root{--g:#003527;--g2:#00563d;--g3:#b0f0d6;--ink:#0d1a14;--muted:#5a7368;--line:#dde8e2;--soft:#f4faf6;--amber:#ff8c00;--rose:#c0392b}
body{font-family:Inter,sans-serif;color:var(--ink);background:#fff;line-height:1.5}
a{text-decoration:none;color:inherit}
/* NAV */
nav{position:sticky;top:0;z-index:100;background:rgba(255,255,255,.92);backdrop-filter:blur(12px);border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between;padding:0 5%;height:64px}
.nav-brand{display:flex;align-items:center;gap:10px;font-weight:800;font-size:18px;color:var(--g)}
.nav-mark{width:36px;height:36px;background:var(--g);border-radius:8px;display:grid;place-items:center;color:#fff;font-weight:900;font-size:15px}
.nav-links{display:flex;align-items:center;gap:24px;font-size:14px;font-weight:600;color:var(--muted)}
.nav-links a:hover{color:var(--g)}
.btn{display:inline-flex;align-items:center;gap:6px;padding:10px 20px;border-radius:10px;font-weight:700;font-size:14px;cursor:pointer;border:none;transition:.15s}
.btn-primary{background:var(--g);color:#fff}
.btn-primary:hover{background:var(--g2)}
.btn-outline{background:#fff;color:var(--g);border:1.5px solid var(--g)}
.btn-outline:hover{background:var(--soft)}
/* HERO */
.hero{padding:80px 5% 60px;display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:center;max-width:1200px;margin:0 auto}
.hero-badge{display:inline-flex;align-items:center;gap:8px;background:var(--soft);border:1px solid var(--line);border-radius:999px;padding:6px 14px;font-size:13px;font-weight:700;color:var(--g2);margin-bottom:20px}
.hero-badge span{width:8px;height:8px;background:#22c55e;border-radius:50%;animation:pulse 2s infinite}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}
.hero h1{font-size:48px;line-height:1.1;font-weight:900;letter-spacing:-.03em;color:var(--ink);margin-bottom:20px}
.hero h1 em{font-style:normal;color:var(--g)}
.hero p{font-size:18px;color:var(--muted);margin-bottom:32px;line-height:1.6}
.hero-actions{display:flex;gap:12px;flex-wrap:wrap}
.hero-trust{margin-top:28px;font-size:13px;color:var(--muted);display:flex;align-items:center;gap:8px}
.hero-trust svg{color:#f59e0b}
/* MOCKUP */
.hero-visual{position:relative}
.mockup-shell{background:var(--g);border-radius:20px;padding:16px;box-shadow:0 40px 80px rgba(0,53,39,.22)}
.mockup-bar{display:flex;gap:6px;margin-bottom:12px}
.mockup-bar span{width:10px;height:10px;border-radius:50%;background:rgba(255,255,255,.25)}
.mockup-screen{background:#f4faf6;border-radius:10px;padding:18px;display:grid;gap:10px}
.m-row{display:grid;grid-template-columns:repeat(4,1fr);gap:8px}
.m-card{background:#fff;border-radius:8px;padding:12px;border:1px solid #dde8e2}
.m-label{font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;margin-bottom:4px}
.m-value{font-size:18px;font-weight:800;color:var(--g)}
.m-note{font-size:10px;color:var(--muted);margin-top:2px}
.m-chart{background:#fff;border-radius:8px;padding:12px;border:1px solid #dde8e2}
.m-bars{display:flex;align-items:flex-end;gap:6px;height:60px;margin-top:6px}
.m-bar{flex:1;border-radius:4px 4px 0 0;background:var(--g3)}
.m-bar.active{background:var(--g)}
.m-table{background:#fff;border-radius:8px;overflow:hidden;border:1px solid #dde8e2}
.m-th{display:grid;grid-template-columns:2fr 1fr 1fr;padding:6px 10px;background:#f4faf6;font-size:10px;font-weight:700;color:var(--muted)}
.m-td{display:grid;grid-template-columns:2fr 1fr 1fr;padding:7px 10px;font-size:11px;border-top:1px solid #dde8e2}
.m-ok{color:#16a34a;font-weight:700}
.m-low{color:var(--rose);font-weight:700}
/* STATS */
.stats{background:var(--g);padding:48px 5%;display:grid;grid-template-columns:repeat(4,1fr);gap:32px;text-align:center}
.stat-num{font-size:36px;font-weight:900;color:var(--g3);line-height:1}
.stat-label{font-size:14px;color:rgba(255,255,255,.7);margin-top:6px}
/* FEATURES */
.features{padding:80px 5%;max-width:1200px;margin:0 auto}
.section-label{font-size:13px;font-weight:800;color:var(--g2);text-transform:uppercase;letter-spacing:.1em;margin-bottom:12px}
.section-title{font-size:36px;font-weight:800;letter-spacing:-.02em;margin-bottom:12px}
.section-sub{color:var(--muted);font-size:17px;max-width:560px;margin-bottom:48px}
.feat-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px}
.feat-card{padding:28px;border:1px solid var(--line);border-radius:16px;transition:.2s}
.feat-card:hover{border-color:var(--g);box-shadow:0 8px 32px rgba(0,53,39,.08);transform:translateY(-2px)}
.feat-icon{width:44px;height:44px;background:var(--soft);border-radius:10px;display:grid;place-items:center;font-size:22px;margin-bottom:16px}
.feat-card h3{font-size:17px;font-weight:700;margin-bottom:8px}
.feat-card p{font-size:14px;color:var(--muted);line-height:1.6}
/* PRICING */
.pricing{background:var(--soft);padding:80px 5%}
.pricing-inner{max-width:1100px;margin:0 auto}
.pricing-header{text-align:center;margin-bottom:48px}
.plan-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:24px;align-items:start}
.plan-card{background:#fff;border:1.5px solid var(--line);border-radius:20px;padding:32px;position:relative;transition:.2s}
.plan-card.popular{border-color:var(--g);box-shadow:0 20px 48px rgba(0,53,39,.12)}
.plan-badge{position:absolute;top:-12px;left:50%;transform:translateX(-50%);background:var(--g);color:#fff;padding:4px 16px;border-radius:999px;font-size:12px;font-weight:800;white-space:nowrap}
.plan-name{font-size:14px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:var(--muted);margin-bottom:8px}
.plan-price{font-size:36px;font-weight:900;color:var(--ink);line-height:1}
.plan-price small{font-size:15px;font-weight:500;color:var(--muted)}
.plan-desc{font-size:14px;color:var(--muted);margin:12px 0 24px}
.plan-features{list-style:none;display:grid;gap:10px;margin-bottom:28px}
.plan-features li{font-size:14px;display:flex;gap:8px;align-items:flex-start}
.plan-features li::before{content:'✓';color:#16a34a;font-weight:800;flex-shrink:0}
.plan-features li.no::before{content:'✗';color:#ccc}
.plan-features li.no{color:#aaa}
/* CTA */
.cta{background:var(--g);padding:80px 5%;text-align:center}
.cta h2{font-size:40px;font-weight:900;color:#fff;margin-bottom:12px;letter-spacing:-.02em}
.cta p{color:rgba(255,255,255,.75);font-size:17px;margin-bottom:32px}
.cta .btn-light{background:var(--g3);color:var(--g);font-weight:800;padding:14px 32px;font-size:16px;border-radius:12px}
.cta .btn-light:hover{background:#fff}
/* FOOTER */
footer{background:#0d1a14;color:rgba(255,255,255,.5);padding:40px 5%;display:flex;justify-content:space-between;align-items:center;font-size:13px}
footer strong{color:var(--g3)}
@media(max-width:900px){
  .hero{grid-template-columns:1fr;gap:32px}
  .hero h1{font-size:34px}
  .hero-visual{display:none}
  .m-row{grid-template-columns:1fr 1fr}
  .feat-grid,.plan-grid{grid-template-columns:1fr}
  .stats{grid-template-columns:1fr 1fr;gap:20px}
  footer{flex-direction:column;gap:12px;text-align:center}
}
</style>
</head>
<body>
@php
  $formatCount = fn (int $value) => number_format($value, 0, ',', '.');
  $formatMoney = function (int $value): string {
      if ($value >= 1000000000) {
          return 'Rp '.number_format($value / 1000000000, 1, ',', '.').'M';
      }

      if ($value >= 1000000) {
          return 'Rp '.number_format($value / 1000000, 1, ',', '.').'jt';
      }

      return 'Rp '.number_format($value, 0, ',', '.');
  };
@endphp

<nav>
  <div class="nav-brand">
    <div class="nav-mark">SP</div>
    StokPintar
  </div>
  <div class="nav-links">
    <a href="#fitur">Fitur</a>
    <a href="#harga">Harga</a>
    <a href="{{ route('login') }}" class="btn btn-outline" style="padding:8px 16px">Masuk</a>
    <a href="{{ route('register') }}" class="btn btn-primary">Mulai Free</a>
  </div>
</nav>

<!-- HERO -->
<section style="background:linear-gradient(180deg,#f4faf6 0%,#fff 100%)">
<div class="hero">
  <div>
    <div class="hero-badge"><span></span> Data operasional langsung dari platform</div>
    <h1>Stok Pintar,<br><em>Bisnis Lancar.</em></h1>
    <p>Platform manajemen stok & kasir berbasis web untuk warung, toko kelontong, dan UMKM. Tanpa instal aplikasi, langsung pakai dari browser.</p>
    <div class="hero-actions">
      <a href="{{ route('register') }}" class="btn btn-primary" style="padding:14px 28px;font-size:16px">Mulai Free</a>
      <a href="{{ route('demo') }}" class="btn btn-outline" style="padding:14px 28px;font-size:16px">Lihat Demo</a>
    </div>
    <div class="hero-trust">
      Statistik di halaman ini dihitung dari data aplikasi yang tersedia.
    </div>
  </div>
  <div class="hero-visual">
    <div class="mockup-shell">
      <div class="mockup-bar"><span></span><span></span><span></span></div>
      <div class="mockup-screen">
        <div class="m-row">
          <div class="m-card"><div class="m-label">Omzet Hari Ini</div><div class="m-value">Rp 2,4jt</div><div class="m-note">↑ 18% vs kemarin</div></div>
          <div class="m-card"><div class="m-label">Transaksi</div><div class="m-value">47</div><div class="m-note">POS hari ini</div></div>
          <div class="m-card"><div class="m-label">Total Produk</div><div class="m-value">148</div><div class="m-note">Aktif di toko</div></div>
          <div class="m-card"><div class="m-label">Stok Kritis</div><div class="m-value" style="color:var(--rose)">5</div><div class="m-note">Perlu restock</div></div>
        </div>
        <div class="m-chart">
          <div style="font-size:11px;font-weight:700;color:var(--muted)">Omzet 7 Hari Terakhir</div>
          <div class="m-bars">
            <div class="m-bar" style="height:40%"></div>
            <div class="m-bar" style="height:60%"></div>
            <div class="m-bar" style="height:45%"></div>
            <div class="m-bar" style="height:75%"></div>
            <div class="m-bar" style="height:55%"></div>
            <div class="m-bar" style="height:85%"></div>
            <div class="m-bar active" style="height:100%"></div>
          </div>
        </div>
        <div class="m-table">
          <div class="m-th"><span>Produk</span><span>Stok</span><span>Status</span></div>
          <div class="m-td"><span>Kopi Susu Botol</span><span>42 pcs</span><span class="m-ok">OK</span></div>
          <div class="m-td"><span>Roti Cokelat</span><span>4 pcs</span><span class="m-low">Kritis</span></div>
          <div class="m-td"><span>Keripik Pedas</span><span>68 pcs</span><span class="m-ok">OK</span></div>
        </div>
      </div>
    </div>
  </div>
</div>
</section>

<!-- STATS -->
<section class="stats">
  <div><div class="stat-num">{{ $formatCount((int) $landingStats['activeStores']) }}</div><div class="stat-label">Toko Terdaftar</div></div>
  <div><div class="stat-num">{{ $formatMoney((int) $landingStats['processedRevenue']) }}</div><div class="stat-label">Omzet Diproses</div></div>
  <div><div class="stat-num">{{ $formatCount((int) $landingStats['managedProducts']) }}</div><div class="stat-label">Produk Dikelola</div></div>
  <div><div class="stat-num">{{ $formatCount((int) $landingStats['activeTenants']) }}</div><div class="stat-label">Tenant Aktif</div></div>
</section>

<!-- FITUR -->
<section class="features" id="fitur">
  <div class="section-label">Fitur Unggulan</div>
  <h2 class="section-title">Semua yang dibutuhkan toko Anda</h2>
  <p class="section-sub">Dirancang khusus untuk UMKM Indonesia — simpel, cepat, dan bisa langsung dipakai hari ini.</p>
  <div class="feat-grid">
    <div class="feat-card">
      <div class="feat-icon">📦</div>
      <h3>Manajemen Produk Lengkap</h3>
      <p>CRUD produk dengan SKU, kategori, satuan, foto, dan stok minimum. Alert otomatis saat stok hampir habis.</p>
    </div>
    <div class="feat-card">
      <div class="feat-icon">🏪</div>
      <h3>POS Kasir Cepat</h3>
      <p>Kasir bisa cari produk, scan barcode, hitung kembalian otomatis, dan cetak struk — semua dalam hitungan detik.</p>
    </div>
    <div class="feat-card">
      <div class="feat-icon">📊</div>
      <h3>Dashboard & Laporan</h3>
      <p>Grafik omzet harian/bulanan, produk terlaris, dan laporan penjualan lengkap yang bisa dieksport.</p>
    </div>
    <div class="feat-card">
      <div class="feat-icon">🔄</div>
      <h3>Mutasi Stok Masuk/Keluar</h3>
      <p>Riwayat lengkap siapa mengubah stok kapan. Audit trail transparan untuk setiap pergerakan barang.</p>
    </div>
    <div class="feat-card">
      <div class="feat-icon">👥</div>
      <h3>Multi-User dengan Role</h3>
      <p>Owner, Manager, Kasir, dan Staff Gudang — masing-masing dengan hak akses yang tepat.</p>
    </div>
    <div class="feat-card">
      <div class="feat-icon">☁️</div>
      <h3>Berbasis Web, Tanpa Instal</h3>
      <p>Cukup buka browser dari HP atau laptop. Data tersimpan aman di cloud, bisa diakses kapan saja.</p>
    </div>
  </div>
</section>

<!-- PRICING -->
<section class="pricing" id="harga">
  <div class="pricing-inner">
    <div class="pricing-header">
      <div class="section-label" style="text-align:center">Harga Transparan</div>
      <h2 class="section-title" style="text-align:center">Pilih paket, semua tetap full setup</h2>
    </div>
    <div class="plan-grid">
      <div class="plan-card">
        <div class="plan-name">Free</div>
        <div class="plan-price">Rp 0<small>/bulan</small></div>
        <div class="plan-desc">Untuk mulai digital dengan fitur inti yang tetap lengkap.</div>
        <ul class="plan-features">
          <li>1 toko, 50 produk, 2 user</li>
          <li>POS, barcode, stok, dan resep F&B</li>
          <li>Export PDF/Excel</li>
          <li>Laporan 7 hari</li>
        </ul>
        <a href="{{ route('register') }}" class="btn btn-outline" style="width:100%;justify-content:center">Mulai Free</a>
      </div>
      <div class="plan-card">
        <div class="plan-name">Starter</div>
        <div class="plan-price">Rp 49rb<small>/bulan</small></div>
        <div class="plan-desc">Untuk toko yang aktif berjualan setiap hari.</div>
        <ul class="plan-features">
          <li>1 toko, 500 produk, 5 user</li>
          <li>Semua fitur operasional aktif</li>
          <li>Export dan struk branding</li>
          <li>Laporan 30 hari</li>
        </ul>
        <a href="{{ route('register') }}" class="btn btn-outline" style="width:100%;justify-content:center">Pilih Starter</a>
      </div>
      <div class="plan-card popular">
        <div class="plan-badge">Paling Populer</div>
        <div class="plan-name">Pro</div>
        <div class="plan-price">Rp 99rb<small>/bulan</small></div>
        <div class="plan-desc">Untuk bisnis yang mulai butuh cabang dan tim lebih besar.</div>
        <ul class="plan-features">
          <li>5 cabang, produk & user unlimited</li>
          <li>Semua fitur Starter</li>
          <li>API access</li>
          <li>Laporan unlimited</li>
        </ul>
        <a href="{{ route('register') }}" class="btn btn-primary" style="width:100%;justify-content:center">Pilih Pro</a>
      </div>
      <div class="plan-card">
        <div class="plan-name">Business</div>
        <div class="plan-price">Rp 199rb<small>/bulan</small></div>
        <div class="plan-desc">Untuk operasional multi cabang yang butuh dukungan prioritas.</div>
        <ul class="plan-features">
          <li>Cabang, produk, user unlimited</li>
          <li>Semua fitur Pro</li>
          <li>Priority support</li>
          <li>White label ready</li>
        </ul>
        <a href="{{ route('register') }}" class="btn btn-outline" style="width:100%;justify-content:center">Pilih Business</a>
      </div>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="cta">
  <h2>Siap digitalisasi toko Anda?</h2>
  <p>Mulai kelola stok, POS, laporan, barcode, cabang, resep F&B, dan akses tim toko Anda dari satu aplikasi web.</p>
  <a href="{{ route('register') }}" class="btn-light btn">Daftar Sekarang</a>
</section>

<footer>
  <strong>StokPintar</strong>
  <span>© 2025 StokPintar. Dibuat untuk membantu UMKM Indonesia.</span>
</footer>

</body>
</html>
