Oke, kita susun konsep distribusinya dulu dalam bentuk catatan yang kepikiran UI user & dashboard admin, ya 👇

---

## 1. Gambaran Umum Arsitektur

* **Node Pusat (Laptop 1 / Central DB)**

  * Menyimpan **semua data**: semua user, semua orders, semua warehouses, semua stok, semua region.
  * Dipakai untuk:

    * Dashboard admin pusat (monitor seluruh bisnis).
    * Sinkronisasi data ke node-node region.
* **Node Region (Laptop 2 / DB Regional, contoh: BDG)**

  * Menyimpan:

    * Data **global** (copy): `products`, `categories`.
    * Data **subset regional**: `warehouses`, `warehouse_items`, `users`, `cart_items`, `orders`, `order_items` khusus `region_code = 'BDG'`.
  * Dipakai untuk:

    * Website yang diakses user region BDG.
    * Dashboard admin regional BDG.

---

## 2. Konsep Data Terdistribusi (Catatan Singkat)

* **Direplikasi ke semua node (global):**

  * `categories` → semua kategori sama di semua region.
  * `products` → semua produk sama, harga sama (kecuali nanti ada konsep harga per region).

* **Di-shard / dibagi per region (regional):**

  * `warehouses` → difilter `region_code`.
  * `warehouse_items` → stok hanya gudang region tersebut.
  * `users` → bisa difilter `region_code` (user BDG disimpan di DB BDG).
  * `cart_items`, `orders`, `order_items` → mengikuti user + stok di region tersebut.

---

## 3. Dari Sisi USER (Tampilan Web Store)

### 3.1. Penentuan Region di UI

* **Saat user membuka web pertama kali:**

  * Opsi:

    * Deteksi lokasi kasar (IP / manual dropdown).
    * Tampilkan modal/popup:

      > “Pilih lokasi pengiriman kamu:
      > [ Jakarta ] [ Bandung ] [ Surabaya ]”
  * Pilihan user → simpan di session/localStorage → misal `selected_region = 'BDG'`.

* **Dampaknya ke backend:**

  * Aplikasi akan mengarahkan request ke:

    * **DB BDG (Laptop 2)** kalau `region = 'BDG'`
    * **DB Pusat (Laptop 1)** atau DB lain kalau `region = 'JKT'`, dll.

---

### 3.2. Halaman Katalog Produk (UI User)

* List produk **diambil dari tabel `products`** (copy global, sama di semua node).

* Namun, **availability + stok + estimasi pengiriman** diambil dari:

  * `warehouse_items` yang join ke `warehouses` dengan `region_code = selected_region`.

* Di UI, misalnya:

  * Nama produk, harga, foto → dari `products`.
  * Badge / info tambahan:

    * ✅ “Tersedia di Gudang Bandung (Stok: 30)”
    * ❌ “Tidak tersedia di region kamu” (kalau stok 0 untuk region tersebut).

* Kalau produk tidak ada di gudang region itu:

  * Bisa:

    * Disembunyikan dari list, **atau**
    * Ditampilkan dengan status “Tidak tersedia di lokasi kamu”.

---

### 3.3. Halaman Detail Produk

* Sama konsepnya:

  * Produk tetap 1 (dari `products`).
  * Panel informasi stok:

    * Stok per gudang di region user:

      > “Dikirim dari: Gudang Bandung
      > Stok tersedia: 30
      > Estimasi tiba: 2–3 hari”

---

### 3.4. Cart & Checkout (User)

* Saat user klik “Tambah ke Keranjang”:

  * `cart_items.warehouse_item_id` **selalu mengacu ke stok gudang region user**.
  * `user_id` → user di DB region terkait.
* Saat checkout:

  * `orders` & `order_items` dibuat **di DB region**.
  * Stok di `warehouse_items.stock` dikurangi **di region itu**, bukan di pusat.

> Konsepnya: **user cuma berinteraksi dengan “versi bisnis” di regionnya**, meskipun definisi produk global.

---

## 4. Dari Sisi ADMIN (Dashboard)

Kita bedakan 2 level:

* **Admin Pusat (Super Admin) – akses ke DB Laptop 1**
* **Admin Regional – akses ke DB Laptop 2 (per region)**

---

### 4.1. Dashboard Admin Pusat (Laptop 1)

