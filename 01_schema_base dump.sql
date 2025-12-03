-- Create Database
IF NOT EXISTS (SELECT * FROM sys.databases WHERE name = 'warehouse_3')
BEGIN
    CREATE DATABASE warehouse_3;
END
GO

USE warehouse_3;
GO

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

IF OBJECT_ID('dbo.products', 'U') IS NOT NULL
    DROP TABLE dbo.products;

IF OBJECT_ID('dbo.categories', 'U') IS NOT NULL
    DROP TABLE dbo.categories;
GO

-- categories (global table)
CREATE TABLE dbo.categories (
    category_id   INT IDENTITY(1,1) PRIMARY KEY,
    category_name NVARCHAR(100) NOT NULL,
    created_at    DATETIME DEFAULT GETDATE()
);
GO

-- products (referensi untuk warehouse_items)
CREATE TABLE dbo.products (
    product_id   INT IDENTITY(1,1) PRIMARY KEY,
    product_name NVARCHAR(100) NOT NULL,
    price        DECIMAL(12,2) NOT NULL DEFAULT 0,
    photo_url    NVARCHAR(255) DEFAULT '/assets/img/products/default.svg',
    category_id  INT NULL,
    created_at   DATETIME DEFAULT GETDATE(),
    CONSTRAINT FK_products_category
        FOREIGN KEY (category_id) REFERENCES dbo.categories(category_id) ON DELETE SET NULL
);
GO

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

-- INDEXES
CREATE INDEX idx_products_category         ON dbo.products(category_id);
CREATE INDEX idx_users_region_code         ON dbo.users(region_code);
CREATE INDEX idx_users_email               ON dbo.users(email);
CREATE INDEX idx_warehouses_region_code    ON dbo.warehouses(region_code);
CREATE INDEX idx_warehouse_items_warehouse ON dbo.warehouse_items(warehouse_id);
CREATE INDEX idx_warehouse_items_product   ON dbo.warehouse_items(product_id);
CREATE INDEX idx_cart_items_user           ON dbo.cart_items(user_id);
CREATE INDEX idx_orders_user               ON dbo.orders(user_id);
CREATE INDEX idx_order_items_order         ON dbo.order_items(order_id);
GO

-- 3.1. Tambah kategori contoh
INSERT INTO dbo.categories (category_name)
VALUES 
    ('Elektronik'),
    ('Pakaian'),
    ('Makanan'),
    ('Minuman'),
    ('Peralatan Rumah Tangga');
GO

-- 3.2. Tambah produk contoh
INSERT INTO dbo.products (product_name, price, category_id, photo_url)
VALUES 
    ('Laptop Gaming', 15000000, 1, '/assets/img/products/default.svg'),
    ('Mouse Wireless', 250000, 1, '/assets/img/products/default.svg'),
    ('Keyboard Mechanical', 750000, 1, '/assets/img/products/default.svg'),
    ('Kaos Polos', 85000, 2, '/assets/img/products/default.svg'),
    ('Celana Jeans', 250000, 2, '/assets/img/products/default.svg');
GO

-- 3.3. Tambah user contoh
INSERT INTO dbo.users (user_id, full_name, email, password, region_code, is_admin)
VALUES
    ('MDR-U-000001', 'Admin Madura', 'admin@example.com',
     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'MDR', 1),
    ('MDR-U-000002', 'John Doe', 'john@example.com',
     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'MDR', 0),
    ('SBY-U-000001', 'Jane Smith', 'jane@example.com',
     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'SBY', 0);
GO

-- 3.4. Tambah warehouse contoh
INSERT INTO dbo.warehouses (warehouse_id, warehouse_name, region_code, address)
VALUES
    ('MDR-W-000001', 'Gudang Madura Pusat',  'MDR', 'Jl. Raya Bangkalan No. 1, Madura'),
    ('SBY-W-000001', 'Gudang Surabaya Timur', 'SBY', 'Jl. Ahmad Yani No. 5, Surabaya');
GO

-- 3.5. Tambah warehouse_items (stok contoh)
INSERT INTO dbo.warehouse_items (warehouse_item_id, warehouse_id, product_id, stock)
VALUES
    ('MDR-WI-000001', 'MDR-W-000001', 1, 100),
    ('SBY-WI-000001', 'SBY-W-000001', 1,  75);
GO

-- Contoh: user MDR-U-000002 punya item di cart
INSERT INTO dbo.cart_items (cart_item_id, user_id, warehouse_item_id, qty)
VALUES
    ('MDR-CI-000001', 'MDR-U-000002', 'MDR-WI-000001', 2);
GO

-- Contoh: 1 order manual untuk demo
INSERT INTO dbo.orders (order_id, user_id, total_amount, order_date)
VALUES
    ('MDR-O-000001', 'MDR-U-000002', 200000, GETDATE());
GO

INSERT INTO dbo.order_items (order_item_id, order_id, warehouse_item_id, qty, price_at_order)
VALUES
    ('MDR-OI-000001', 'MDR-O-000001', 'MDR-WI-000001', 2, 100000);
GO

/* ============================================
   7. VERIFIKASI
   (HANYA SELECT, TIDAK ADA VIEW/FUNCTION/SP/TRIGGER/CURSOR)
   ============================================ */

PRINT 'Categories:';
SELECT category_id, category_name FROM dbo.categories;

PRINT 'Products:';
SELECT product_id, product_name, price, category_id, photo_url FROM dbo.products;

PRINT 'Users:';
SELECT user_id, full_name, email, region_code, is_admin FROM dbo.users;

PRINT 'Warehouses:';
SELECT warehouse_id, warehouse_name, region_code, address FROM dbo.warehouses;

PRINT 'Warehouse Items:';
SELECT warehouse_item_id, warehouse_id, product_id, stock FROM dbo.warehouse_items;

PRINT 'Cart Items:';
SELECT cart_item_id, user_id, warehouse_item_id, qty FROM dbo.cart_items;

PRINT 'Orders:';
SELECT order_id, user_id, total_amount, order_date FROM dbo.orders;

PRINT 'Order Items:';
SELECT order_item_id, order_id, warehouse_item_id, qty, price_at_order FROM dbo.order_items;
GO