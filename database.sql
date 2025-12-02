-- Create Database
IF NOT EXISTS (SELECT * FROM sys.databases WHERE name = 'warehouse_db')
BEGIN
    CREATE DATABASE warehouse_db;
END
GO

USE warehouse_db;
GO

/* ============================================
   0. OPTIONAL: Pastikan tabel products ada
   (kalau kamu sudah punya desain sendiri,
    hapus bagian ini dan gunakan punyamu)
   ============================================ */

IF OBJECT_ID('dbo.products', 'U') IS NULL
BEGIN
    CREATE TABLE dbo.products (
        product_id   INT IDENTITY(1,1) PRIMARY KEY,
        product_name NVARCHAR(100) NOT NULL,
        price        DECIMAL(12,2) NOT NULL DEFAULT 0,
        created_at   DATETIME       DEFAULT GETDATE()
    );
END;
GO

/* ============================================
   1. DROP TABLE LAMA (kalau ada)
   ============================================ */

IF OBJECT_ID('dbo.order_items', 'U') IS NOT NULL
    DROP TABLE dbo.order_items;

IF OBJECT_ID('dbo.orders', 'U') IS NOT NULL
    DROP TABLE dbo.orders;

IF OBJECT_ID('dbo.cart_items', 'U') IS NOT NULL
    DROP TABLE dbo.cart_items;

IF OBJECT_ID('dbo.warehouse_items', 'U') IS NOT NULL
    DROP TABLE dbo.warehouse_items;

IF OBJECT_ID('dbo.warehouses', 'U') IS NOT NULL
    DROP TABLE dbo.warehouses;

IF OBJECT_ID('dbo.users', 'U') IS NOT NULL
    DROP TABLE dbo.users;
GO

/* ============================================
   2. BUAT TABEL BARU DENGAN VARCHAR ID
   ============================================ */

-- users
CREATE TABLE dbo.users (
    user_id     VARCHAR(50)   PRIMARY KEY,  -- Format: JKT-U-000001
    full_name   NVARCHAR(100) NOT NULL,
    email       NVARCHAR(100) NOT NULL UNIQUE,
    password    NVARCHAR(255) NOT NULL,
    region_code NVARCHAR(10)  NOT NULL,
    is_admin    BIT           DEFAULT 0,
    created_at  DATETIME      DEFAULT GETDATE()
);
GO

-- warehouses
CREATE TABLE dbo.warehouses (
    warehouse_id   VARCHAR(50)   PRIMARY KEY,  -- Format: BDG-W-000001
    warehouse_name NVARCHAR(100) NOT NULL,
    region_code    NVARCHAR(10)  NOT NULL,
    address        NVARCHAR(255),
    created_at     DATETIME      DEFAULT GETDATE()
);
GO

-- warehouse_items
CREATE TABLE dbo.warehouse_items (
    warehouse_item_id VARCHAR(50) PRIMARY KEY,  -- Format: BDG-WI-000001
    warehouse_id      VARCHAR(50) NOT NULL,
    product_id        INT         NOT NULL,
    stock             INT         NOT NULL DEFAULT 0,
    created_at        DATETIME    DEFAULT GETDATE(),
    CONSTRAINT FK_warehouse_items_warehouse
        FOREIGN KEY (warehouse_id) REFERENCES dbo.warehouses(warehouse_id) ON DELETE CASCADE,
    CONSTRAINT FK_warehouse_items_product
        FOREIGN KEY (product_id)   REFERENCES dbo.products(product_id)     ON DELETE CASCADE
);
GO

-- cart_items
CREATE TABLE dbo.cart_items (
    cart_item_id       VARCHAR(50) PRIMARY KEY,  -- Format: BDG-CI-000001
    user_id            VARCHAR(50) NOT NULL,
    warehouse_item_id  VARCHAR(50) NOT NULL,
    qty                INT         NOT NULL DEFAULT 1,
    created_at         DATETIME    DEFAULT GETDATE(),
    CONSTRAINT FK_cart_items_user
        FOREIGN KEY (user_id) REFERENCES dbo.users(user_id) ON DELETE CASCADE,
    CONSTRAINT FK_cart_items_warehouse_item
        FOREIGN KEY (warehouse_item_id) REFERENCES dbo.warehouse_items(warehouse_item_id) ON DELETE CASCADE
);
GO

