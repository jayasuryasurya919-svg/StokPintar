# Acuan Desain StokPintar

Sumber acuan: `stitch_stokpintar_saas_dashboard (2).zip`, terutama file `operational_excellence/DESIGN.md` dan layar referensi dashboard, POS, produk, laporan, login, register, subscription, admin tenant, dan versi mobile.

## Arah Visual

StokPintar harus terasa seperti alat kerja operasional UMKM: rapi, cepat dipindai, tegas, dan tidak dekoratif berlebihan. Prioritaskan kepadatan informasi, kontras yang jelas, tabel/list yang mudah dibaca, dan aksi yang langsung terlihat.

Gaya utama:

- Corporate modern, minimal, dan utilitarian.
- Light mode sebagai default untuk toko, gudang, dan kasir.
- Struktur dibangun dari border dan layer warna, bukan shadow berat.
- Radius konsisten 8px agar tetap profesional.
- Angka stok, harga, omzet, dan ID memakai tabular numerals.

## Token Warna

Gunakan warna berdasarkan fungsi, bukan sekadar variasi visual.

| Fungsi | Token | Hex | Pemakaian |
| --- | --- | --- | --- |
| Background | `--sp-background` | `#f8f9ff` | Kanvas halaman |
| Surface | `--sp-surface` | `#ffffff` | Card, panel, form |
| Surface low | `--sp-surface-low` | `#eff4ff` | Header tabel, filter bar |
| Surface high | `--sp-surface-high` | `#dce9ff` | Active/hover nav |
| Text utama | `--sp-ink` | `#0b1c30` | Judul, isi penting |
| Text sekunder | `--sp-muted` | `#404944` | Subtitle, metadata |
| Border | `--sp-line` | `#bfc9c3` | Border card/input/table |
| Primary emerald | `--sp-primary` | `#003527` | Brand, sidebar active, tombol utama |
| Primary container | `--sp-primary-container` | `#064e3b` | Brand mark, panel upgrade |
| Success tint | `--sp-success-soft` | `#b0f0d6` | Badge sukses/stok aman |
| Secondary blue | `--sp-secondary` | `#0051d5` | Grafik, insight analytics, link info |
| Transaction amber | `--sp-amber` | `#ff9939` | Pembayaran, uang, pending |
| Critical rose | `--sp-error` | `#ba1a1a` | Stok kritis, hapus, error |
| Critical tint | `--sp-error-soft` | `#ffdad6` | Badge stok rendah/error |

Catatan: warna hijau dipakai untuk identitas dan aksi utama. Jangan pakai hijau untuk grafik analitik jika ada data pembanding; gunakan biru.

## Tipografi

Font utama: `Inter`, fallback `ui-sans-serif, system-ui, sans-serif`.

| Style | Ukuran | Line height | Weight | Pemakaian |
| --- | ---: | ---: | ---: | --- |
| Headline lg | 32px | 40px | 700 | Judul besar halaman auth/landing internal |
| Headline md | 24px | 32px | 600 | Judul halaman dashboard/POS |
| Headline sm | 20px | 28px | 600 | Judul section/card besar |
| Body lg | 16px | 24px | 400 | Paragraf penting |
| Body md | 14px | 20px | 400 | Isi tabel, form, nav |
| Body sm | 12px | 16px | 400 | Metadata kecil |
| Label md | 14px | 20px | 600 | Tombol, label form, chip |

Aturan:

- Judul boleh memakai letter spacing sedikit ketat, tetapi teks UI biasa tetap `0`.
- Label tabel boleh uppercase dengan tracking ringan.
- Harga, stok, total, invoice, dan metrik wajib memakai `font-variant-numeric: tabular-nums`.

## Layout

Gunakan skala 8px:

- Jarak kecil: 8px.
- Gutter komponen: 16px.
- Padding panel/card besar: 24px.
- Margin desktop: 32px.
- Margin mobile: 16px.

Desktop:

- Sidebar tetap 280px.
- Konten utama mulai setelah sidebar.
- Dashboard memakai grid metrik 4 kolom.
- POS memakai split workspace: grid produk di kiri dan panel transaksi di kanan.

Tablet:

- Sidebar boleh menjadi drawer/collapsed.
- Grid dashboard turun ke 2 kolom.

Mobile:

- Margin 16px.
- Tabel lebar diganti list/card ringkas jika memungkinkan.
- Tombol utama minimal 48px tinggi.
- POS mobile fokus pada pencarian, daftar produk, dan cart yang mudah dibuka/tutup.

## Komponen Dasar

### Sidebar

- Lebar desktop 280px.
- Background surface terang dengan border kanan.
- Active nav: teks primary, background `surface-high`, border kanan 4px primary.
- Item nav memakai ikon + teks, tinggi sekitar 44px.
- Area upgrade/plan berada di bawah.

