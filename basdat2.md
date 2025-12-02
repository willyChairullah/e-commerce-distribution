Siap, kita rapikan jadi **script terpisah** khusus:

- VIEW
- FUNCTION (tambahan)
- STORED PROCEDURE (tambahan, termasuk yang pakai CURSOR)
- TRIGGER

Lalu di bawahnya aku jelaskan:

- Dipakai di **halaman apa** di aplikasi
- Contoh **PHP native** (pakai `sqlsrv` untuk SQL Server)

> Script ini **tidak membuat tabel lagi**. Dia mengasumsikan kamu sudah menjalankan script utama yang bikin tabel + SP insert + function `GetNextSequentialID` sebelumnya.

---

## 🔹 1. SQL – File Terpisah (misal: `extra_db_objects.sql`)

```sql
USE warehouse_3;
GO
/* =====================================================
   1) FUNCTION TAMBAHAN
   ===================================================== */

-- Fungsi: Ambil region_code dari user_id (misal: JKT-U-000001 → JKT)
IF OBJECT_ID('dbo.fn_GetRegionFromUserId', 'FN') IS NOT NULL
    DROP FUNCTION dbo.fn_GetRegionFromUserId;
GO

CREATE FUNCTION dbo.fn_GetRegionFromUserId
(
    @user_id VARCHAR(50)
)
RETURNS NVARCHAR(10)
AS
BEGIN
    DECLARE @region NVARCHAR(10);

    -- Ambil substring sebelum '-' pertama
    SET @region = LEFT(@user_id, CHARINDEX('-', @user_id + '-') - 1);

    RETURN @region;
END;
GO

/* =====================================================
   2) VIEWS
   ===================================================== */

-- View: Ringkasan total pesanan per user
IF OBJECT_ID('dbo.v_UserOrdersSummary', 'V') IS NOT NULL
    DROP VIEW dbo.v_UserOrdersSummary;
GO

CREATE VIEW dbo.v_UserOrdersSummary
AS
SELECT
    u.user_id,
    u.full_name,
    u.email,
    u.region_code,
    COUNT(o.order_id)       AS total_orders,
    ISNULL(SUM(o.total_amount), 0) AS total_spent
FROM dbo.users u
LEFT JOIN dbo.orders o
    ON u.user_id = o.user_id
GROUP BY
    u.user_id, u.full_name, u.email, u.region_code;
GO


-- View: Detail stok gudang (gabung gudang, item, produk)
IF OBJECT_ID('dbo.v_WarehouseStockDetail', 'V') IS NOT NULL
    DROP VIEW dbo.v_WarehouseStockDetail;
GO

CREATE VIEW dbo.v_WarehouseStockDetail
AS
SELECT
    w.warehouse_id,
    w.warehouse_name,
    w.region_code,
    wi.warehouse_item_id,
    wi.product_id,
    p.product_name,
    p.price,
    wi.stock,
    wi.created_at AS added_at
FROM dbo.warehouses w
JOIN dbo.warehouse_items wi
    ON w.warehouse_id = wi.warehouse_id
JOIN dbo.products p
    ON wi.product_id = p.product_id;
GO


-- View: Detail isi cart (user, produk, gudang)
IF OBJECT_ID('dbo.v_CartDetails', 'V') IS NOT NULL
    DROP VIEW dbo.v_CartDetails;
GO

CREATE VIEW dbo.v_CartDetails
AS
SELECT
    c.cart_item_id,
    c.user_id,
    u.full_name,
    c.warehouse_item_id,
    w.warehouse_id,
    w.warehouse_name,
    p.product_id,
    p.product_name,
    p.price,
    c.qty,
    (p.price * c.qty) AS line_total,
    c.created_at
FROM dbo.cart_items c
JOIN dbo.users u
    ON c.user_id = u.user_id
JOIN dbo.warehouse_items wi
    ON c.warehouse_item_id = wi.warehouse_item_id
JOIN dbo.warehouses w
    ON wi.warehouse_id = w.warehouse_id
JOIN dbo.products p
    ON wi.product_id = p.product_id;
GO

/* =====================================================
   3) TRIGGER
   ===================================================== */

-- Trigger: Setelah insert order_items → kurangi stok di warehouse_items
IF OBJECT_ID('dbo.trg_OrderItems_AfterInsert_UpdateStock', 'TR') IS NOT NULL
    DROP TRIGGER dbo.trg_OrderItems_AfterInsert_UpdateStock;
GO

CREATE TRIGGER dbo.trg_OrderItems_AfterInsert_UpdateStock
ON dbo.order_items
AFTER INSERT
AS
BEGIN
    SET NOCOUNT ON;

    -- Cek stok cukup sebelum dikurangi
    IF EXISTS (
        SELECT 1
        FROM inserted i
        JOIN dbo.warehouse_items wi
            ON wi.warehouse_item_id = i.warehouse_item_id
        WHERE wi.stock < i.qty
    )
    BEGIN
        RAISERROR('Stok tidak cukup untuk salah satu item pesanan.', 16, 1);
        ROLLBACK TRANSACTION;
        RETURN;
    END;

    -- Kurangi stok
    UPDATE wi
    SET wi.stock = wi.stock - i.qty
    FROM dbo.warehouse_items wi
    JOIN inserted i
        ON wi.warehouse_item_id = i.warehouse_item_id;
END;
GO

/* =====================================================
   4) STORED PROCEDURE (SELECT / REPORT)
   ===================================================== */

-- SP: Ambil daftar pesanan per user (untuk halaman "Riwayat Pesanan" user)
IF OBJECT_ID('dbo.sp_GetUserOrders', 'P') IS NOT NULL
    DROP PROCEDURE dbo.sp_GetUserOrders;
GO

CREATE PROCEDURE dbo.sp_GetUserOrders
    @user_id VARCHAR(50)
AS
BEGIN
    SET NOCOUNT ON;

    SELECT
        o.order_id,
        o.order_date,
        o.total_amount
    FROM dbo.orders o
    WHERE o.user_id = @user_id
    ORDER BY o.order_date DESC;
END;
GO


-- SP: Ambil detail 1 pesanan (header + item)
IF OBJECT_ID('dbo.sp_GetOrderDetail', 'P') IS NOT NULL
    DROP PROCEDURE dbo.sp_GetOrderDetail;
GO

CREATE PROCEDURE dbo.sp_GetOrderDetail
    @order_id VARCHAR(50)
AS
BEGIN
    SET NOCOUNT ON;

    -- Header order
    SELECT
        o.order_id,
        o.user_id,
        u.full_name,
        o.order_date,
        o.total_amount
    FROM dbo.orders o
    JOIN dbo.users u ON o.user_id = u.user_id
    WHERE o.order_id = @order_id;

    -- Detail item
    SELECT
        oi.order_item_id,
        oi.warehouse_item_id,
        w.warehouse_id,
        w.warehouse_name,
        p.product_id,
        p.product_name,
        oi.qty,
        oi.price_at_order,
        (oi.qty * oi.price_at_order) AS line_total
    FROM dbo.order_items oi
    JOIN dbo.warehouse_items wi
        ON oi.warehouse_item_id = wi.warehouse_item_id
    JOIN dbo.warehouses w
        ON wi.warehouse_id = w.warehouse_id
    JOIN dbo.products p
        ON wi.product_id = p.product_id
    WHERE oi.order_id = @order_id;
END;
GO


-- SP: Ambil isi cart user (bisa juga langsung SELECT dari view v_CartDetails)
IF OBJECT_ID('dbo.sp_GetCartByUser', 'P') IS NOT NULL
    DROP PROCEDURE dbo.sp_GetCartByUser;
GO

CREATE PROCEDURE dbo.sp_GetCartByUser
    @user_id VARCHAR(50)
AS
BEGIN
    SET NOCOUNT ON;

    SELECT *
    FROM dbo.v_CartDetails
    WHERE user_id = @user_id
    ORDER BY created_at DESC;
END;
GO

/* =====================================================
   5) STORED PROCEDURE DENGAN CURSOR
      Checkout dari cart → buat order + order_items
      (sekalian menunjukkan penggunaan CURSOR)
   ===================================================== */

IF OBJECT_ID('dbo.sp_CheckoutFromCart_WithCursor', 'P') IS NOT NULL
    DROP PROCEDURE dbo.sp_CheckoutFromCart_WithCursor;
GO

CREATE PROCEDURE dbo.sp_CheckoutFromCart_WithCursor
    @user_id       VARCHAR(50),
    @new_order_id  VARCHAR(50) OUTPUT
AS
BEGIN
    SET NOCOUNT ON;

    DECLARE @total_amount DECIMAL(12,2) = 0;
    DECLARE @tmp_order_id VARCHAR(50);

    -- Hitung total_amount dari cart user
    SELECT
        @total_amount = SUM(p.price * c.qty)
    FROM dbo.cart_items c
    JOIN dbo.warehouse_items wi
        ON c.warehouse_item_id = wi.warehouse_item_id
    JOIN dbo.products p
        ON wi.product_id = p.product_id
    WHERE c.user_id = @user_id;

    IF @total_amount IS NULL OR @total_amount = 0
    BEGIN
        RAISERROR('Cart kosong atau total 0, tidak bisa checkout.', 16, 1);
        RETURN;
    END;

    BEGIN TRY
        BEGIN TRANSACTION;

        -- Buat order via SP yang sudah ada
        EXEC dbo.sp_InsertOrder
            @user_id       = @user_id,
            @total_amount  = @total_amount,
            @new_order_id  = @tmp_order_id OUTPUT;

        -- Cursor untuk iterasi cart user → buat order_items
        DECLARE
            @cart_item_id      VARCHAR(50),
            @warehouse_item_id VARCHAR(50),
            @qty               INT,
            @price_at_order    DECIMAL(10,2);

        DECLARE cart_cursor CURSOR LOCAL FAST_FORWARD FOR
            SELECT
                c.cart_item_id,
                c.warehouse_item_id,
                c.qty,
                p.price
            FROM dbo.cart_items c
            JOIN dbo.warehouse_items wi
                ON c.warehouse_item_id = wi.warehouse_item_id
            JOIN dbo.products p
                ON wi.product_id = p.product_id
            WHERE c.user_id = @user_id;

        OPEN cart_cursor;

        FETCH NEXT FROM cart_cursor INTO @cart_item_id, @warehouse_item_id, @qty, @price_at_order;

        WHILE @@FETCH_STATUS = 0
        BEGIN
            DECLARE @order_item_id VARCHAR(50);

            -- Buat order_item (trigger akan otomatis mengurangi stok)
            EXEC dbo.sp_InsertOrderItem
                @order_id           = @tmp_order_id,
                @warehouse_item_id  = @warehouse_item_id,
                @qty                = @qty,
                @price_at_order     = @price_at_order,
                @new_order_item_id  = @order_item_id OUTPUT;

            FETCH NEXT FROM cart_cursor INTO @cart_item_id, @warehouse_item_id, @qty, @price_at_order;
        END;

        CLOSE cart_cursor;
        DEALLOCATE cart_cursor;

        -- Hapus cart user
        DELETE FROM dbo.cart_items WHERE user_id = @user_id;

        COMMIT TRANSACTION;

        SET @new_order_id = @tmp_order_id;
    END TRY
    BEGIN CATCH
        IF @@TRANCOUNT > 0
            ROLLBACK TRANSACTION;

        DECLARE @ErrMsg NVARCHAR(4000), @ErrSeverity INT;
        SELECT @ErrMsg = ERROR_MESSAGE(), @ErrSeverity = ERROR_SEVERITY();
        RAISERROR(@ErrMsg, @ErrSeverity, 1);
        RETURN;
    END CATCH
END;
GO
```