-- orders
CREATE TABLE dbo.orders (
    order_id     VARCHAR(50)   PRIMARY KEY,  -- Format: BDG-O-000001
    user_id      VARCHAR(50)   NOT NULL,
    total_amount DECIMAL(12,2) NOT NULL,
    order_date   DATETIME      DEFAULT GETDATE(),
    CONSTRAINT FK_orders_user
        FOREIGN KEY (user_id) REFERENCES dbo.users(user_id) ON DELETE CASCADE
);
GO

-- order_items
CREATE TABLE dbo.order_items (
    order_item_id      VARCHAR(50)   PRIMARY KEY,  -- Format: BDG-OI-000001
    order_id           VARCHAR(50)   NOT NULL,
    warehouse_item_id  VARCHAR(50)   NOT NULL,
    qty                INT           NOT NULL,
    price_at_order     DECIMAL(10,2) NOT NULL,
    CONSTRAINT FK_order_items_order
        FOREIGN KEY (order_id)          REFERENCES dbo.orders(order_id) ON DELETE CASCADE,
    CONSTRAINT FK_order_items_warehouse_item
        FOREIGN KEY (warehouse_item_id) REFERENCES dbo.warehouse_items(warehouse_item_id)
);
GO

/* ============================================
   3. INDEXES
   ============================================ */

CREATE INDEX idx_users_region_code         ON dbo.users(region_code);
CREATE INDEX idx_users_email               ON dbo.users(email);
CREATE INDEX idx_warehouses_region_code    ON dbo.warehouses(region_code);
CREATE INDEX idx_warehouse_items_warehouse ON dbo.warehouse_items(warehouse_id);
CREATE INDEX idx_warehouse_items_product   ON dbo.warehouse_items(product_id);
CREATE INDEX idx_cart_items_user           ON dbo.cart_items(user_id);
CREATE INDEX idx_orders_user               ON dbo.orders(user_id);
CREATE INDEX idx_order_items_order         ON dbo.order_items(order_id);
GO

/* ============================================
   4. FUNCTION ID GENERATOR
   ============================================ */

PRINT 'Creating ID generation function...';
GO

IF OBJECT_ID('dbo.GetNextSequentialID', 'FN') IS NOT NULL
    DROP FUNCTION dbo.GetNextSequentialID;
GO

CREATE FUNCTION dbo.GetNextSequentialID(
    @prefix    VARCHAR(20),
    @tableName VARCHAR(50)
)
RETURNS VARCHAR(50)
AS
BEGIN
    DECLARE @maxId      VARCHAR(50);
    DECLARE @nextNumber INT;
    DECLARE @newId      VARCHAR(50);
    
    IF @tableName = 'users'
        SELECT @maxId = MAX(user_id)
        FROM dbo.users
        WHERE user_id LIKE @prefix + '%';
    ELSE IF @tableName = 'warehouses'
        SELECT @maxId = MAX(warehouse_id)
        FROM dbo.warehouses
        WHERE warehouse_id LIKE @prefix + '%';
    ELSE IF @tableName = 'warehouse_items'
        SELECT @maxId = MAX(warehouse_item_id)
        FROM dbo.warehouse_items
        WHERE warehouse_item_id LIKE @prefix + '%';
    ELSE IF @tableName = 'cart_items'
        SELECT @maxId = MAX(cart_item_id)
        FROM dbo.cart_items
        WHERE cart_item_id LIKE @prefix + '%';
    ELSE IF @tableName = 'orders'
        SELECT @maxId = MAX(order_id)
        FROM dbo.orders
        WHERE order_id LIKE @prefix + '%';
    ELSE IF @tableName = 'order_items'
        SELECT @maxId = MAX(order_item_id)
        FROM dbo.order_items
        WHERE order_item_id LIKE @prefix + '%';
    
    IF @maxId IS NULL
        SET @nextNumber = 1;
    ELSE
        SET @nextNumber = CAST(RIGHT(@maxId, 6) AS INT) + 1;
    
    SET @newId = @prefix + RIGHT('000000' + CAST(@nextNumber AS VARCHAR(6)), 6);
    
    RETURN @newId;
END;
GO

/* ============================================
   5. STORED PROCEDURES
   ============================================ */

PRINT 'Creating stored procedures...';
GO

-- Hapus kalau sudah ada
IF OBJECT_ID('dbo.sp_InsertUser', 'P') IS NOT NULL
    DROP PROCEDURE dbo.sp_InsertUser;
