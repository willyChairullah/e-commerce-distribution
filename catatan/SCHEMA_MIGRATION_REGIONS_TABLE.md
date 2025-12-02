# 🔄 MIGRASI SCHEMA: Pemisahan Tabel Regions

**Tanggal:** 3 Desember 2025  
**Versi:** 2.0

---

## 📋 Ringkasan Perubahan

### ❌ Schema LAMA (v1.0)

```sql
-- region_code langsung di tabel users & warehouses
CREATE TABLE users (
    user_id VARCHAR(50) PRIMARY KEY,
    region_code NVARCHAR(10) NOT NULL,  -- <-- Langsung di sini
    ...
);

CREATE TABLE warehouses (
    warehouse_id VARCHAR(50) PRIMARY KEY,
    region_code NVARCHAR(10) NOT NULL,  -- <-- Langsung di sini
    ...
);
```

### ✅ Schema BARU (v2.0)

```sql
-- Tabel regions terpisah (master data)
CREATE TABLE regions (
    region_id INT IDENTITY(1,1) PRIMARY KEY,
    region_code NVARCHAR(10) NOT NULL UNIQUE,  -- 'MDR', 'SBY'
    region_name NVARCHAR(100) NOT NULL,        -- 'Madura', 'Surabaya'
    ...
);

-- users & warehouses pakai FK ke regions
CREATE TABLE users (
    user_id VARCHAR(50) PRIMARY KEY,
    region_id INT NOT NULL,  -- <-- FK ke regions
    FOREIGN KEY (region_id) REFERENCES regions(region_id)
);

CREATE TABLE warehouses (
    warehouse_id VARCHAR(50) PRIMARY KEY,
    region_id INT NOT NULL,  -- <-- FK ke regions
    FOREIGN KEY (region_id) REFERENCES regions(region_id)
);
```

---

## 🎯 Alasan Perubahan

### 1. **Normalisasi Database**

- Menghindari duplikasi data region di banyak tabel
- Region name hanya disimpan 1x di tabel `regions`
- Perubahan nama region cukup edit 1 baris

### 2. **Foreign Key Integrity**

- SQL Server bisa enforce valid region_code
- Tidak bisa insert user/warehouse dengan region_code invalid
- Cascading rules lebih mudah diatur

### 3. **Performa Query**

- JOIN dengan INT (region_id) lebih cepat dari VARCHAR (region_code)
- Index FK otomatis dibuat SQL Server
- Reduce storage overhead

### 4. **Maintenance**

- Tambah region baru cukup INSERT ke `regions`
- Rename region tidak perlu UPDATE semua tabel
- Clear separation of concerns

---

## 📁 File yang Diubah

### 1. **Database Schema**

✅ `01_schema_base.sql`

- Tambah tabel `regions` dengan sample data MDR/SBY
- Ubah kolom `region_code` → `region_id` di `users` & `warehouses`
- Tambah FK constraint

### 2. **Stored Procedures & Logic Objects**

✅ `02_logic_objects.sql`

- **sp_InsertUser**: Lookup `region_id` dari `region_code` parameter
- **sp_InsertWarehouse**: Lookup `region_id` dari `region_code` parameter
- **v_UserOrdersSummary**: JOIN ke `regions` untuk expose `region_code`
- **v_WarehouseStockDetail**: JOIN ke `regions` untuk expose `region_code`

### 3. **PHP Models**

✅ `app/models/User.php`

- `findByEmail()`: JOIN ke `regions`, expose `region_code` & `region_name`
- `findById()`: JOIN ke `regions`
- `getAll()`: JOIN ke `regions`

✅ `app/models/Warehouse.php`

- `getAll()`: JOIN ke `regions`
- `findById()`: JOIN ke `regions`
- `update()`: Lookup `region_id` dari `region_code` POST data
- `getTotalWarehouses()`: JOIN ke `regions` untuk filter
- `getAllByRegion()`: JOIN ke `regions`

✅ `app/models/WarehouseItem.php`

- `getByProduct()`: JOIN ke `regions` via warehouses

✅ `app/models/Order.php`

- `getAll()`: JOIN `users` → `regions`
- `findById()`: JOIN `users` → `regions`
- `getTotalOrders()`: JOIN `users` → `regions`

✅ `app/models/Cart.php`

- `getByUser()`: JOIN `warehouses` → `regions`

✅ `app/models/Product.php`

- `getAllWithRegionStock()`: JOIN `warehouses` → `regions`
- `getByCategoryWithRegionStock()`: JOIN `warehouses` → `regions`

### 4. **Views**

✅ `views/dashboard/warehouse_form.php`

- Ubah input text `region_code` → dropdown dari `AVAILABLE_REGIONS`

✅ `views/auth/register.php`

- Sudah ada dropdown region (unchanged)

### 5. **Controllers**

✅ `app/controllers/AuthController.php`

- Login: `$_SESSION['region_code']` sekarang dari hasil JOIN User model
- Register: Kirim `region_code` ke SP, SP yang lookup `region_id`

✅ `app/controllers/WarehouseController.php`

- Create/Update: Kirim `region_code` ke Model, Model yang lookup `region_id`

---

## 🔄 Alur Kerja Baru

### Insert User (Register)

```
1. Controller: terima region_code dari form (MDR/SBY)
2. Model: panggil sp_InsertUser(@region_code = 'MDR')
3. SP: lookup region_id dari regions WHERE region_code='MDR'
4. SP: INSERT INTO users (region_id = 1, ...)
5. Return: user_id = 'MDR-U-000001'
```

