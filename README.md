# StokPintar

StokPintar adalah aplikasi POS dan manajemen stok multi-tenant untuk toko kecil sampai bisnis dengan banyak cabang. Aplikasi ini memakai Laravel dan sudah menyiapkan role, paket langganan, POS, laporan, riwayat stok, demo publik, dan administrasi platform.

## Fitur Utama

- Multi-tenant toko dengan isolasi data per bisnis.
- Role `owner`, `manager`, `cashier`, dan `platform_admin`.
- Paket `Free`, `Starter`, `Pro`, dan `Business` yang tetap setup-ready untuk semua fitur.
- POS kasir dengan keranjang, metode bayar tunai/QRIS/transfer, struk, dan scan barcode.
- Produk, stok, kategori, cabang, mutasi stok, dan riwayat stok.
- Dukungan menu/resep untuk bisnis makanan dan minuman siap saji.
- Laporan penjualan, laporan kasir, export PDF/Excel, dan pembatasan retensi laporan.
- Undangan tim, reset password, halaman legal, dan demo guest.
- Panel platform admin untuk tenant dan paket.

## Role

- `owner`: mengelola bisnis, toko/cabang, paket, produk, stok, laporan, tim, dan setting utama.
- `manager`: mengelola operasional toko, produk, stok, POS, laporan operasional, dan tim terbatas sesuai akses.
- `cashier`: menjalankan POS, mencetak struk, melihat transaksi sendiri, dan bekerja sesuai jam/cabang akses.
- `platform_admin`: mengelola tenant, status tenant, paket, dan operasional platform.

## Setup Lokal

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan storage:link
php artisan serve
```

Untuk build asset:

```bash
npm run build
```

Untuk menjalankan test:

```bash
php artisan test
```

## Demo Data

Seeder membuat beberapa toko contoh lengkap dengan owner, manager, kasir, produk, stok, transaksi, dan laporan. Jalankan ulang demo data dengan:

```bash
php artisan migrate:fresh --seed
```

## Production Checklist

- Ubah `APP_ENV=production`, `APP_DEBUG=false`, dan `APP_URL` ke domain asli.
- Pakai database MySQL/PostgreSQL production dan jalankan `php artisan migrate --force`.
- Isi SMTP production agar undangan tim dan reset password benar-benar terkirim.
- Jalankan queue worker untuk email dan pekerjaan background.
- Isi storage/public disk sesuai hosting dan jalankan `php artisan storage:link`.
- Pilih `PAYMENT_PROVIDER=manual`, `midtrans`, atau `xendit` sesuai integrasi bisnis.
- Isi key payment gateway hanya di `.env` production, jangan commit key rahasia.
- Pakai HTTPS agar akses kamera barcode browser bisa aktif di production.

## Payment Gateway

Konfigurasi payment disiapkan di `.env.example` dan `config/services.php`.

- `PAYMENT_PROVIDER=manual`: pembayaran langganan ditangani manual/admin.
- `PAYMENT_PROVIDER=midtrans`: isi `MIDTRANS_SERVER_KEY`, `MIDTRANS_CLIENT_KEY`, dan `MIDTRANS_IS_PRODUCTION`.
- `PAYMENT_PROVIDER=xendit`: isi `XENDIT_SECRET_KEY` dan `XENDIT_CALLBACK_TOKEN`.

Integrasi charge otomatis dapat ditambahkan di atas konfigurasi ini tanpa menyimpan credential di kode.

## Barcode Scanner

POS mendukung tiga pola scan:

- Browser modern dengan `BarcodeDetector`.
- Fallback kamera berbasis ZXing saat browser tidak mendukung detector native.
- Scanner USB/HP yang mengetik barcode ke kolom pencarian, lalu tekan Enter.

Untuk kamera browser, gunakan HTTPS di production dan izinkan permission kamera.
