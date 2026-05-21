# Prompt Desain StokPintar Lite

Dokumen ini adalah prompt desain utama untuk mengembangkan UI StokPintar secara konsisten. Dokumen ini sudah disesuaikan dengan referensi visual dari file zip `stitch_stokpintar_lite_umkm_dashboard.zip` dan harus meniru bahasa desain yang ada di dalamnya.

## Acuan Referensi

Seluruh prompt di bawah harus meniru karakter desain dari kumpulan screen berikut:

- `dashboard_stokpintar_lite`
- `dashboard_pro_stokpintar_lite`
- `produk_stok_stokpintar_lite`
- `produk_stok_pro_stokpintar_lite`
- `tambah_produk_stokpintar_lite`
- `tambah_produk_pro_stokpintar_lite`
- `pos_kasir_stokpintar_lite`
- `pos_kasir_pro_stokpintar_lite`
- `laporan_analisis_stokpintar_lite`
- `laporan_pro_stokpintar_lite`
- `riwayat_stok_stokpintar_lite`
- `riwayat_stok_pro_stokpintar_lite`
- `login_stokpintar_lite`
- `register_toko_stokpintar_lite`

Artinya, saat meminta AI membuat desain baru, jangan hanya memakai tema "dashboard modern" secara umum. AI harus meniru ritme layout, kepadatan informasi, bentuk komponen, warna, dan suasana visual yang sudah terlihat di referensi Stitch tersebut.

## Fundamental Desain

Sebelum mendesain layar, pahami dulu fungsi aplikasinya.

StokPintar Lite adalah aplikasi manajemen stok dan POS untuk UMKM. Pengguna utamanya adalah owner toko dan kasir. Mereka membuka aplikasi untuk bekerja cepat: melihat stok, menjual produk, mencatat mutasi, dan membaca laporan harian.

Karena itu, desain harus mengutamakan:

- Kejelasan informasi: angka stok, omzet, harga, total transaksi, dan status harus cepat terbaca.
- Efisiensi kerja: aksi penting seperti `Tambah Produk`, `Buka POS`, `Catat`, dan `Proses Transaksi` harus langsung terlihat.
- Konsistensi visual: warna status, tombol, tabel, form, panel, dan kartu harus punya arti yang sama di semua halaman.
- Responsif operasional: desktop nyaman untuk dashboard dan tabel, mobile nyaman untuk POS dan pengecekan cepat.
- Tampilan profesional: rapi, utilitarian, modern, ringan, tidak seperti landing page marketing.

## DNA Visual yang Harus Ditiru

Desain di zip punya ciri khas yang jelas. Ini wajib ditiru:

- Sidebar kiri permanen lebar sekitar `280px`.
- Background aplikasi sangat terang, lembut, sedikit warm-neutral, bukan putih polos.
- Card dan panel putih dengan border 1px tipis, bukan shadow berat.
- Typography Inter dengan heading besar tegas dan body text ringkas.
- Active navigation memakai hijau mint lembut dengan aksen border kanan hijau tua.
- Topbar bersih dengan search, CTA utama, ikon notifikasi/setting, dan avatar.
- Kartu metrik berbentuk rounded besar 12px sampai 16px pada versi pro, lebih sederhana pada versi lite.
- Tabel lebar, rapi, memakai header abu terang, garis horizontal jelas, dan angka yang mudah dipindai.
- POS menonjolkan katalog produk di kiri dan panel checkout tinggi di kanan.
- Register dan login memakai visual retail yang tenang, bukan hero marketing bombastis.

## Token Visual Utama

Gunakan sistem visual berikut agar hasil desain terasa sama dengan referensi:

- Font utama: `Inter`.
- Background aplikasi utama: `#f9faf7`.
- Surface/card/panel: `#ffffff`.
- Surface rendah: `#f3f4f1`.
- Surface container: `#edeeeb`.
- Surface hover/highlight: `#e7e8e6`.
- Border utama: `#c0c8c3`.
- Teks utama: `#1a1c1b`.
- Teks sekunder: `#404944`.
- Primary gelap: `#001e15`.
- Primary container: `#003527`.
- Secondary hijau operasional: `#2b6954`.
- Secondary soft / fixed: `#b0f0d6`.
- Success mint/soft accent: `#adedd3`.
- Error: `#ba1a1a`.
- Error tint: `#ffdad6`.
- Amber transaksi tetap boleh dipakai untuk checkout atau CTA POS seperti di implementasi sebelumnya: `#ff9939`.

Catatan penting:

