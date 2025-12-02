# 🚀 QUICK START - Setup Regional System

## 📦 PERSIAPAN

### 1. Backup Database

```sql
-- Backup database sebelum modifikasi
BACKUP DATABASE warehouse_db TO DISK = 'E:\backup\warehouse_db_before_regional.bak'
```

### 2. Jalankan Migration SQL

```sql
-- Buka SQL Server Management Studio (SSMS)
-- Jalankan file: database_migration_regional.sql
```

---

## 🖥️ SETUP LAPTOP 1 (DATABASE PUSAT)

### Step 1: Copy Config File

```bash
cd e:\laragon\www\distribution
copy config\app.example.central.php config\app.php
```

### Step 2: Verifikasi Config

Buka `config/app.php`, pastikan:

```php
define('REGION_MODE', 'central');
define('REGION_CODE', null);
```

### Step 3: Test

1. Akses: `http://localhost/distribution/public/`
2. Register user baru
3. Login sebagai admin
4. Dashboard harus tampilkan: **"Dashboard Admin Pusat - Monitoring Semua Region"**
5. Harus ada tabel "Statistik Per Region"

---

## 💻 SETUP LAPTOP 2 (DATABASE REGIONAL BANDUNG)

### Step 1: Copy Project

```bash
# Copy seluruh folder distribution ke Laptop 2
# Atau git pull dari repository
```

### Step 2: Copy Config File

```bash
cd e:\laragon\www\distribution
copy config\app.example.regional-bdg.php config\app.php
```

### Step 3: Verifikasi Config

Buka `config/app.php`, pastikan:

```php
define('REGION_MODE', 'regional');
define('REGION_CODE', 'BDG');
```

### Step 4: Setup Database

**PENTING:** Database di Laptop 2 hanya berisi data BDG!

**Opsi A - Filter Data Manual:**

```sql
-- Di Laptop 2, hapus data region lain
DELETE FROM warehouse_items
WHERE warehouse_id IN (
    SELECT warehouse_id FROM warehouses WHERE region_code != 'BDG'
);

DELETE FROM warehouses WHERE region_code != 'BDG';
DELETE FROM users WHERE region_code != 'BDG';
DELETE FROM cart_items WHERE user_id NOT IN (SELECT user_id FROM users);
```

**Opsi B - Export/Import Selektif:**

```sql
-- Di Laptop 1, export hanya data BDG
-- Kemudian import ke Laptop 2
```

### Step 5: Test

1. Akses: `http://localhost/distribution/public/`
2. Header harus tampilkan: **"📍 Region: Bandung (BDG)"**
3. Register user baru (tidak ada input region, otomatis BDG)
4. Cek database: `SELECT region_code FROM users` → harus 'BDG'
5. Login sebagai admin
6. Dashboard harus tampilkan: **"Dashboard Admin Regional - Bandung"**
7. Tidak ada pilihan region lain di menu

---

## ✅ CHECKLIST TESTING

### User Flow (Laptop 2 - Regional BDG):

- [ ] Header menampilkan "📍 Region: Bandung (BDG)"
- [ ] Register tanpa input region, otomatis BDG
- [ ] Hanya produk dengan stok BDG yang tampil
- [ ] Badge produk: "✅ Tersedia di Bandung (Stok: XX)"
- [ ] Keranjang menampilkan "Dikirim dari: Gudang Bandung"

### Admin Flow (Laptop 2 - Regional BDG):

- [ ] Dashboard: "Dashboard Admin Regional - Bandung"
- [ ] Menu tidak ada "Semua Region"
- [ ] Warehouse hanya tampilkan warehouse BDG
- [ ] Order hanya tampilkan order dari user BDG
- [ ] Tidak bisa lihat data region lain

### Admin Flow (Laptop 1 - Central):

- [ ] Dashboard: "Dashboard Admin Pusat"
- [ ] Ada tabel "Statistik Per Region"
- [ ] Menu ada label "(Semua Region)"
- [ ] Bisa lihat warehouse semua region
- [ ] Bisa lihat order semua region

---

## 🔧 TROUBLESHOOTING

### Error: "Undefined constant REGION_MODE"

**Solusi:**

- Pastikan `config/app.php` sudah ada
- Cek `public/index.php` sudah include `config/app.php`

### Produk tidak muncul di regional

**Solusi:**

```sql
-- Cek warehouse items di region BDG
SELECT wi.*, w.warehouse_name, w.region_code, p.name
FROM warehouse_items wi
LEFT JOIN warehouses w ON wi.warehouse_id = w.warehouse_id
LEFT JOIN products p ON wi.product_id = p.product_id
WHERE w.region_code = 'BDG' AND wi.stock > 0;

-- Jika kosong, tambahkan stok:
INSERT INTO warehouse_items (warehouse_id, product_id, stock, created_at)
VALUES (1, 1, 100, GETDATE());
```

### Dashboard kosong

**Solusi:**

```sql
-- Cek data users punya region_code
SELECT user_id, full_name, region_code FROM users;

-- Update jika NULL:
UPDATE users SET region_code = 'BDG' WHERE region_code IS NULL;
```

### User tidak bisa register

**Solusi:**

- Cek `config/app.php`: REGION_CODE harus terisi untuk regional mode
- Cek log error di browser console atau PHP error log

---

## 📊 MONITORING

### Query untuk Monitoring Data:

```sql
-- Jumlah users per region
SELECT region_code, COUNT(*) as total
FROM users
GROUP BY region_code;

-- Jumlah warehouse per region
SELECT region_code, COUNT(*) as total
FROM warehouses
GROUP BY region_code;

-- Jumlah produk dengan stok per region
SELECT w.region_code, COUNT(DISTINCT wi.product_id) as products_with_stock
FROM warehouse_items wi
JOIN warehouses w ON wi.warehouse_id = w.warehouse_id
WHERE wi.stock > 0
GROUP BY w.region_code;

-- Total stok per region
SELECT w.region_code, SUM(wi.stock) as total_stock
FROM warehouse_items wi
JOIN warehouses w ON wi.warehouse_id = w.warehouse_id
GROUP BY w.region_code;

-- Orders per region
SELECT u.region_code, COUNT(o.order_id) as total_orders, SUM(o.total_amount) as revenue
FROM orders o
JOIN users u ON o.user_id = u.user_id
GROUP BY u.region_code;
```

---

## 🎯 NEXT: Deploy ke Production

1. **Laptop 1 (Pusat):**

   - Setup di server yang powerful
   - Backup rutin
   - Monitor semua region

2. **Laptop 2+ (Regional):**
   - Bisa gunakan hardware lebih ringan
   - Fokus ke satu region saja
   - Sync data products & categories dari pusat (manual atau automated)

---

## 📞 Support

Jika ada masalah, cek file:

- `REGIONAL_SYSTEM.md` - Dokumentasi lengkap
- `database_migration_regional.sql` - SQL migration
- `config/app.example.*.php` - Contoh config

**Semua sudah siap! Tinggal setup config sesuai deployment.** 🎉
