# Distributed ID System - Documentation

## 📋 Ringkasan

Sistem ini mengubah primary key dari **INT IDENTITY** (auto-increment) menjadi **VARCHAR dengan region prefix** untuk mencegah ID collision saat data dari beberapa database regional di-merge ke database central.

---

## 🎯 Masalah yang Diselesaikan

### Sebelum (INT IDENTITY):

```
Laptop 2 (BDG): user_id = 1, 2, 3, ...
Laptop 3 (JKT): user_id = 1, 2, 3, ...  ❌ BENTROK!
Laptop 1 (Central): Tidak bisa merge karena ID sama
```

### Setelah (VARCHAR dengan Region Prefix):

```
Laptop 2 (BDG): user_id = "BDG-U-000001", "BDG-U-000002", ...
Laptop 3 (JKT): user_id = "JKT-U-000001", "JKT-U-000002", ...  ✅ AMAN!
Laptop 1 (Central): Bisa merge semua data tanpa bentrok
```

---

## 📊 Format ID

| Tabel             | Format ID           | Contoh          | Keterangan          |
| ----------------- | ------------------- | --------------- | ------------------- |
| `users`           | `{REGION}-U-{SEQ}`  | `BDG-U-000001`  | U = User            |
| `warehouses`      | `{REGION}-W-{SEQ}`  | `JKT-W-000001`  | W = Warehouse       |
| `warehouse_items` | `{REGION}-WI-{SEQ}` | `SBY-WI-000001` | WI = Warehouse Item |
| `cart_items`      | `{REGION}-CI-{SEQ}` | `BDG-CI-000001` | CI = Cart Item      |
| `orders`          | `{REGION}-O-{SEQ}`  | `JKT-O-000001`  | O = Order           |
| `order_items`     | `{REGION}-OI-{SEQ}` | `BDG-OI-000001` | OI = Order Item     |

**Catatan:** Tabel `categories` dan `products` tetap menggunakan INT karena data global yang direplikasi (bukan regional).

---

## 🔧 Cara Migrasi Database

### Step 1: Backup Database

```sql
-- Jalankan di SQL Server Management Studio atau Azure Data Studio
sqlcmd -S localhost -d warehouse_db -E -i database_distributed_id.sql
```

**PENTING:** Script akan otomatis:

1. Backup semua tabel ke `*_backup` (misal: `users_backup`)
2. Drop foreign keys lama
3. Drop tabel lama
4. Buat tabel baru dengan VARCHAR ID
5. Buat stored procedures untuk insert data
6. Insert sample data

### Step 2: Verifikasi

```sql
-- Cek data setelah migrasi
SELECT user_id, full_name, region_code FROM users;
SELECT warehouse_id, warehouse_name, region_code FROM warehouses;
```

Output yang diharapkan:

```
user_id         full_name       region_code
-------------------------------------------------
JKT-U-000001    Admin Pusat     JKT
BDG-U-000001    John Doe        BDG
SBY-U-000001    Jane Smith      SBY
```

---

## 💻 Cara Update PHP Code

### 1. Helper Functions (Sudah Dibuat)

File: `helpers/util.php`

```php
// Generate ID
$userId = generateUserId('BDG');           // "BDG-U-000001"
$warehouseId = generateWarehouseId('JKT'); // "JKT-W-000001"
$orderId = generateOrderId();              // Auto ambil region dari getCurrentRegion()

// Validasi ID
if (isValidDistributedId($userId, 'U')) {
    // ID valid dan tipe U (User)
}

// Extract region dari ID
$region = extractRegionFromId('BDG-U-000001'); // "BDG"
```

### 2. Update Model - User.php

**SEBELUM:**

```php
public static function create($data)
{
    global $conn;
    $sql = "INSERT INTO users (full_name, email, password, region_code, is_admin)
            VALUES (?, ?, ?, ?, ?)";
    $params = [$data['full_name'], $data['email'], $data['password'],
               $data['region_code'], $data['is_admin'] ?? 0];
    $stmt = sqlsrv_query($conn, $sql, $params);
    return $stmt;
}
```

**SETELAH:**

```php
public static function create($data)
{
    global $conn;

    // Generate distributed ID
    $userId = generateUserId($data['region_code']);

    $sql = "INSERT INTO users (user_id, full_name, email, password, region_code, is_admin)
            VALUES (?, ?, ?, ?, ?, ?)";
    $params = [$userId, $data['full_name'], $data['email'], $data['password'],
               $data['region_code'], $data['is_admin'] ?? 0];
    $stmt = sqlsrv_query($conn, $sql, $params);

    return $userId; // Return generated ID
}
```

### 3. Update Model - Warehouse.php

**SEBELUM:**

```php
public static function create($data)
{
    global $conn;
    $sql = "INSERT INTO warehouses (warehouse_name, region_code, address)
            VALUES (?, ?, ?)";
    $params = [$data['warehouse_name'], $data['region_code'], $data['address']];
    $stmt = sqlsrv_query($conn, $sql, $params);
    return $stmt;
}
```