- Referensi zip memakai basis hijau tua dan hijau mint, bukan dominan biru.
- Jika butuh warna analytics, gunakan hijau operasional atau slate soft yang tetap senapas dengan palette ini.
- Jangan memakai gradient besar kecuali untuk aksen terbatas di versi pro.

## Radius, Border, dan Elevation

- Radius default input, button, card, dan panel: `8px`.
- Untuk versi pro, card utama dan product tile bisa memakai `12px` sampai `16px`.
- Border 1px adalah alat struktur utama.
- Shadow sangat halus dan hanya boleh dipakai di versi pro atau area penting seperti product tile hover, dropdown, atau checkout panel.
- Hindari shadow tebal, glassmorphism berlebihan, dan blur besar di seluruh aplikasi.

## Tipografi

- Heading utama dashboard: `32px`, tebal, tracking rapat.
- Section heading: `24px`.
- Body umum: `14px`.
- Label kecil: `13px`, semi-bold, uppercase ringan.
- Angka uang, invoice, stok, dan metrik wajib memakai tabular numerals.
- Fokuskan hierarki agar informasi utama bisa dipahami dalam 3 detik.

## Prompt Utama

Rancang antarmuka web app bernama StokPintar Lite, sebuah aplikasi manajemen stok dan POS untuk UMKM Indonesia. Tiru bahasa desain dari referensi Stitch `stitch_stokpintar_lite_umkm_dashboard.zip`.

Desain harus terasa seperti alat kerja operasional: modern, bersih, padat informasi, profesional, cepat dipindai, dan tidak dekoratif berlebihan. Gunakan light mode. Prioritaskan keterbacaan tabel, panel checkout, filter, metrik, dan CTA utama.

Gunakan app shell yang sama seperti referensi:

- Sidebar kiri permanen `280px`.
- Topbar tipis dan bersih di area konten.
- Search field dengan bentuk rounded lebar.
- CTA utama berwarna hijau tua atau amber tergantung konteks.
- Ikon memakai Material Symbols.
- Panel utama memakai background putih dengan border tipis.

Jika AI diminta membuat desain baru, arahkan agar hasilnya menyerupai sistem internal bisnis yang sudah jadi, bukan konsep UI generik.

## Prompt Dashboard

Buat dashboard operasional untuk owner toko dengan struktur dan nuansa yang meniru `dashboard_stokpintar_lite` dan `dashboard_pro_stokpintar_lite`.

Aturan layout:

- Sidebar kiri permanen `280px`.
- Topbar berisi search, CTA `Tambah Produk`, CTA `Buka POS`, ikon notifikasi dan setting, serta avatar.
- Konten utama punya padding lebar dan grid yang rapi.

Urutan konten:

1. Header besar dengan judul `Dashboard Manajemen Stok UMKM` atau versi pro `Ringkasan Operasional`.
2. Empat card metrik: omzet hari ini, total produk, stok kritis, dan role/transaksi aktif.
3. Grafik omzet 7 hari terakhir.
4. Panel transaksi terbaru di sisi kanan atau berdampingan dengan chart.
5. Tabel alert stok menipis.
6. Tabel riwayat mutasi stok.

Detail visual yang harus ditiru:

- Card metrik lite: sederhana, bersih, putih, border tipis, icon box kecil.
- Card metrik pro: lebih premium, rounded lebih besar, ikon latar lembut, angka lebih bold, hover halus.
- Card stok kritis memakai latar merah muda lembut.
- Chart area terasa lapang, ringan, dan minim ornamen.
- Tabel bawah memakai header abu terang dengan badge status merah/hijau.

Tujuan:

Owner toko harus langsung tahu omzet, stok kritis, dan transaksi terbaru dalam beberapa detik.

## Prompt Produk & Stok

Buat halaman inventory yang meniru `produk_stok_stokpintar_lite` dan `produk_stok_pro_stokpintar_lite`.

Struktur utama:

- Header halaman `Produk & Stok`.
- CTA `Tambah Produk`.
- Tombol sekunder `Dashboard`.
- Filter pencarian produk/SKU.
- Ringkasan metrik total SKU aktif, perlu reorder, dan nilai stok.
- Tabel produk besar.
- Area mutasi cepat yang ringkas.

Hal-hal yang wajib ditiru:

