# StokPintar Project Structure

StokPintar Lite adalah aplikasi manajemen stok untuk UMKM dengan POS kasir, laporan, dan riwayat stok. Fondasi multi-tenant tetap ada di codebase, tetapi versi yang aktif untuk presentasi saat ini adalah versi lite.

## Backend Laravel

- `app/Models/Tenant.php`: akun bisnis/toko utama pelanggan SaaS.
- `app/Models/SubscriptionPlan.php`: paket Gratis, Starter, Pro.
- `app/Models/Store.php`: cabang/outlet di dalam satu tenant.
- `app/Models/Product.php`: produk, stok, SKU, harga, dan batas stok minimum.
- `app/Models/StockMutation.php`: audit trail setiap perubahan stok.
- `app/Models/Sale.php` dan `app/Models/SaleItem.php`: transaksi POS dan detail item.
- `app/Models/Concerns/BelongsToTenant.php`: trait untuk model yang wajib terisolasi tenant.
- `app/Models/Scopes/TenantScope.php`: global scope `tenant_id`.
- `app/Support/TenantManager.php`: context tenant aktif.
- `app/Services/POS/CheckoutService.php`: proses transaksi kasir, update stok, dan mutasi stok.

## Multi-Tenant Rules

Model yang memakai `BelongsToTenant` otomatis:

- menambahkan filter `where tenant_id = current tenant`;
- mengisi `tenant_id` saat create jika tenant context tersedia;
- tetap bisa diakses lintas tenant dengan `withoutGlobalScopes()` untuk kebutuhan admin platform, seeder, atau job internal.

## Suggested Modules

- `Auth`: register tenant, login, logout, invite user.
- `Inventory`: kategori, produk, mutasi stok, alert stok menipis.
- `POS`: checkout, cetak struk, riwayat penjualan.
- `Reports`: omzet, produk terlaris, stok kritis, export PDF/Excel.
- `Billing`: toggle manual subscription dulu, Midtrans setelah core stabil.
- `Platform Admin`: kelola tenant, paket, status subscription.

## Demo Accounts

Seeder membuat:

- Email: `owner@stokpintar.test`
- Password: `password`
- Tenant: `Toko Demo`