IF OBJECT_ID('dbo.sp_InsertWarehouse', 'P') IS NOT NULL
    DROP PROCEDURE dbo.sp_InsertWarehouse;
IF OBJECT_ID('dbo.sp_InsertWarehouseItem', 'P') IS NOT NULL
    DROP PROCEDURE dbo.sp_InsertWarehouseItem;
IF OBJECT_ID('dbo.sp_InsertCartItem', 'P') IS NOT NULL
    DROP PROCEDURE dbo.sp_InsertCartItem;
IF OBJECT_ID('dbo.sp_InsertOrder', 'P') IS NOT NULL
    DROP PROCEDURE dbo.sp_InsertOrder;
IF OBJECT_ID('dbo.sp_InsertOrderItem', 'P') IS NOT NULL
    DROP PROCEDURE dbo.sp_InsertOrderItem;
GO

-- sp_InsertUser
CREATE PROCEDURE dbo.sp_InsertUser
    @full_name    NVARCHAR(100),
    @email        NVARCHAR(100),
    @password     NVARCHAR(255),
    @region_code  NVARCHAR(10),
    @is_admin     BIT = 0,
    @new_user_id  VARCHAR(50) OUTPUT
AS
BEGIN
    SET NOCOUNT ON;
    
    DECLARE @prefix VARCHAR(20) = @region_code + '-U-';
    SET @new_user_id = dbo.GetNextSequentialID(@prefix, 'users');
    
    INSERT INTO dbo.users (user_id, full_name, email, password, region_code, is_admin, created_at)
    VALUES (@new_user_id, @full_name, @email, @password, @region_code, @is_admin, GETDATE());
END;
GO

-- sp_InsertWarehouse
CREATE PROCEDURE dbo.sp_InsertWarehouse
    @warehouse_name    NVARCHAR(100),
    @region_code       NVARCHAR(10),
    @address           NVARCHAR(255),
    @new_warehouse_id  VARCHAR(50) OUTPUT
AS
BEGIN
    SET NOCOUNT ON;
    
    DECLARE @prefix VARCHAR(20) = @region_code + '-W-';
    SET @new_warehouse_id = dbo.GetNextSequentialID(@prefix, 'warehouses');
    
    INSERT INTO dbo.warehouses (warehouse_id, warehouse_name, region_code, address, created_at)
    VALUES (@new_warehouse_id, @warehouse_name, @region_code, @address, GETDATE());
END;
GO

-- sp_InsertWarehouseItem
CREATE PROCEDURE dbo.sp_InsertWarehouseItem
    @warehouse_id          VARCHAR(50),
    @product_id            INT,
    @stock                 INT,
    @new_warehouse_item_id VARCHAR(50) OUTPUT
AS
BEGIN
    SET NOCOUNT ON;
    
    DECLARE @region_code VARCHAR(10);
    SET @region_code = LEFT(@warehouse_id, CHARINDEX('-', @warehouse_id) - 1);
    
    DECLARE @prefix VARCHAR(20) = @region_code + '-WI-';
    SET @new_warehouse_item_id = dbo.GetNextSequentialID(@prefix, 'warehouse_items');
    
    INSERT INTO dbo.warehouse_items (warehouse_item_id, warehouse_id, product_id, stock, created_at)
    VALUES (@new_warehouse_item_id, @warehouse_id, @product_id, @stock, GETDATE());
END;
GO

-- sp_InsertCartItem
CREATE PROCEDURE dbo.sp_InsertCartItem
    @user_id           VARCHAR(50),
    @warehouse_item_id VARCHAR(50),
    @qty               INT,
    @new_cart_item_id  VARCHAR(50) OUTPUT
AS
BEGIN
    SET NOCOUNT ON;
    
    DECLARE @region_code VARCHAR(10);
    SET @region_code = LEFT(@user_id, CHARINDEX('-', @user_id) - 1);
    
    DECLARE @prefix VARCHAR(20) = @region_code + '-CI-';
    SET @new_cart_item_id = dbo.GetNextSequentialID(@prefix, 'cart_items');
    
    INSERT INTO dbo.cart_items (cart_item_id, user_id, warehouse_item_id, qty, created_at)
    VALUES (@new_cart_item_id, @user_id, @warehouse_item_id, @qty, GETDATE());
END;
GO