---

## 🔹 2. Di Halaman Apa Masing-masing Dipakai?

Contoh arsitektur sederhana:

1. **View**

   - `v_WarehouseStockDetail` →
     Halaman **Admin – Manajemen Stok** / **Daftar Stok Gudang**.
   - `v_CartDetails` →
     Halaman **Cart** user (tampilan isi keranjang).
   - `v_UserOrdersSummary` →
     Halaman **Admin – Dashboard User** (lihat total belanja user).

2. **Function**

   - `fn_GetRegionFromUserId` →
     Dipakai di query laporan, misal di **Admin – Laporan Penjualan per Region**:

     ```sql
     SELECT dbo.fn_GetRegionFromUserId(user_id) AS region, SUM(total_amount)
     FROM orders
     GROUP BY dbo.fn_GetRegionFromUserId(user_id);
     ```

3. **Stored Procedure**

   - `sp_InsertUser` → Halaman **Register / Admin tambah user**.
   - `sp_InsertWarehouse`, `sp_InsertWarehouseItem` → **Admin – Manajemen Gudang & Stok**.
   - `sp_GetCartByUser` → **Halaman Cart**.
   - `sp_GetUserOrders`, `sp_GetOrderDetail` → **Riwayat Pesanan**, **Detail Pesanan**.
   - `sp_CheckoutFromCart_WithCursor` → **Halaman Checkout** (tombol “Buat Pesanan”).