- Search panel berdiri sendiri sebagai card filter.
- Tiga kartu statistik horizontal dengan ikon bulat atau icon container.
- Tabel produk dengan kolom: produk, SKU, kategori, stok, minimum, harga, status, aksi.
- Pada versi pro, area `Mutasi Cepat` tampil di atas tabel sebagai bar aksi horizontal.
- Product row memakai thumbnail atau inisial, dengan teks kecil pendukung.
- Harga rata kanan atau tampak menonjol.
- Badge stok aman hijau lembut, badge stok kritis merah lembut.
- Tombol edit/hapus kecil, rapi, tidak dominan berlebihan.

Rasa halaman:

Ini harus terasa seperti layar inventory sungguhan, bukan data table template biasa.

## Prompt Tambah / Edit Produk

Buat halaman form produk yang meniru `tambah_produk_stokpintar_lite` dan `tambah_produk_pro_stokpintar_lite`.

Struktur:

- Header jelas dengan judul `Tambah Produk` atau `Edit Produk`.
- Subtitle singkat yang menjelaskan tujuan form.
- Tombol kembali.
- Form utama dalam panel putih bordered.

Field:

- Nama Produk
- SKU
- Kategori
- Kategori Baru
- Satuan
- Harga Modal
- Harga Jual
- Stok Awal
- Stok Minimum
- Upload foto produk

Gaya yang harus ditiru:

- Label selalu terlihat di atas field.
- Grid dua kolom di desktop.
- Input rounded 8px dengan focus ring hijau lembut.
- Form terasa rapi, cepat, dan nyaman untuk diisi berulang.
- Bagian aksi bawah selalu jelas: `Batal` dan `Simpan`/`Tambah Produk`.

## Prompt POS Kasir

Buat layar POS yang meniru `pos_kasir_stokpintar_lite` dan `pos_kasir_pro_stokpintar_lite`.

Ini adalah halaman paling penting untuk pengalaman kasir.

Struktur utama desktop:

- Sidebar kiri permanen.
- Topbar dengan search besar, segmented control kategori, tombol `Simpan Draft`, notifikasi, dan avatar.
- Workspace terbagi dua:
  - kiri: katalog produk
  - kanan: panel keranjang dan pembayaran

Bagian katalog produk:

- Product tile berbentuk card tegak dengan foto besar atau inisial.
- Ada kategori kecil uppercase di atas nama produk.
- Nama produk dibuat ringkas jika panjang.
- Harga besar dan tegas.
- Status stok di bawah harga.
- Tombol tambah berupa tombol hijau dengan ikon keranjang atau plus di sudut bawah kanan.
- Pada versi pro, product card bisa punya shadow halus dan animasi hover lembut.

Bagian checkout:

- Judul `Keranjang`.
- Badge jumlah item.
- ID transaksi.
- Daftar item terpilih dengan thumbnail kecil.
- Stepper plus/minus.
- Total per item.
- Ringkasan subtotal, pajak, total.
- Metode pembayaran berupa segmented/tile button: tunai, QRIS, bank/transfer.
- Field uang diterima sangat besar dan mudah diisi.
- Area kembalian menonjol tapi tetap bersih.
- Tombol aksi utama besar di bagian bawah: `Proses Transaksi`, `Cetak Struk & Selesai`, atau padanan yang setara.

Aturan visual:

- Konteks transaksi boleh memakai amber atau hijau kuat, sesuai referensi.
- Jangan menyembunyikan checkout.
- Layout harus membuat kasir bisa menambah item dan menyelesaikan pembayaran tanpa kebingungan.

Mobile POS:

- Fokus pada pencarian, grid produk 2 kolom, dan akses cepat ke keranjang.
- Input dan tombol minimal 48px.
- Hindari tabel.

## Prompt Laporan

Buat halaman laporan yang meniru `laporan_analisis_stokpintar_lite` dan `laporan_pro_stokpintar_lite`.

Struktur:

- Header `Laporan & Analisis Penjualan`.
- Tombol `Dashboard`.
- Filter bulan dan tahun dalam panel atas.
- Tiga kartu metrik: total pendapatan, total transaksi, rata-rata keranjang.
- Tabel transaksi terbaru.

Detail yang harus ditiru:

- Filter tampil ringkas, bersih, horizontal.
- Kartu metrik memiliki icon container, angka besar, dan delta kecil bila perlu.
- Tabel transaksi berisi invoice, tanggal, kasir, metode, status, total.
- Kolom uang harus rata kanan dan tabular.
- Status ditampilkan sebagai badge.
- Pada versi pro, tombol export atau filter tambahan bisa muncul di header tabel.

Halaman ini harus terasa analitis tapi tetap sederhana untuk owner UMKM.