**SETELAH:**

```php
public static function create($data)
{
    global $conn;

    // Generate distributed ID
    $warehouseId = generateWarehouseId($data['region_code']);

    $sql = "INSERT INTO warehouses (warehouse_id, warehouse_name, region_code, address)
            VALUES (?, ?, ?, ?)";
    $params = [$warehouseId, $data['warehouse_name'], $data['region_code'], $data['address']];
    $stmt = sqlsrv_query($conn, $sql, $params);

    return $warehouseId;
}
```

### 4. Update Model - WarehouseItem.php

**SEBELUM:**

```php
public static function create($data)
{
    global $conn;
    $sql = "INSERT INTO warehouse_items (warehouse_id, product_id, stock)
            VALUES (?, ?, ?)";
    $params = [$data['warehouse_id'], $data['product_id'], $data['stock']];
    $stmt = sqlsrv_query($conn, $sql, $params);
    return $stmt;
}
```

**SETELAH:**

```php
public static function create($data)
{
    global $conn;

    // Extract region dari warehouse_id
    $regionCode = extractRegionFromId($data['warehouse_id']);

    // Generate distributed ID
    $warehouseItemId = generateWarehouseItemId($regionCode);

    $sql = "INSERT INTO warehouse_items (warehouse_item_id, warehouse_id, product_id, stock)
            VALUES (?, ?, ?, ?)";
    $params = [$warehouseItemId, $data['warehouse_id'], $data['product_id'], $data['stock']];
    $stmt = sqlsrv_query($conn, $sql, $params);

    return $warehouseItemId;
}
```

### 5. Update Model - Cart.php

**SEBELUM:**

```php
public static function addItem($userId, $warehouseItemId, $qty)
{
    global $conn;

    // Check if item exists
    $existing = self::getByUserAndWarehouseItem($userId, $warehouseItemId);

    if ($existing) {
        // Update quantity
        $newQty = $existing['qty'] + $qty;
        $sql = "UPDATE cart_items SET qty = ? WHERE cart_item_id = ?";
        $params = [$newQty, $existing['cart_item_id']];
    } else {
        // Insert new
        $sql = "INSERT INTO cart_items (user_id, warehouse_item_id, qty) VALUES (?, ?, ?)";
        $params = [$userId, $warehouseItemId, $qty];
    }

    return sqlsrv_query($conn, $sql, $params);
}
```

**SETELAH:**

```php
public static function addItem($userId, $warehouseItemId, $qty)
{
    global $conn;

    // Check if item exists
    $existing = self::getByUserAndWarehouseItem($userId, $warehouseItemId);

    if ($existing) {
        // Update quantity
        $newQty = $existing['qty'] + $qty;
        $sql = "UPDATE cart_items SET qty = ? WHERE cart_item_id = ?";
        $params = [$newQty, $existing['cart_item_id']];
        return sqlsrv_query($conn, $sql, $params);
    } else {
        // Generate distributed ID
        $regionCode = extractRegionFromId($userId);
        $cartItemId = generateCartItemId($regionCode);

        // Insert new
        $sql = "INSERT INTO cart_items (cart_item_id, user_id, warehouse_item_id, qty)
                VALUES (?, ?, ?, ?)";
        $params = [$cartItemId, $userId, $warehouseItemId, $qty];
        return sqlsrv_query($conn, $sql, $params);
    }
}
```

### 6. Update Model - Order.php

**SEBELUM:**

```php
public static function create($userId, $totalAmount)
{
    global $conn;
    $sql = "INSERT INTO orders (user_id, total_amount, order_date)
            VALUES (?, ?, GETDATE())";
    $params = [$userId, $totalAmount];
    $stmt = sqlsrv_query($conn, $sql, $params);

    // Get last inserted ID
    $sql = "SELECT SCOPE_IDENTITY() as order_id";
    $result = sqlsrv_query($conn, $sql);
    $row = sqlsrv_fetch_array($result);

    return $row['order_id'];
}
```

**SETELAH:**

```php
public static function create($userId, $totalAmount)
{
    global $conn;

    // Generate distributed ID
    $regionCode = extractRegionFromId($userId);
    $orderId = generateOrderId($regionCode);

    $sql = "INSERT INTO orders (order_id, user_id, total_amount, order_date)
            VALUES (?, ?, ?, GETDATE())";
    $params = [$orderId, $userId, $totalAmount];
    sqlsrv_query($conn, $sql, $params);

    return $orderId;
}

public static function addItem($orderId, $warehouseItemId, $qty, $price)
{
    global $conn;

    // Generate distributed ID
    $regionCode = extractRegionFromId($orderId);
    $orderItemId = generateOrderItemId($regionCode);

    $sql = "INSERT INTO order_items (order_item_id, order_id, warehouse_item_id, qty, price_at_order)
            VALUES (?, ?, ?, ?, ?)";
    $params = [$orderItemId, $orderId, $warehouseItemId, $qty, $price];

    return sqlsrv_query($conn, $sql, $params);
}
```