4. **Trigger**

   - `trg_OrderItems_AfterInsert_UpdateStock` →
     Aktif **otomatis** setiap ada insert ke `order_items`, biasanya saat:

     - SP `sp_InsertOrderItem` dipanggil di proses **checkout**.

5. **Cursor**

   - Dipakai di dalam `sp_CheckoutFromCart_WithCursor` →
     Proses **checkout** untuk membaca setiap row cart dan bikin `order_items`.

---

## 🔹 3. Contoh PHP Native (SQLSRV) – Cara Pakai

### 3.1. Koneksi Dasar

```php
<?php
$serverName = "localhost";
$connectionOptions = [
    "Database" => "warehouse_3",
    "Uid"      => "sa",
    "PWD"      => "password_kamu"
];

$conn = sqlsrv_connect($serverName, $connectionOptions);

if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}
?>
```

---

### 3.2. Contoh: Register User (pakai `sp_InsertUser`)

Halaman: `register.php` / `admin_user_add.php`

```php
<?php
// ... include koneksi $conn

$full_name   = $_POST['full_name'];
$email       = $_POST['email'];
$password    = password_hash($_POST['password'], PASSWORD_BCRYPT);
$region_code = $_POST['region_code'];
$is_admin    = 0; // atau dari form

$sql = "{CALL sp_InsertUser(?, ?, ?, ?, ?, ?)}";

$new_user_id = null;

$params = [
    [&$full_name,   SQLSRV_PARAM_IN],
    [&$email,       SQLSRV_PARAM_IN],
    [&$password,    SQLSRV_PARAM_IN],
    [&$region_code, SQLSRV_PARAM_IN],
    [&$is_admin,    SQLSRV_PARAM_IN],
    [&$new_user_id, SQLSRV_PARAM_OUT]   // OUTPUT parameter
];

$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

echo "User baru berhasil dibuat dengan ID: " . $new_user_id;
?>
```