### Header Halaman

- Berisi judul, subtitle singkat, dan aksi kanan.
- Border bawah 1px.
- Hindari hero marketing untuk area aplikasi; ini alat kerja.

### Card Metrik

- Surface putih, border 1px, radius 8px, padding 24px.
- Label kecil uppercase.
- Angka besar memakai warna sesuai konteks.
- Card stok kritis memakai tint merah, bukan hanya teks merah.

### Tabel Data

- Header tabel memakai surface low.
- Border horizontal saja untuk mengurangi noise.
- Row hover memakai tint primary yang sangat lembut.
- Angka rata kanan, teks rata kiri.
- Status memakai chip, bukan teks polos.

### Form

- Label selalu terlihat.
- Input tinggi 40px desktop, 48px mobile.
- Border 1px `outline-variant`.
- Focus: border primary dan ring tipis primary.
- Placeholder harus lebih redup dari teks isi.

### Tombol

- Primary: background emerald, teks putih.
- Secondary/outline: surface putih, border, teks ink/primary.
- Transaction: amber untuk pembayaran/checkout/complete sale.
- Danger: rose untuk hapus dan aksi destruktif.
- Tinggi desktop 40px, mobile 48px.
- Radius 8px.

### Badge/Chip

- Pill radius.
- Stok aman: emerald tint.
- Stok rendah/kritis: error tint.
- Pending/pembayaran: amber tint.
- Info/analytics: blue tint.

### POS Product Card

- Gunakan gambar produk bila tersedia.
- Aspect ratio gambar 1:1.
- Nama produk maksimal 1-2 baris.
- Harga diberi warna primary dan tabular numerals.
- Tombol tambah sebaiknya ikon `+` dalam tombol kecil yang konsisten.

## Pola Layar

### Dashboard

Urutan prioritas:

1. Header: judul, subtitle, aksi `Tambah Produk` dan `Buka POS`.
2. Empat metrik utama: omzet hari ini, total produk, stok kritis, transaksi hari ini.
3. Tabel prioritas stok/produk.
4. Grafik/analytics memakai warna biru.
5. Panel kanan opsional untuk alert stok, subscription, atau aktivitas.

### Produk & Stok

- Awali dengan search, filter kategori/status, dan aksi tambah produk.
- Tabel desktop: produk, SKU, kategori, stok, harga, status, aksi.
- Mobile: list item dengan status stok dan aksi cepat.
- Stok kritis harus mudah terlihat tanpa membaca angka detail.

### POS Kasir

- Fokus utama adalah pencarian/scan barcode.
- Kategori dibuat segmented control.
- Grid produk kiri, cart/transaksi kanan di desktop.
- Tombol checkout memakai amber jika konteksnya pembayaran.
- Panel total harus sticky/terlihat terus.

### Auth

- Desktop: panel visual retail di kiri, form di kanan.
- Mobile: hanya brand ringkas dan form.
- Form login/register tetap bersih, tidak penuh dekorasi.

### Laporan

- Gunakan filter tanggal/periode di atas.
- Metrik ringkas dulu, grafik setelahnya, tabel transaksi/detail di bawah.
- Data uang memakai amber hanya pada konteks transaksi/payment, bukan semua angka.

## Implementasi Di Laravel Saat Ini

Project saat ini memakai Blade dengan style global di `resources/views/layouts/app.blade.php`. Untuk menyelaraskan dengan acuan:

- Ganti sidebar gelap menjadi sidebar surface terang dengan border kanan.
- Samakan token CSS lama (`--ink`, `--muted`, `--line`, `--soft`, `--panel`, `--green`, `--blue`, `--amber`, `--rose`) ke palet di atas.
- Naikkan lebar sidebar dari 248px ke 280px di desktop.
- Gunakan `#f8f9ff` untuk background aplikasi.
- Gunakan card border tanpa shadow.
- Pastikan semua `.price`, `.metric-value`, stok, dan angka total memakai tabular numerals.
- Pertahankan radius 8px untuk tombol, input, card, dan nav.

Contoh mapping token CSS:

```css
:root {
    --ink: #0b1c30;
    --muted: #404944;
    --line: #bfc9c3;
    --soft: #f8f9ff;
    --panel: #ffffff;
    --green: #003527;
    --blue: #0051d5;
    --amber: #ff9939;
    --rose: #ba1a1a;
}
```

## Checklist Saat Membuat Layar Baru

- Apakah informasi paling penting terlihat dalam 3 detik?
- Apakah angka uang/stok rata dan mudah dibandingkan?
- Apakah warna status dipakai konsisten sesuai fungsi?
- Apakah tombol utama hanya satu per area kerja?
- Apakah mobile tetap nyaman tanpa tabel melebar jika datanya bisa dibuat list?
- Apakah tampilan terasa seperti aplikasi kerja, bukan landing page?