-- sp_InsertOrder
CREATE PROCEDURE dbo.sp_InsertOrder
    @user_id        VARCHAR(50),
    @total_amount   DECIMAL(12, 2),
    @new_order_id   VARCHAR(50) OUTPUT
AS
BEGIN
    SET NOCOUNT ON;
    
    DECLARE @region_code VARCHAR(10);
    SET @region_code = LEFT(@user_id, CHARINDEX('-', @user_id) - 1);
    
    DECLARE @prefix VARCHAR(20) = @region_code + '-O-';
    SET @new_order_id = dbo.GetNextSequentialID(@prefix, 'orders');
    
    INSERT INTO dbo.orders (order_id, user_id, total_amount, order_date)
    VALUES (@new_order_id, @user_id, @total_amount, GETDATE());
END;
GO

-- sp_InsertOrderItem
CREATE PROCEDURE dbo.sp_InsertOrderItem
    @order_id           VARCHAR(50),
    @warehouse_item_id  VARCHAR(50),
    @qty                INT,
    @price_at_order     DECIMAL(10, 2),
    @new_order_item_id  VARCHAR(50) OUTPUT
AS
BEGIN
    SET NOCOUNT ON;
    
    DECLARE @region_code VARCHAR(10);
    SET @region_code = LEFT(@order_id, CHARINDEX('-', @order_id) - 1);
    
    DECLARE @prefix VARCHAR(20) = @region_code + '-OI-';
    SET @new_order_item_id = dbo.GetNextSequentialID(@prefix, 'order_items');
    
    INSERT INTO dbo.order_items (order_item_id, order_id, warehouse_item_id, qty, price_at_order)
    VALUES (@new_order_item_id, @order_id, @warehouse_item_id, @qty, @price_at_order);
END;
GO

/* ============================================
   6. SAMPLE DATA
   ============================================ */

PRINT 'Inserting sample data...';
GO

DECLARE @userId1 VARCHAR(50), @userId2 VARCHAR(50), @userId3 VARCHAR(50);

EXEC dbo.sp_InsertUser 'Admin Pusat', 'admin@example.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'JKT', 1, @userId1 OUTPUT;

EXEC dbo.sp_InsertUser 'John Doe', 'john@example.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'BDG', 0, @userId2 OUTPUT;

EXEC dbo.sp_InsertUser 'Jane Smith', 'jane@example.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'SBY', 0, @userId3 OUTPUT;

DECLARE @warehouseId1 VARCHAR(50), @warehouseId2 VARCHAR(50), @warehouseId3 VARCHAR(50);

EXEC dbo.sp_InsertWarehouse 'Gudang Jakarta Pusat',  'JKT', 'Jl. Sudirman No. 1, Jakarta',   @warehouseId1 OUTPUT;
EXEC dbo.sp_InsertWarehouse 'Gudang Bandung Utara',  'BDG', 'Jl. Dago No. 10, Bandung',       @warehouseId2 OUTPUT;
EXEC dbo.sp_InsertWarehouse 'Gudang Surabaya Timur', 'SBY', 'Jl. Ahmad Yani No. 5, Surabaya', @warehouseId3 OUTPUT;

-- Tambah 1 produk dummy kalau belum ada
IF NOT EXISTS (SELECT 1 FROM dbo.products WHERE product_id = 1)
BEGIN
    INSERT INTO dbo.products(product_name, price) VALUES ('Produk Contoh', 100000);
END;

DECLARE @warehouseItemId1 VARCHAR(50), @warehouseItemId2 VARCHAR(50);

IF EXISTS (SELECT 1 FROM dbo.products WHERE product_id = 1)
BEGIN
    EXEC dbo.sp_InsertWarehouseItem @warehouseId1, 1, 100, @warehouseItemId1 OUTPUT;
    EXEC dbo.sp_InsertWarehouseItem @warehouseId2, 1,  50, @warehouseItemId2 OUTPUT;
END;
GO

/* ============================================
   7. VERIFIKASI
   ============================================ */

PRINT 'Users:';
SELECT user_id, full_name, email, region_code FROM dbo.users;

PRINT 'Warehouses:';
SELECT warehouse_id, warehouse_name, region_code FROM dbo.warehouses;

PRINT 'Warehouse Items:';
SELECT warehouse_item_id, warehouse_id, product_id, stock FROM dbo.warehouse_items;
GO