### Insert Warehouse

```
1. Controller: terima region_code dari form dropdown
2. Model: panggil sp_InsertWarehouse(@region_code = 'SBY')
3. SP: lookup region_id dari regions WHERE region_code='SBY'
4. SP: INSERT INTO warehouses (region_id = 2, ...)
5. Return: warehouse_id = 'SBY-W-000001'
```

### Update Warehouse

```
1. Controller: terima region_code baru dari form
2. Model: SELECT region_id FROM regions WHERE region_code = ?
3. Model: UPDATE warehouses SET region_id = ? WHERE warehouse_id = ?
```

### Query dengan Filter Region

```sql
-- DULU (v1.0):
SELECT * FROM warehouses WHERE region_code = 'MDR'

-- SEKARANG (v2.0):
SELECT w.*, r.region_code, r.region_name
FROM warehouses w
LEFT JOIN regions r ON w.region_id = r.region_id
WHERE r.region_code = 'MDR'
```

---

## 🧪 Testing Checklist

### 1. **Re-run Database Schema**

```powershell
sqlcmd -S localhost -d warehouse_3 -E -i "E:\laragon\www\distribution\01_schema_base.sql"
```

**Expected:**

- ✅ Tabel `regions` created dengan 2 rows (MDR, SBY)
- ✅ Users: MDR-U-000001, MDR-U-000002, SBY-U-000001
- ✅ Warehouses: MDR-W-000001, SBY-W-000001

### 2. **Re-install Stored Procedures**

```powershell
sqlcmd -S localhost -d warehouse_3 -E -i "E:\laragon\www\distribution\02_logic_objects.sql"
```

**Expected:**

- ✅ sp_InsertUser & sp_InsertWarehouse updated
- ✅ Views (v_UserOrdersSummary, v_WarehouseStockDetail) updated

### 3. **Test Registration**

- URL: `http://localhost/distribution/public/register`
- Isi form dengan region **Madura**
- **Expected:** User ID = `MDR-U-000003`, region_id = 1

### 4. **Test Login**

- Login dengan user `admin@example.com`
- **Expected:** `$_SESSION['region_code']` = `'MDR'`

### 5. **Test Warehouse Create**

- Dashboard → Warehouse → Tambah Baru
- Pilih region **Surabaya** dari dropdown
- **Expected:** Warehouse ID = `SBY-W-000002`, region_id = 2

### 6. **Test Warehouse List**

- Dashboard → Warehouse → List
- **Expected:** Kolom `region_code` & `region_name` tampil dari JOIN

### 7. **Test Regional Filter**

- Config mode: `REGION_MODE='regional'`, `REGION_CODE='MDR'`
- Dashboard → Warehouse
- **Expected:** Hanya warehouse MDR yang muncul

---

## 🚨 Breaking Changes

### ⚠️ Direct SQL Query di Luar Models

Jika ada query manual yang langsung akses `region_code` di `users`/`warehouses`, harus diubah:

```sql
-- ❌ BROKEN:
SELECT * FROM users WHERE region_code = 'MDR'

-- ✅ FIXED:
SELECT u.*, r.region_code
FROM users u
LEFT JOIN regions r ON u.region_id = r.region_id
WHERE r.region_code = 'MDR'
```

### ⚠️ Session Data

`$_SESSION['region_code']` tetap tersedia karena JOIN di `User::findByEmail()`

### ⚠️ Form Input

- Warehouse form: input text → dropdown
- Tetap kirim `region_code` (bukan `region_id`) ke backend

---

## 📊 Data Migration (Jika Ada Data Lama)

Jika sudah ada data production dengan schema lama:

```sql
-- 1. Backup dulu!
-- 2. Buat tabel regions
-- 3. Populate dari existing region_code
INSERT INTO regions (region_code, region_name)
SELECT DISTINCT region_code, region_code FROM users;

-- 4. Tambah kolom region_id ke users
ALTER TABLE users ADD region_id INT;

-- 5. Update region_id dari lookup
UPDATE u
SET u.region_id = r.region_id
FROM users u
JOIN regions r ON u.region_code = r.region_code;

-- 6. Drop kolom region_code lama
ALTER TABLE users DROP COLUMN region_code;

-- 7. Repeat untuk warehouses
```

---

## 📝 Catatan Penting

1. **Stored Procedures tetap terima `region_code` string** (bukan `region_id`) untuk backward compatibility dengan controller
2. **Views expose `region_code` & `region_name`** untuk UI display
3. **Session tetap simpan `region_code`** untuk regional filtering
4. **Helper functions** di `util.php` tidak perlu diubah (masih filter by `region_code` via JOIN)

---

## ✅ Status

- [x] Schema updated (01_schema_base.sql)
- [x] Stored procedures updated (02_logic_objects.sql)
- [x] All models updated (6 models)
- [x] Views updated (warehouse_form.php)
- [x] Documentation created
- [ ] **Testing pending** (perlu re-run SQL files)
- [ ] **Production migration** (jika ada data lama)

---

**Next Steps:**

1. Re-run `01_schema_base.sql` untuk recreate database dengan schema baru
2. Re-run `02_logic_objects.sql` untuk update stored procedures
3. Test registration, login, warehouse CRUD
4. Verify regional filtering masih berfungsi
