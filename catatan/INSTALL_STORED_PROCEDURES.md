# 🚀 Instalasi Stored Procedures

## Langkah Cepat

### 1️⃣ Install Logic Objects (Functions, SPs, Views, Triggers)

```powershell
sqlcmd -S localhost -d warehouse_3 -E -i "E:\laragon\www\distribution\02_logic_objects.sql"
```

**Output yang diharapkan:**

```
Dropping old objects...
Creating functions...
Creating stored procedures...
Creating views...
Creating trigger...
Commands completed successfully.
```

---

### 2️⃣ Verifikasi Instalasi

```sql
-- Check stored procedures (harus ada 11)
SELECT name FROM sys.procedures WHERE name LIKE 'sp_%';
```

Expected output:

- sp_CheckoutFromCart_WithCursor
- sp_GetCartByUser
- sp_GetOrderDetail
- sp_GetUserOrders
- sp_InsertCartItem
- sp_InsertOrder
- sp_InsertOrderItem
- sp_InsertUser
- sp_InsertWarehouse
- sp_InsertWarehouseItem

```sql
-- Check functions (harus ada 2)
SELECT name FROM sys.objects WHERE type = 'FN';
```

Expected output:

- fn_GetRegionFromUserId
- GetNextSequentialID

```sql
-- Check views (harus ada 3)
SELECT name FROM sys.views WHERE name LIKE 'v_%';
```

Expected output:

- v_CartDetails
- v_UserOrdersSummary
- v_WarehouseStockDetail

```sql
-- Check trigger (harus ada 1)
SELECT name FROM sys.triggers;
```

Expected output:

- trg_OrderItems_AfterInsert_UpdateStock

---

### 3️⃣ Test Fungsi Dasar

#### Test Function GetNextSequentialID

```sql
SELECT dbo.GetNextSequentialID('BDG-U-', 'users');
-- Output: BDG-U-000001 (atau sequence berikutnya)
```

#### Test View

```sql
SELECT * FROM v_WarehouseStockDetail;
-- Harus menampilkan data warehouse + stock + products
```

#### Test Stored Procedure

```sql
-- Test insert user
DECLARE @new_user_id VARCHAR(50);
EXEC sp_InsertUser
    @full_name = 'Test User',
    @email = 'test123@example.com',
    @password = 'hashed_password_here',
    @region_code = 'BDG',
    @is_admin = 0,
    @new_user_id = @new_user_id OUTPUT;

SELECT @new_user_id as GeneratedUserId;
-- Output: BDG-U-XXXXXX

-- Verify
SELECT * FROM users WHERE user_id = @new_user_id;
```

---

### 4️⃣ Test Aplikasi

1. **Register User Baru:**

   - http://localhost/distribution/public/register
   - Check database: ID harus format `{REGION}-U-{SEQUENCE}`

2. **Login sebagai Admin:**

   - Dashboard → Warehouse → Tambah warehouse baru
   - Dashboard → Warehouse Items → Tambah stock
   - Verify IDs menggunakan stored procedures

3. **Test Checkout Flow:**
   - Login sebagai user biasa
   - Tambah produk ke cart
   - Checkout
   - Verify:
     - ✅ Order created
     - ✅ Stock berkurang otomatis
     - ✅ Cart ter-clear

---

## 🐛 Troubleshooting

### Error: "Could not find stored procedure 'sp_InsertUser'"

**Solusi:** Jalankan ulang script instalasi:

```powershell
sqlcmd -S localhost -d warehouse_3 -E -i "E:\laragon\www\distribution\02_logic_objects.sql"
```

### Error: "Stok tidak cukup untuk salah satu item pesanan"

**Penyebab:** Trigger `trg_OrderItems_AfterInsert_UpdateStock` detect stock tidak cukup

**Solusi:**

- Tambah stock di warehouse_items
- Atau kurangi qty di cart

### Error: OUTPUT parameter tidak return value

**Penyebab:** Lupa `sqlsrv_free_stmt($stmt);`

**Solusi:** Pastikan code menggunakan pattern ini:

```php
$stmt = sqlsrv_query($conn, $sql, $params);
if ($stmt === false) {
    error_log(sqlsrv_errors());
    return false;
}
sqlsrv_free_stmt($stmt); // PENTING!
return $outputVariable;
```

---

## ✅ Checklist Instalasi

- [ ] Jalankan `02_logic_objects.sql`
- [ ] Verify 11 stored procedures created
- [ ] Verify 2 functions created
- [ ] Verify 3 views created
- [ ] Verify 1 trigger created
- [ ] Test registration (user ID format benar)
- [ ] Test add warehouse (warehouse ID format benar)
- [ ] Test checkout (stock berkurang otomatis)
- [ ] Test views (data muncul dengan join)

---

## 📚 Dokumentasi Lengkap

Lihat: `STORED_PROCEDURES_GUIDE.md` untuk detail implementasi, benefits, dan contoh penggunaan.

---

**Status:** ✅ Ready to use!
**Version:** 1.0 (December 2024)
