-- SQL Server Database Schema
-- Warehouse Management & E-commerce System

-- Create Database
IF NOT EXISTS (SELECT * FROM sys.databases WHERE name = 'warehouse_db')
BEGIN
    CREATE DATABASE warehouse_db;
END
GO

USE warehouse_db;
GO

-- Table: users
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[users]') AND type in (N'U'))
BEGIN
    CREATE TABLE users (
        user_id INT IDENTITY(1,1) PRIMARY KEY,
        full_name NVARCHAR(100) NOT NULL,
        email NVARCHAR(100) NOT NULL UNIQUE,
        password NVARCHAR(255) NOT NULL,
        region_code NVARCHAR(10) NOT NULL,
        is_admin BIT DEFAULT 0,
        created_at DATETIME DEFAULT GETDATE()
    );
END
GO

-- Table: categories
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[categories]') AND type in (N'U'))
BEGIN
    CREATE TABLE categories (
        category_id INT IDENTITY(1,1) PRIMARY KEY,
        category_name NVARCHAR(100) NOT NULL,
        created_at DATETIME DEFAULT GETDATE()
    );
END
GO

-- Table: products
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[products]') AND type in (N'U'))
BEGIN
    CREATE TABLE products (
        product_id INT IDENTITY(1,1) PRIMARY KEY,
        name NVARCHAR(200) NOT NULL,
        price DECIMAL(10, 2) NOT NULL,
        photo_url NVARCHAR(255),
        category_id INT,
        created_at DATETIME DEFAULT GETDATE(),
        FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE SET NULL
    );
END
GO

-- Table: warehouses
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[warehouses]') AND type in (N'U'))
BEGIN
    CREATE TABLE warehouses (
        warehouse_id INT IDENTITY(1,1) PRIMARY KEY,
        warehouse_name NVARCHAR(100) NOT NULL,
        region_code NVARCHAR(10) NOT NULL,
        address NVARCHAR(255),
        created_at DATETIME DEFAULT GETDATE()
    );
END
GO

-- Table: warehouse_items (STOCK INVENTORY)
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[warehouse_items]') AND type in (N'U'))
BEGIN
    CREATE TABLE warehouse_items (
        warehouse_item_id INT IDENTITY(1,1) PRIMARY KEY,
        warehouse_id INT NOT NULL,
        product_id INT NOT NULL,
        stock INT NOT NULL DEFAULT 0,
        created_at DATETIME DEFAULT GETDATE(),
        FOREIGN KEY (warehouse_id) REFERENCES warehouses(warehouse_id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
    );
END
GO

-- Table: cart_items
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[cart_items]') AND type in (N'U'))
BEGIN
    CREATE TABLE cart_items (
        cart_item_id INT IDENTITY(1,1) PRIMARY KEY,
        user_id INT NOT NULL,
        warehouse_item_id INT NOT NULL,
        qty INT NOT NULL DEFAULT 1,
        created_at DATETIME DEFAULT GETDATE(),
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
        FOREIGN KEY (warehouse_item_id) REFERENCES warehouse_items(warehouse_item_id) ON DELETE CASCADE
    );
END
GO

-- Table: orders
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[orders]') AND type in (N'U'))
BEGIN
    CREATE TABLE orders (
        order_id INT IDENTITY(1,1) PRIMARY KEY,
        user_id INT NOT NULL,
        total_amount DECIMAL(12, 2) NOT NULL,
        order_date DATETIME DEFAULT GETDATE(),
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
    );
END
GO

-- Table: order_items
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[order_items]') AND type in (N'U'))
BEGIN
    CREATE TABLE order_items (
        order_item_id INT IDENTITY(1,1) PRIMARY KEY,
        order_id INT NOT NULL,
        warehouse_item_id INT NOT NULL,
        qty INT NOT NULL,
        price_at_order DECIMAL(10, 2) NOT NULL,
        FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
        FOREIGN KEY (warehouse_item_id) REFERENCES warehouse_items(warehouse_item_id)
    );
END
GO

-- Insert Sample Data

-- Admin User (password: admin123)
INSERT INTO users (full_name, email, password, region_code, is_admin) 
VALUES ('Admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'JKT', 1);

-- Sample User (password: user123)
INSERT INTO users (full_name, email, password, region_code, is_admin) 
VALUES ('John Doe', 'user@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'BDG', 0);

-- Categories
INSERT INTO categories (category_name) VALUES 
('Elektronik'),
('Fashion'),
('Makanan'),
('Olahraga'),
('Buku');

-- Sample Products
INSERT INTO products (name, price, photo_url, category_id) VALUES
('Laptop ASUS', 7500000, '/assets/img/products/default.svg', 1),
('Smartphone Samsung', 3500000, '/assets/img/products/default.svg', 1),
('Kaos Polo', 150000, '/assets/img/products/default.svg', 2),
('Sepatu Nike', 850000, '/assets/img/products/default.svg', 4),
('Buku Pemrograman PHP', 125000, '/assets/img/products/default.svg', 5);

-- Warehouses
INSERT INTO warehouses (warehouse_name, region_code, address) VALUES
('Gudang Jakarta', 'JKT', 'Jl. Sudirman No. 123, Jakarta'),
('Gudang Bandung', 'BDG', 'Jl. Asia Afrika No. 45, Bandung'),
('Gudang Surabaya', 'SBY', 'Jl. Tunjungan No. 78, Surabaya');

-- Warehouse Items (Stock)
INSERT INTO warehouse_items (warehouse_id, product_id, stock) VALUES
(1, 1, 50),  -- Laptop di Jakarta
(1, 2, 100), -- Samsung di Jakarta
(1, 3, 200), -- Kaos di Jakarta
(2, 1, 30),  -- Laptop di Bandung
(2, 4, 80),  -- Sepatu di Bandung
(3, 2, 75),  -- Samsung di Surabaya
(3, 5, 150); -- Buku di Surabaya

GO

PRINT 'Database schema created successfully!';
PRINT 'Default admin: admin@example.com / admin123';
PRINT 'Default user: user@example.com / user123';
