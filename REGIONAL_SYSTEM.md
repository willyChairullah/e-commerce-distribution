# 🚀 SISTEM DISTRIBUSI REGIONAL - PANDUAN IMPLEMENTASI

## 📋 MODIFIKASI YANG TELAH DILAKUKAN

### ✅ **1. Konfigurasi Regional Mode**

File baru: `config/app.php`

**Konstanta penting:**

- `REGION_MODE`: Menentukan mode deployment ('central' atau 'regional')
- `REGION_CODE`: Kode region untuk mode regional (JKT, BDG, SBY)
- `AVAILABLE_REGIONS`: Daftar region yang didukung

### ✅ **2. Helper Functions Baru**

File: `helpers/util.php`

**Fungsi baru yang ditambahkan:**

- `isCentralMode()` - Cek apakah DB pusat atau regional
- `getCurrentRegion()` - Ambil region code dari config
- `getRegionName()` - Ambil nama region yang user-friendly
- `canAccessRegion()` - Validasi akses region
- `getRegionFilter()` - Generate SQL filter untuk query
- `isSuperAdmin()` - Cek admin pusat
- `isRegionalAdmin()` - Cek admin regional

### ✅ **3. Auto-Set Region pada Registrasi**

**Modifikasi:**

- `AuthController::register()` - Region otomatis diset dari config
- `views/auth/register.php` - Input region manual dihapus, ditampilkan info saja

**Behavior:**

- User di Laptop 1 (central): region_code = null atau sesuai pilihan
- User di Laptop 2 (regional BDG): region_code = 'BDG' (otomatis)

### ✅ **4. Filter Produk Berdasarkan Region**

**Modifikasi Model:**

- `Product::getAllWithRegionStock()` - Produk dengan stok region
- `Product::getByCategoryWithRegionStock()` - Filter kategori + region
- `WarehouseItem::getByProduct()` - Tambah parameter region

**Modifikasi Controller:**

- `ClientController` - Semua method filter by region otomatis

**Behavior:**

- Central mode: tampilkan semua produk
- Regional mode: hanya produk dengan stok di region tersebut

### ✅ **5. UI User - Region Indicator**

**Modifikasi:**

- `views/client/layout.php` - Tampilkan badge region (bukan selector)
- `views/client/index.php` - Badge ketersediaan stok per region
- `views/client/detil_produk.php` - Info warehouse region

**Tampilan:**

```
📍 Region: Bandung (BDG)
✅ Tersedia di Bandung (Stok: 30)
```

### ✅ **6. Dashboard Admin - Central vs Regional**

**Modifikasi:**

- `DashboardController` - Logic berbeda untuk central/regional
- `views/dashboard/index.php` - Tampilan sesuai mode
- `views/dashboard/layout.php` - Menu berbeda per mode

**Dashboard Admin Pusat (Central):**

- Lihat semua region
- Statistik per region
- Menu: "Semua Region"

**Dashboard Admin Regional:**

- Hanya lihat data regionnya
- Filter otomatis by region
- Menu: "Region BDG"

### ✅ **7. Auto-Filter Data Admin**

**Controller yang dimodifikasi:**

- `OrderController` - Filter order by region
- `WarehouseController` - Filter warehouse by region
- `DashboardController` - Statistik by region

**Model yang dimodifikasi:**

- `Order::getAll()` - Tambah parameter region
- `Order::getTotalOrders()` - Filter by region
- `Warehouse::getAllByRegion()` - Filter by region
- `Warehouse::getTotalWarehouses()` - Filter by region

---

## 🔧 CARA SETUP

### **LAPTOP 1 - Database Pusat (Central Mode)**

Edit `config/app.php`:

```php
define('REGION_MODE', 'central');  // Mode pusat
define('REGION_CODE', null);       // Tidak perlu region spesifik
```

**Karakteristik:**

- Database berisi semua data (JKT, BDG, SBY)
- Admin bisa lihat semua region
- User bisa dari region manapun
- Dashboard menampilkan statistik per region

### **LAPTOP 2 - Database Regional Bandung (Regional Mode)**

Edit `config/app.php`:

```php
define('REGION_MODE', 'regional'); // Mode regional
define('REGION_CODE', 'BDG');      // Khusus Bandung
```

**Karakteristik:**

- Database hanya berisi data BDG
- Admin hanya lihat data BDG
- User register otomatis region BDG
- Produk difilter hanya yang ada stok di BDG