---

## 🧪 Testing

### Test 1: Insert User di Region BDG

```php
$userData = [
    'full_name' => 'Test User',
    'email' => 'test@example.com',
    'password' => password_hash('password', PASSWORD_DEFAULT),
    'region_code' => 'BDG'
];

$userId = User::create($userData);
echo "Created User ID: " . $userId; // Output: BDG-U-000001
```

### Test 2: Insert Warehouse di Region JKT

```php
$warehouseData = [
    'warehouse_name' => 'Gudang Jakarta',
    'region_code' => 'JKT',
    'address' => 'Jl. Sudirman'
];

$warehouseId = Warehouse::create($warehouseData);
echo "Created Warehouse ID: " . $warehouseId; // Output: JKT-W-000001
```

### Test 3: Validasi ID

```php
$userId = "BDG-U-000001";

if (isValidDistributedId($userId, 'U')) {
    $region = extractRegionFromId($userId);
    echo "Valid User ID dari region: " . $region; // Output: BDG
}
```

---

## ⚠️ PENTING - Checklist Migrasi

### Database:

- [ ] Backup database lama
- [ ] Jalankan `database_distributed_id.sql`
- [ ] Verifikasi data dengan query SELECT
- [ ] Test stored procedures

### PHP Code:

- [ ] Update `User.php` model
- [ ] Update `Warehouse.php` model
- [ ] Update `WarehouseItem.php` model
- [ ] Update `Cart.php` model
- [ ] Update `Order.php` model
- [ ] Update semua controller yang ada INSERT/UPDATE
- [ ] Update form views (hidden input untuk ID)

### Testing:

- [ ] Test registrasi user baru
- [ ] Test buat warehouse baru
- [ ] Test tambah item ke cart
- [ ] Test checkout order
- [ ] Test filter by region
- [ ] Test merge data dari 2 region ke central

---

## 🔄 Proses Sinkronisasi (Central ↔ Regional)

### Scenario: Regional → Central (Upload)

1. **Regional DB (BDG) punya data:**

   ```
   user_id: BDG-U-000001, BDG-U-000002
   order_id: BDG-O-000001, BDG-O-000002
   ```

2. **Regional DB (JKT) punya data:**

   ```
   user_id: JKT-U-000001, JKT-U-000002
   order_id: JKT-O-000001, JKT-O-000002
   ```

3. **Merge ke Central DB:**

   ```sql
   -- Dari BDG
   INSERT INTO users SELECT * FROM bdg_server.users;
   INSERT INTO orders SELECT * FROM bdg_server.orders;

   -- Dari JKT
   INSERT INTO users SELECT * FROM jkt_server.users;
   INSERT INTO orders SELECT * FROM jkt_server.orders;
   ```

4. **Result di Central DB:** ✅ TIDAK BENTROK
   ```
   user_id: BDG-U-000001, BDG-U-000002, JKT-U-000001, JKT-U-000002
   order_id: BDG-O-000001, BDG-O-000002, JKT-O-000001, JKT-O-000002
   ```

---

## 📝 Catatan Tambahan

### Performa

- VARCHAR ID **sedikit lebih lambat** dari INT untuk JOIN operations
- Namun perbedaannya **tidak signifikan** untuk skala menengah (< 1 juta records)
- Bisa ditambahkan INDEX untuk optimasi:
  ```sql
  CREATE INDEX idx_users_id ON users(user_id);
  CREATE INDEX idx_orders_user ON orders(user_id);
  ```

### Storage

- VARCHAR(50) butuh lebih banyak storage dari INT (4 bytes)
- ID format "BDG-U-000001" = ~14 bytes vs INT = 4 bytes
- Untuk 100k records: ~1 MB extra storage (negligible)

### Alternatif

Jika ingin performa maksimal, bisa gunakan **GUID/UUID** (`UNIQUEIDENTIFIER` di SQL Server):

- Pro: Performa JOIN sama dengan INT
- Con: Susah dibaca manusia, butuh 16 bytes storage

**Kesimpulan:** VARCHAR dengan region prefix adalah **balance terbaik** antara:
✅ Human-readable
✅ Collision-free
✅ Regional tracking
✅ Performa acceptable

---

## 📞 Troubleshooting

### Q: ID generation bentrok antar concurrent request?

**A:** Gunakan stored procedures di SQL (sudah disediakan di migration script) atau tambahkan locking mechanism di PHP.

### Q: Bagaimana cara migrate data lama yang pakai INT?

**A:**

```sql
-- Convert INT to VARCHAR with prefix
UPDATE users SET user_id = 'BDG-U-' + RIGHT('000000' + CAST(user_id AS VARCHAR), 6)
WHERE region_code = 'BDG';
```

### Q: Apakah bisa mix INT dan VARCHAR ID?

**A:** Tidak disarankan. Pilih salah satu strategi untuk consistency.

---

**END OF DOCUMENTATION**
