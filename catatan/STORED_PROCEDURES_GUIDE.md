# 📚 Panduan Implementasi Stored Procedures

## Ringkasan

Aplikasi ini sekarang menggunakan **stored procedures, functions, views, dan triggers** dari file `02_logic_objects.sql` untuk meningkatkan performa dan konsistensi database.

---

## ✅ Yang Sudah Diimplementasikan

### 1. **Stored Procedures untuk INSERT Operations**

Semua operasi INSERT sekarang menggunakan stored procedures dengan **OUTPUT parameter** untuk mendapatkan distributed ID:

#### 📌 User Registration

```php
// File: app/models/User.php
User::create($data)
// Memanggil: sp_InsertUser
// Output: user_id (format: BDG-U-000001)
```

#### 📌 Warehouse Creation

```php
// File: app/models/Warehouse.php
Warehouse::create($data)
// Memanggil: sp_InsertWarehouse
// Output: warehouse_id (format: BDG-W-000001)
```

#### 📌 Warehouse Item (Stock) Creation

```php
// File: app/models/WarehouseItem.php
WarehouseItem::create($data)
// Memanggil: sp_InsertWarehouseItem
// Output: warehouse_item_id (format: BDG-WI-000001)
```

#### 📌 Cart Item Addition

```php
// File: app/models/Cart.php
Cart::add($data)
// Memanggil: sp_InsertCartItem
// Output: cart_item_id (format: BDG-CI-000001)
```

#### 📌 Order Creation

```php
// File: app/models/Order.php
Order::create($userId, $totalAmount)
// Memanggil: sp_InsertOrder
// Output: order_id (format: BDG-O-000001)
```

#### 📌 Order Item Creation

```php
// File: app/models/Order.php
Order::addOrderItem($orderId, $warehouseItemId, $qty, $price)
// Memanggil: sp_InsertOrderItem
// Output: order_item_id (format: BDG-OI-000001)
```

---

### 2. **Stored Procedure dengan CURSOR untuk Checkout** 🛒

Method baru untuk checkout menggunakan **cursor-based stored procedure**:

```php
// File: app/models/Order.php
$orderId = Order::checkoutFromCart($userId);
// Memanggil: sp_CheckoutFromCart_WithCursor

// SP ini melakukan:
// 1. Hitung total dari cart
// 2. Buat order dengan sp_InsertOrder
// 3. Iterasi cart items dengan CURSOR
// 4. Insert order_items dengan sp_InsertOrderItem (trigger kurangi stok otomatis)
// 5. Hapus cart user
// 6. Return order_id yang baru dibuat
```

**Implementasi di Controller:**

```php
// File: app/controllers/ClientController.php::checkout()
// SEBELUM: Manual loop + reduceStock() calls
// SEKARANG: Single call ke checkoutFromCart()

$orderId = $this->orderModel->checkoutFromCart($userId);
```

**Keuntungan:**

- ✅ Transactional (semua atau tidak sama sekali)
- ✅ Stock reduction otomatis via trigger
- ✅ Error handling built-in
- ✅ Lebih efisien (1 round-trip ke DB)

---

### 3. **Database Functions**

#### 📌 GetNextSequentialID

Function untuk generate ID dengan sequence:

```sql
GetNextSequentialID('BDG-U-', 'users')
-- Returns: BDG-U-000001, BDG-U-000002, dst.
```

Digunakan oleh semua stored procedures untuk generate distributed ID.

#### 📌 fn_GetRegionFromUserId

Extract region dari user_id:

```sql
fn_GetRegionFromUserId('JKT-U-000001')
-- Returns: JKT
```

---

### 4. **Views**

#### 📌 v_UserOrdersSummary

Menampilkan summary orders per user:

```sql
SELECT * FROM v_UserOrdersSummary;
```

Output:

- user_id
- full_name
- email
- region_code
- total_orders
- total_spent

#### 📌 v_WarehouseStockDetail

Menampilkan detail stok per warehouse dengan join products:

```sql
SELECT * FROM v_WarehouseStockDetail WHERE region_code = 'BDG';
```

Output:

- warehouse_id, warehouse_name, region_code
- warehouse_item_id
- product_id, product_name, price
- stock
- added_at

#### 📌 v_CartDetails

Menampilkan cart dengan full details:

```sql
SELECT * FROM v_CartDetails WHERE user_id = 'BDG-U-000001';
```

Output:

- cart_item_id, user_id, full_name
- warehouse_item_id, warehouse_id, warehouse_name
- product_id, product_name, price
- qty, line_total
- created_at

---

### 5. **Trigger - Automatic Stock Reduction**

#### 📌 trg_OrderItems_AfterInsert_UpdateStock

Trigger otomatis ketika `order_items` di-INSERT:

```sql
-- Automatic execution pada INSERT order_items
-- 1. Cek stok cukup (RAISERROR jika tidak)
-- 2. Kurangi stock di warehouse_items
```