## Prompt Riwayat Stok

Buat halaman audit trail yang meniru `riwayat_stok_stokpintar_lite` dan `riwayat_stok_pro_stokpintar_lite`.

Konten:

- Header `Riwayat Stok`.
- Tombol kembali ke `Produk & Stok` atau aksi terkait bila perlu.
- Tabel mutasi stok besar.

Kolom utama:

- Waktu
- Produk
- SKU
- Tipe
- Qty
- Stok Akhir
- Oleh
- Catatan

Aturan:

- Tipe mutasi positif hijau.
- Tipe mutasi negatif merah.
- Baris mudah dibaca dan dipindai cepat.
- Tidak perlu dekorasi ekstra.
- Halaman ini harus terasa presisi, rapi, dan dapat dipercaya.

## Prompt Auth

Buat halaman login dan register yang meniru `login_stokpintar_lite` dan `register_toko_stokpintar_lite`.

### Login

Desktop:

- Layout split dua kolom.
- Kolom kiri adalah visual retail/business dengan overlay hijau tua.
- Brand muncul jelas di atas area visual.
- Headline besar dan copy singkat.
- Kolom kanan adalah form login bersih.

Elemen form:

- Label uppercase ringan.
- Field email dengan ikon.
- Field password dengan ikon dan visibility toggle.
- Link `Lupa Password?`.
- CTA utama `Masuk Sekarang`.
- Divider `Atau`.
- Tombol `Masuk dengan Google`.
- Link daftar akun.
- Footer links kecil.

Rasa visual:

- Lebih elegan dan dewasa daripada template login SaaS biasa.
- Tetap ringan dan tidak berlebihan.

### Register

Desktop:

- Header atas tipis dengan brand dan tombol login.
- Dua kolom utama.
- Kiri berisi benefit card dan banner nilai produk.
- Kanan berisi form register toko.

Yang harus ditiru:

- Feature cards kecil yang menjelaskan manfaat.
- Banner hijau tua dengan kutipan atau positioning statement.
- Form register bersih, fokus, dan percaya diri.
- Checkbox syarat & ketentuan terlihat jelas.

Mobile auth:

- Fokus pada brand dan form.
- Visual besar boleh dikurangi atau disembunyikan agar proses cepat.

## Aturan Implementasi Blade/CSS

Jika mengimplementasikan desain ini di project Laravel Blade saat ini:

- Pertahankan file layout utama di `resources/views/layouts/app.blade.php`.
- Gunakan class global yang sudah ada seperti `.shell`, `.sidebar`, `.topbar`, `.card`, `.btn`, `.badge`, `.metric-card`, `.table-wrap`, `.field`, `.pos-*`.
- Tiru style dari referensi zip ke dalam struktur class yang sudah ada, jangan membuat sistem baru yang bertabrakan.
- Jangan membuat token warna baru yang memecah konsistensi referensi Stitch.
- Hindari inline style baru kecuali sangat kecil dan benar-benar perlu.
- Pastikan semua halaman tetap responsif di breakpoint tablet dan mobile.
- Jangan mengubah business logic controller saat hanya memperbaiki tampilan.
- Untuk ikon, gunakan Material Symbols yang sudah dipakai project.

## Prompt Khusus Untuk AI

Jika prompt ini dipakai ke Stitch AI atau generator lain, tambahkan arahan berikut:

- "Tiru bahasa desain dari referensi StokPintar Lite dan Pro."
- "Gunakan sidebar 280px, topbar bersih, panel putih, border tipis, dan tone hijau operasional."
- "Jangan hasilkan dashboard generik atau marketing layout."
- "Tiru kepadatan informasi, struktur tabel, dan checkout panel seperti aplikasi internal bisnis."
- "Utamakan fungsi operasional harian owner dan kasir."

## Checklist Evaluasi

Gunakan checklist ini sebelum menganggap desain selesai:

- Apakah hasilnya terasa mirip dengan referensi Stitch di zip?
- Apakah sidebar, topbar, card, dan tabel memakai ritme visual yang sama?
- Apakah informasi penting terlihat dalam 3 detik?
- Apakah tombol utama per halaman jelas dan tidak kalah oleh elemen lain?
- Apakah warna status konsisten?
- Apakah angka uang dan stok mudah dibandingkan?
- Apakah tabel desktop tetap rapi?
- Apakah mobile tidak memaksa user membaca tabel lebar?
- Apakah POS bisa dipakai cepat oleh kasir?
- Apakah desain terasa seperti aplikasi kerja, bukan landing page?