**Tujuan:** melihat dan mengelola **seluruh bisnis** lintas region.

**Menu / Modul Utama:**

1. **Overview Global**

   * Total order semua region.
   * Omset per region (JKT, BDG, SBY).
   * Grafik penjualan per hari/per minggu per region.
   * Top selling products secara global.

2. **Manajemen Produk & Kategori**

   * Kelola `products` dan `categories` sekali di pusat.
   * Setelah ada perubahan → replikasi ke node region (BDG, JKT, SBY).
   * UI:

     * Form create/edit/delete produk.
     * Upload foto.
     * Set harga global.

3. **Monitoring Stok Lintas Region**

   * Tabel gabungan:

     * `products` + `warehouse_items` + `warehouses`.
   * Kolom:

     * Product Name | Region | Warehouse Name | Stock | Status (Low/Normal).
   * Admin pusat bisa:

     * Lihat stok kritis di region tertentu.
     * Ambil keputusan restock antar region (level bisnis).

4. **Monitoring Order Lintas Region**

   * List `orders` semua region (karena di Laptop 1 terkumpul semua).
   * Filter by:

     * Region, tanggal, status, user.
   * Detail order:

     * Join `order_items`, `warehouse_items`, `products`, `users`.

5. **User Management (Opsional)**

   * Melihat semua user di semua region.
   * Bisa ada flag: berapa banyak user per region.

---

### 4.2. Dashboard Admin Regional (Laptop 2 – contoh: BDG)

**Tujuan:** fokus mengelola operasional untuk **satu region** saja.

**Menu / Modul Utama:**

1. **Overview Region**

   * Total order harian/mingguan untuk region BDG.
   * Total revenue BDG.
   * Grafik penjualan hanya data BDG.
   * Produk terlaris di BDG.

2. **Stok Gudang Region**

   * Data dari `warehouse_items` join `products`.
   * Hanya `warehouses` dengan `region_code = 'BDG'`.
   * Fitur:

     * Update stok (terima barang baru).
     * Tandai stok hampir habis.
   * UI contoh:

     * Product | Warehouse | Stock | Action (Edit stock).

3. **Order Management Region**

   * List orders dari `orders` yang user-nya `region_code = 'BDG'` atau dari DB BDG langsung.
   * Bisa:

     * Update status (processed, shipped, delivered, canceled).
   * Detail:

     * Lihat `order_items` + produk + jumlah.

4. **User Region (Opsional)**

   * List user dengan `region_code = 'BDG'`.
   * Untuk keperluan support, cek histori order user.

5. **Laporan Region**

   * Export PDF/Excel untuk data BDG saja (stok, penjualan, dsb).

> Catatan penting:
> **Admin regional tidak perlu tahu data region lain**, karena di DB nya memang hanya ada data BDG.

---

## 5. Catatan UX Tambahan (User vs Admin)

### 5.1. UX User

* **Pilih region = pilih “dunia” mana yang dia lihat**:

  * Produk sama, tapi:

    * Stok bisa beda.
    * Estimasi pengiriman beda.
    * Ongkir bisa beda (kalau kamu tambahkan nanti).

* Di UI user, kamu bisa kasih label:

  * “Anda belanja sebagai pelanggan **Bandung (BDG)**”
  * Ada tombol:

    * “Ganti lokasi” → kalau dia pindah kota.

---

### 5.2. UX Admin

* **Admin Pusat:**

  * Dashboard lebih kompleks, lintas region.
  * Banyak filter: by region, by warehouse.

* **Admin Regional:**

  * Dashboard lebih simple, fokus 1 region.
  * Tidak ada filter region, karena data sudah otomatis hanya region sendiri.

---

## 6. Ringkasan Konsep dalam 1 Kalimat

> **Data produk & kategori → direplikasi ke semua server.
> Data stok, user, cart, order → dipecah per region dan hanya muncul di node server (laptop) yang menangani region tersebut.
> UI user menyesuaikan region yang dipilih, UI admin menyesuaikan level (pusat vs regional).**

Kalau kamu mau lanjut, next step bisa:

* Kita bikin **wireframe kasar** (layout halaman user & admin sesuai konsep distribusi ini), atau
* Bikin **mapping rute Laravel / Next.js**: URL mana yang hit ke DB pusat vs DB region.