**Impact:** Method `WarehouseItem::reduceStock()` **TIDAK PERLU dipanggil manual** lagi di checkout flow.

---

## 🔧 Cara Menginstall Logic Objects

### 1. Jalankan SQL Script

```powershell
sqlcmd -S localhost -d warehouse_3 -E -i "E:\laragon\www\distribution\02_logic_objects.sql"
```

### 2. Verifikasi Installation

```sql
-- Check stored procedures
SELECT name FROM sys.procedures WHERE name LIKE 'sp_%';

-- Check functions
SELECT name FROM sys.objects WHERE type = 'FN';

-- Check views
SELECT name FROM sys.views;

-- Check triggers
SELECT name FROM sys.triggers;
```

---

## 🧪 Testing Guide

### Test 1: User Registration

1. Register user baru
2. Check database: `SELECT * FROM users WHERE email = 'test@example.com'`
3. Verify: user_id format `{REGION}-U-{SEQUENCE}`

### Test 2: Warehouse & Stock Creation

1. Dashboard → Warehouse → Add New
2. Dashboard → Warehouse Items → Add Stock
3. Verify: IDs menggunakan format distributed

### Test 3: Checkout Flow dengan Cursor SP

1. Login sebagai user biasa
2. Tambah item ke cart
3. Checkout
4. Verify:
   - Order created dengan distributed ID
   - Order items created
   - Stock berkurang otomatis (via trigger)
   - Cart ter-clear

### Test 4: Views

```sql
-- Test user summary
SELECT * FROM v_UserOrdersSummary;

-- Test warehouse stock detail
SELECT * FROM v_WarehouseStockDetail WHERE region_code = 'BDG';

-- Test cart details
SELECT * FROM v_CartDetails WHERE user_id = 'BDG-U-000001';
```

---

## 📊 Performance Benefits

| Operation              | Before (PHP Logic)          | After (Stored Procedures)         |
| ---------------------- | --------------------------- | --------------------------------- |
| **Checkout**           | 20+ DB calls                | 1 SP call (dengan cursor)         |
| **ID Generation**      | PHP random (risk collision) | DB sequential (guaranteed unique) |
| **Stock Reduction**    | Manual PHP call             | Automatic via trigger             |
| **Transaction Safety** | Manual BEGIN/COMMIT         | Built-in SP transaction           |
| **Error Handling**     | Try-catch di PHP            | RAISERROR + ROLLBACK di SP        |

---

## ⚠️ Important Notes

### OUTPUT Parameter Syntax

```php
// Correct syntax untuk SQL Server OUTPUT parameters:
$newId = '';
$params = array(
    array($inputValue, SQLSRV_PARAM_IN),
    array(&$newId, SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_STRING(SQLSRV_ENC_CHAR), SQLSRV_SQLTYPE_VARCHAR(50))
);

$stmt = sqlsrv_query($conn, "{CALL sp_Name(?, ?)}", $params);
sqlsrv_free_stmt($stmt); // Important!

// $newId sekarang berisi generated ID
```

### Trigger Behavior

- Trigger `trg_OrderItems_AfterInsert_UpdateStock` akan **ROLLBACK** transaction jika stok tidak cukup
- Error message: "Stok tidak cukup untuk salah satu item pesanan."
- Pastikan error handling di PHP menangkap ini

### Sequential ID vs Random ID

- **Sequential ID (via SP):** Konsisten, no collision, lebih mudah tracking
- **Random ID (PHP):** Risk collision, perlu unique constraint + retry logic

---

## 🚀 Next Steps

### Potential Enhancements

1. **Add More SPs:**

   - `sp_UpdateWarehouseStock` (bulk stock update)
   - `sp_CancelOrder` (return stock, update status)
   - `sp_GetLowStockItems` (alert stok menipis)

2. **Optimize Views:**

   - Add indexed views untuk report besar
   - Create materialized views untuk dashboard

3. **Add More Triggers:**

   - `trg_Orders_AfterUpdate_LogStatus` (audit trail)
   - `trg_WarehouseItems_BeforeDelete_CheckOrders` (prevent delete if used)

4. **Monitoring:**
   - Log SP execution time
   - Monitor trigger performance
   - Track cursor efficiency

---

## 📝 Summary

✅ **6 Models** updated to use stored procedures
✅ **1 Controller** (ClientController) simplified dengan SP checkout
✅ **3 Functions** created (GetNextSequentialID, fn_GetRegionFromUserId)
✅ **6 Stored Procedures** implemented (sp_InsertUser, sp_InsertWarehouse, sp_InsertWarehouseItem, sp_InsertCartItem, sp_InsertOrder, sp_InsertOrderItem)
✅ **1 Complex SP** dengan cursor (sp_CheckoutFromCart_WithCursor)
✅ **3 Views** created (v_UserOrdersSummary, v_WarehouseStockDetail, v_CartDetails)
✅ **1 Trigger** implemented (auto stock reduction)

**Result:** Aplikasi lebih efisien, konsisten, dan transaction-safe! 🎉