---

### 3.3. Contoh: Tampilkan Cart User (pakai `sp_GetCartByUser` atau view)

Halaman: `cart.php`

```php
<?php
// ... include koneksi $conn

$user_id = $_SESSION['user_id'];

$sql = "{CALL sp_GetCartByUser(?)}";
$params = [[$user_id, SQLSRV_PARAM_IN]];

$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

$total = 0;
?>

<table border="1">
    <tr>
        <th>Produk</th>
        <th>Gudang</th>
        <th>Qty</th>
        <th>Harga</th>
        <th>Subtotal</th>
    </tr>

<?php while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)): ?>
    <tr>
        <td><?= htmlspecialchars($row['product_name']) ?></td>
        <td><?= htmlspecialchars($row['warehouse_name']) ?></td>
        <td><?= (int)$row['qty'] ?></td>
        <td><?= number_format($row['price']) ?></td>
        <td><?= number_format($row['line_total']) ?></td>
    </tr>
    <?php $total += $row['line_total']; ?>
<?php endwhile; ?>

<tr>
    <td colspan="4"><strong>Total</strong></td>
    <td><strong><?= number_format($total) ?></strong></td>
</tr>
</table>
```

---

### 3.4. Contoh: Checkout (pakai `sp_CheckoutFromCart_WithCursor`)

Halaman: `checkout.php`

```php
<?php
// ... include koneksi $conn

$user_id = $_SESSION['user_id'];

$sql = "{CALL sp_CheckoutFromCart_WithCursor(?, ?)}";

$new_order_id = null;

$params = [
    [&$user_id,      SQLSRV_PARAM_IN],
    [&$new_order_id, SQLSRV_PARAM_OUT]
];

$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    // Kalau stok nggak cukup, trigger akan RAISERROR dan masuk ke sini
    echo "Checkout gagal:<br>";
    die(print_r(sqlsrv_errors(), true));
}

echo "Checkout berhasil! Order ID: " . $new_order_id;
?>
```

---

### 3.5. Contoh: Riwayat Pesanan User (pakai `sp_GetUserOrders`)

Halaman: `orders.php`

```php
<?php
// ... include koneksi $conn

$user_id = $_SESSION['user_id'];

$sql = "{CALL sp_GetUserOrders(?)}";
$params = [[$user_id, SQLSRV_PARAM_IN]];

$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}
?>

<table border="1">
    <tr>
        <th>Order ID</th>
        <th>Tanggal</th>
        <th>Total</th>
        <th>Aksi</th>
    </tr>

<?php while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)): ?>
    <tr>
        <td><?= htmlspecialchars($row['order_id']) ?></td>
        <td><?= $row['order_date']->format('Y-m-d H:i') ?></td>
        <td><?= number_format($row['total_amount']) ?></td>
        <td><a href="order_detail.php?id=<?= urlencode($row['order_id']) ?>">Detail</a></td>
    </tr>
<?php endwhile; ?>
</table>
```

---

Kalau kamu mau, next step kita bisa:

- Bikin **diagram** sederhana: flow dari `cart` → `checkout` → `orders`.
- Atau aku bantu mapping kode PHP kamu yang sudah ada ke SP/SP di atas (biar nggak perlu rewrite dari nol).