---

## 📊 FLOW SISTEM

### **User di Laptop 2 (Regional BDG):**

1. **Buka Website**

   - Header otomatis menampilkan: `📍 Region: Bandung (BDG)`
   - Tidak ada pilihan ganti region

2. **Register**

   - Isi: Nama, Email, Password
   - Region otomatis = BDG (dari config)
   - Tidak ada input manual region

3. **Lihat Produk**

   - Hanya produk dengan stok di warehouse BDG
   - Badge: `✅ Tersedia di Bandung (Stok: 30)`
   - Produk tanpa stok BDG: tidak tampil

4. **Keranjang & Checkout**
   - Warehouse otomatis dari BDG
   - Info: "Dikirim dari: Gudang Bandung"

### **Admin di Laptop 1 (Central):**

1. **Login Dashboard**

   - Menu: "Dashboard Global"
   - Lihat statistik per region

2. **Kelola Data**
   - Bisa filter by region
   - Akses ke semua warehouse (JKT, BDG, SBY)
   - Lihat order dari semua region

### **Admin di Laptop 2 (Regional BDG):**

1. **Login Dashboard**

   - Menu: "Dashboard Regional - Bandung"
   - Hanya data BDG

2. **Kelola Data**
   - Tidak ada filter region (otomatis BDG)
   - Hanya warehouse BDG
   - Hanya order BDG

---

## 🎯 TESTING

### **Test Laptop 2 (Regional BDG):**

1. **Test Config:**

   - Pastikan `REGION_MODE = 'regional'`
   - Pastikan `REGION_CODE = 'BDG'`

2. **Test Registrasi:**

   - Register user baru
   - Cek database: `region_code` harus 'BDG'

3. **Test Produk:**

   - Lihat katalog
   - Hanya produk dengan stok BDG yang muncul

4. **Test Dashboard:**
   - Login sebagai admin
   - Dashboard hanya tampilkan data BDG

### **Test Laptop 1 (Central):**

1. **Test Config:**

   - Pastikan `REGION_MODE = 'central'`

2. **Test Dashboard:**
   - Login sebagai admin
   - Dashboard tampilkan semua region
   - Ada tabel statistik per region

---

## 🔄 MIGRATION DATA

### **Untuk Database yang Sudah Ada:**

Jika ada user dengan region_code kosong, update:

```sql
-- Set region default untuk user lama
UPDATE users
SET region_code = 'JKT'
WHERE region_code IS NULL OR region_code = '';

-- Set region untuk warehouse yang belum punya
UPDATE warehouses
SET region_code = 'JKT'
WHERE region_code IS NULL OR region_code = '';
```

---

## 📝 CATATAN PENTING

### **JANGAN:**

❌ Ubah REGION_MODE & REGION_CODE setelah deploy
❌ User pilih region sendiri (otomatis dari config)
❌ Admin regional akses data region lain

### **HARUS:**

✅ Set config sesuai deployment (central/regional)
✅ Pastikan warehouse punya region_code
✅ Test setiap mode sebelum deploy
✅ Backup database sebelum migration

---

## 🚀 NEXT STEPS (Optional - Fase 2)

Untuk implementasi penuh distributed system:

1. **Multi-Database Connection**

   - Setup koneksi ke multiple database
   - Laptop 1 bisa query ke DB BDG/JKT/SBY

2. **Data Replication**

   - Auto-sync products & categories
   - Dari central ke regional

3. **API Sync**
   - Webhook untuk sinkronisasi real-time
   - Central pull data dari regional

---

## 📞 TROUBLESHOOTING

### **Error: Region tidak terdeteksi**

- Cek `config/app.php` sudah di-include
- Pastikan konstanta REGION_CODE terisi

### **Produk tidak muncul di regional**

- Cek warehouse punya `region_code`
- Cek warehouse_items punya stok > 0

### **Dashboard kosong**

- Cek data warehouse & order punya region_code
- Pastikan user login punya region_code

---

## ✅ SUMMARY

Sistem sekarang sudah support:

- ✅ Mode Central vs Regional
- ✅ Auto-set region saat register
- ✅ Filter produk by region
- ✅ Region indicator di UI
- ✅ Dashboard berbeda per mode
- ✅ Auto-filter data admin by region

**Siap deploy ke 2 laptop dengan konfigurasi berbeda!** 🎉
