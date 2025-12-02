-- ============================================
-- SQL MIGRATION - DISTRIBUTED ID SYSTEM
-- Mengubah ID dari INT IDENTITY ke VARCHAR dengan Region Prefix
-- ============================================

USE warehouse_db;
GO

-- ============================================
-- BACKUP TABLES FIRST (PENTING!)
-- ============================================

PRINT 'Creating backup tables...';

-- Backup existing tables
SELECT * INTO users_backup FROM users;
SELECT * INTO warehouses_backup FROM warehouses;
SELECT * INTO warehouse_items_backup FROM warehouse_items;
SELECT * INTO cart_items_backup FROM cart_items;
SELECT * INTO orders_backup FROM orders;
SELECT * INTO order_items_backup FROM order_items;

PRINT 'Backup completed!';
GO

-- ============================================
-- DROP EXISTING FOREIGN KEYS
-- ============================================

PRINT 'Dropping foreign key constraints...';

-- Drop FK dari order_items
IF EXISTS (SELECT * FROM sys.foreign_keys WHERE name = 'FK__order_ite__order__5DCAEF64')
    ALTER TABLE order_items DROP CONSTRAINT FK__order_ite__order__5DCAEF64;

IF EXISTS (SELECT * FROM sys.foreign_keys WHERE name = 'FK__order_ite__wareh__5EBF139D')
    ALTER TABLE order_items DROP CONSTRAINT FK__order_ite__wareh__5EBF139D;

-- Drop FK dari orders
IF EXISTS (SELECT * FROM sys.foreign_keys WHERE name = 'FK__orders__user_id__5AEE82B9')
    ALTER TABLE orders DROP CONSTRAINT FK__orders__user_id__5AEE82B9;

-- Drop FK dari cart_items
IF EXISTS (SELECT * FROM sys.foreign_keys WHERE name = 'FK__cart_item__user___52593CB8')
    ALTER TABLE cart_items DROP CONSTRAINT FK__cart_item__user___52593CB8;

IF EXISTS (SELECT * FROM sys.foreign_keys WHERE name = 'FK__cart_item__wareh__534D60F1')
    ALTER TABLE cart_items DROP CONSTRAINT FK__cart_item__wareh__534D60F1;

-- Drop FK dari warehouse_items
IF EXISTS (SELECT * FROM sys.foreign_keys WHERE name = 'FK__warehouse__wareh__4BAC3F29')
    ALTER TABLE warehouse_items DROP CONSTRAINT FK__warehouse__wareh__4BAC3F29;

IF EXISTS (SELECT * FROM sys.foreign_keys WHERE name = 'FK__warehouse__produ__4CA06362')
    ALTER TABLE warehouse_items DROP CONSTRAINT FK__warehouse__produ__4CA06362;

PRINT 'Foreign keys dropped!';
GO

-- ============================================
-- DROP OLD TABLES
-- ============================================

PRINT 'Dropping old tables...';

DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS cart_items;
DROP TABLE IF EXISTS warehouse_items;
DROP TABLE IF EXISTS warehouses;
DROP TABLE IF EXISTS users;

PRINT 'Old tables dropped!';
GO

-- ============================================
-- CREATE NEW TABLES WITH VARCHAR ID
-- ============================================

PRINT 'Creating new tables with distributed ID system...';

-- Table: users (VARCHAR ID dengan region prefix)
CREATE TABLE users (
    user_id VARCHAR(50) PRIMARY KEY,  -- Format: JKT-U-000001
    full_name NVARCHAR(100) NOT NULL,
    email NVARCHAR(100) NOT NULL UNIQUE,
    password NVARCHAR(255) NOT NULL,
    region_code NVARCHAR(10) NOT NULL,
    is_admin BIT DEFAULT 0,
    created_at DATETIME DEFAULT GETDATE()
);
GO

-- Table: warehouses (VARCHAR ID dengan region prefix)
CREATE TABLE warehouses (
    warehouse_id VARCHAR(50) PRIMARY KEY,  -- Format: BDG-W-000001
    warehouse_name NVARCHAR(100) NOT NULL,
    region_code NVARCHAR(10) NOT NULL,
    address NVARCHAR(255),
    created_at DATETIME DEFAULT GETDATE()
);
GO

-- Table: warehouse_items (VARCHAR ID dengan region prefix)
CREATE TABLE warehouse_items (
    warehouse_item_id VARCHAR(50) PRIMARY KEY,  -- Format: BDG-WI-000001
    warehouse_id VARCHAR(50) NOT NULL,
    product_id INT NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT GETDATE(),
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(warehouse_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
);
GO

-- Table: cart_items (VARCHAR ID dengan region prefix)
CREATE TABLE cart_items (
    cart_item_id VARCHAR(50) PRIMARY KEY,  -- Format: BDG-CI-000001
    user_id VARCHAR(50) NOT NULL,
    warehouse_item_id VARCHAR(50) NOT NULL,
    qty INT NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT GETDATE(),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (warehouse_item_id) REFERENCES warehouse_items(warehouse_item_id) ON DELETE CASCADE
);
GO

-- Table: orders (VARCHAR ID dengan region prefix)
CREATE TABLE orders (
    order_id VARCHAR(50) PRIMARY KEY,  -- Format: BDG-O-000001
    user_id VARCHAR(50) NOT NULL,
    total_amount DECIMAL(12, 2) NOT NULL,
    order_date DATETIME DEFAULT GETDATE(),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);
GO

-- Table: order_items (VARCHAR ID dengan region prefix)
CREATE TABLE order_items (
    order_item_id VARCHAR(50) PRIMARY KEY,  -- Format: BDG-OI-000001
    order_id VARCHAR(50) NOT NULL,
    warehouse_item_id VARCHAR(50) NOT NULL,
    qty INT NOT NULL,
    price_at_order DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (warehouse_item_id) REFERENCES warehouse_items(warehouse_item_id)
);
GO

PRINT 'New tables created with distributed ID system!';
GO

-- ============================================
-- CREATE INDEXES FOR PERFORMANCE
-- ============================================

PRINT 'Creating indexes...';

CREATE INDEX idx_users_region_code ON users(region_code);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_warehouses_region_code ON warehouses(region_code);
CREATE INDEX idx_warehouse_items_warehouse ON warehouse_items(warehouse_id);
CREATE INDEX idx_warehouse_items_product ON warehouse_items(product_id);
CREATE INDEX idx_cart_items_user ON cart_items(user_id);
CREATE INDEX idx_orders_user ON orders(user_id);
CREATE INDEX idx_order_items_order ON order_items(order_id);

PRINT 'Indexes created!';
GO

-- ============================================
-- CREATE FUNCTIONS FOR ID GENERATION
-- ============================================

PRINT 'Creating ID generation functions...';

-- Function: Generate next sequential number for a prefix
CREATE OR ALTER FUNCTION dbo.GetNextSequentialID(
    @prefix VARCHAR(20),
    @tableName VARCHAR(50)
)
RETURNS VARCHAR(50)
AS
BEGIN
    DECLARE @maxId VARCHAR(50);
    DECLARE @nextNumber INT;
    DECLARE @newId VARCHAR(50);
    
    -- Get max ID for this prefix
    IF @tableName = 'users'
        SELECT @maxId = MAX(user_id) FROM users WHERE user_id LIKE @prefix + '%';
    ELSE IF @tableName = 'warehouses'
        SELECT @maxId = MAX(warehouse_id) FROM warehouses WHERE warehouse_id LIKE @prefix + '%';
    ELSE IF @tableName = 'warehouse_items'
        SELECT @maxId = MAX(warehouse_item_id) FROM warehouse_items WHERE warehouse_item_id LIKE @prefix + '%';
    ELSE IF @tableName = 'cart_items'
        SELECT @maxId = MAX(cart_item_id) FROM cart_items WHERE cart_item_id LIKE @prefix + '%';
    ELSE IF @tableName = 'orders'
        SELECT @maxId = MAX(order_id) FROM orders WHERE order_id LIKE @prefix + '%';
    ELSE IF @tableName = 'order_items'
        SELECT @maxId = MAX(order_item_id) FROM order_items WHERE order_item_id LIKE @prefix + '%';
    
    -- Extract number and increment
    IF @maxId IS NULL
        SET @nextNumber = 1;
    ELSE
        SET @nextNumber = CAST(RIGHT(@maxId, 6) AS INT) + 1;
    
    -- Format: PREFIX-000001
    SET @newId = @prefix + RIGHT('000000' + CAST(@nextNumber AS VARCHAR), 6);
    
    RETURN @newId;
END;
GO

PRINT 'ID generation function created!';
GO

-- ============================================
-- CREATE STORED PROCEDURES FOR INSERT
-- ============================================

PRINT 'Creating stored procedures...';

-- SP: Insert User
CREATE OR ALTER PROCEDURE sp_InsertUser
    @full_name NVARCHAR(100),
    @email NVARCHAR(100),
    @password NVARCHAR(255),
    @region_code NVARCHAR(10),
    @is_admin BIT = 0,
    @new_user_id VARCHAR(50) OUTPUT
AS
BEGIN
    SET NOCOUNT ON;
    
    -- Generate new ID: {REGION}-U-000001
    DECLARE @prefix VARCHAR(20) = @region_code + '-U-';
    SET @new_user_id = dbo.GetNextSequentialID(@prefix, 'users');
    
    INSERT INTO users (user_id, full_name, email, password, region_code, is_admin, created_at)
    VALUES (@new_user_id, @full_name, @email, @password, @region_code, @is_admin, GETDATE());
END;
GO

-- SP: Insert Warehouse
CREATE OR ALTER PROCEDURE sp_InsertWarehouse
    @warehouse_name NVARCHAR(100),
    @region_code NVARCHAR(10),
    @address NVARCHAR(255),
    @new_warehouse_id VARCHAR(50) OUTPUT
AS
BEGIN
    SET NOCOUNT ON;
    
    -- Generate new ID: {REGION}-W-000001
    DECLARE @prefix VARCHAR(20) = @region_code + '-W-';
    SET @new_warehouse_id = dbo.GetNextSequentialID(@prefix, 'warehouses');
    
    INSERT INTO warehouses (warehouse_id, warehouse_name, region_code, address, created_at)
    VALUES (@new_warehouse_id, @warehouse_name, @region_code, @address, GETDATE());
END;
GO

-- SP: Insert Warehouse Item
CREATE OR ALTER PROCEDURE sp_InsertWarehouseItem
    @warehouse_id VARCHAR(50),
    @product_id INT,
    @stock INT,
    @new_warehouse_item_id VARCHAR(50) OUTPUT
AS
BEGIN
    SET NOCOUNT ON;
    
    -- Extract region code from warehouse_id (first part before first dash)
    DECLARE @region_code VARCHAR(10);
    SET @region_code = LEFT(@warehouse_id, CHARINDEX('-', @warehouse_id) - 1);
    
    -- Generate new ID: {REGION}-WI-000001
    DECLARE @prefix VARCHAR(20) = @region_code + '-WI-';
    SET @new_warehouse_item_id = dbo.GetNextSequentialID(@prefix, 'warehouse_items');
    
    INSERT INTO warehouse_items (warehouse_item_id, warehouse_id, product_id, stock, created_at)
    VALUES (@new_warehouse_item_id, @warehouse_id, @product_id, @stock, GETDATE());
END;
GO

-- SP: Insert Cart Item
CREATE OR ALTER PROCEDURE sp_InsertCartItem
    @user_id VARCHAR(50),
    @warehouse_item_id VARCHAR(50),
    @qty INT,
    @new_cart_item_id VARCHAR(50) OUTPUT
AS
BEGIN
    SET NOCOUNT ON;
    
    -- Extract region code from user_id
    DECLARE @region_code VARCHAR(10);
    SET @region_code = LEFT(@user_id, CHARINDEX('-', @user_id) - 1);
    
    -- Generate new ID: {REGION}-CI-000001
    DECLARE @prefix VARCHAR(20) = @region_code + '-CI-';
    SET @new_cart_item_id = dbo.GetNextSequentialID(@prefix, 'cart_items');
    
    INSERT INTO cart_items (cart_item_id, user_id, warehouse_item_id, qty, created_at)
    VALUES (@new_cart_item_id, @user_id, @warehouse_item_id, @qty, GETDATE());
END;
GO

-- SP: Insert Order
CREATE OR ALTER PROCEDURE sp_InsertOrder
    @user_id VARCHAR(50),
    @total_amount DECIMAL(12, 2),
    @new_order_id VARCHAR(50) OUTPUT
AS
BEGIN
    SET NOCOUNT ON;
    
    -- Extract region code from user_id
    DECLARE @region_code VARCHAR(10);
    SET @region_code = LEFT(@user_id, CHARINDEX('-', @user_id) - 1);
    
    -- Generate new ID: {REGION}-O-000001
    DECLARE @prefix VARCHAR(20) = @region_code + '-O-';
    SET @new_order_id = dbo.GetNextSequentialID(@prefix, 'orders');
    
    INSERT INTO orders (order_id, user_id, total_amount, order_date)
    VALUES (@new_order_id, @user_id, @total_amount, GETDATE());
END;
GO

-- SP: Insert Order Item
CREATE OR ALTER PROCEDURE sp_InsertOrderItem
    @order_id VARCHAR(50),
    @warehouse_item_id VARCHAR(50),
    @qty INT,
    @price_at_order DECIMAL(10, 2),
    @new_order_item_id VARCHAR(50) OUTPUT
AS
BEGIN
    SET NOCOUNT ON;
    
    -- Extract region code from order_id
    DECLARE @region_code VARCHAR(10);
    SET @region_code = LEFT(@order_id, CHARINDEX('-', @order_id) - 1);
    
    -- Generate new ID: {REGION}-OI-000001
    DECLARE @prefix VARCHAR(20) = @region_code + '-OI-';
    SET @new_order_item_id = dbo.GetNextSequentialID(@prefix, 'order_items');
    
    INSERT INTO order_items (order_item_id, order_id, warehouse_item_id, qty, price_at_order)
    VALUES (@new_order_item_id, @order_id, @warehouse_item_id, @qty, @price_at_order);
END;
GO

PRINT 'Stored procedures created!';
GO

-- ============================================
-- INSERT SAMPLE DATA
-- ============================================

PRINT 'Inserting sample data...';

-- Insert sample users
DECLARE @userId1 VARCHAR(50), @userId2 VARCHAR(50), @userId3 VARCHAR(50);

EXEC sp_InsertUser 'Admin Pusat', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'JKT', 1, @userId1 OUTPUT;
EXEC sp_InsertUser 'John Doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'BDG', 0, @userId2 OUTPUT;
EXEC sp_InsertUser 'Jane Smith', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'SBY', 0, @userId3 OUTPUT;

-- Insert sample warehouses
DECLARE @warehouseId1 VARCHAR(50), @warehouseId2 VARCHAR(50), @warehouseId3 VARCHAR(50);

EXEC sp_InsertWarehouse 'Gudang Jakarta Pusat', 'JKT', 'Jl. Sudirman No. 1, Jakarta', @warehouseId1 OUTPUT;
EXEC sp_InsertWarehouse 'Gudang Bandung Utara', 'BDG', 'Jl. Dago No. 10, Bandung', @warehouseId2 OUTPUT;
EXEC sp_InsertWarehouse 'Gudang Surabaya Timur', 'SBY', 'Jl. Ahmad Yani No. 5, Surabaya', @warehouseId3 OUTPUT;

-- Insert warehouse items (assuming products with ID 1-3 exist)
DECLARE @warehouseItemId1 VARCHAR(50), @warehouseItemId2 VARCHAR(50);

IF EXISTS (SELECT 1 FROM products WHERE product_id = 1)
BEGIN
    EXEC sp_InsertWarehouseItem @warehouseId1, 1, 100, @warehouseItemId1 OUTPUT;
    EXEC sp_InsertWarehouseItem @warehouseId2, 1, 50, @warehouseItemId2 OUTPUT;
END

PRINT 'Sample data inserted!';
PRINT 'Sample User IDs: ' + @userId1 + ', ' + @userId2 + ', ' + @userId3;
PRINT 'Sample Warehouse IDs: ' + @warehouseId1 + ', ' + @warehouseId2 + ', ' + @warehouseId3;
GO

-- ============================================
-- VERIFICATION QUERIES
-- ============================================

PRINT '';
PRINT '============================================';
PRINT 'VERIFICATION - Check Data';
PRINT '============================================';

PRINT 'Users:';
SELECT user_id, full_name, email, region_code FROM users;

PRINT '';
PRINT 'Warehouses:';
SELECT warehouse_id, warehouse_name, region_code FROM warehouses;

PRINT '';
PRINT 'Warehouse Items:';
SELECT warehouse_item_id, warehouse_id, product_id, stock FROM warehouse_items;

GO

PRINT '';
PRINT '============================================';
PRINT 'MIGRATION COMPLETED SUCCESSFULLY!';
PRINT '============================================';
PRINT '';
PRINT 'ID FORMAT:';
PRINT '  Users:           {REGION}-U-000001   (e.g., BDG-U-000001)';
PRINT '  Warehouses:      {REGION}-W-000001   (e.g., JKT-W-000001)';
PRINT '  Warehouse Items: {REGION}-WI-000001  (e.g., SBY-WI-000001)';
PRINT '  Cart Items:      {REGION}-CI-000001  (e.g., BDG-CI-000001)';
PRINT '  Orders:          {REGION}-O-000001   (e.g., JKT-O-000001)';
PRINT '  Order Items:     {REGION}-OI-000001  (e.g., BDG-OI-000001)';
PRINT '';
PRINT 'NEXT STEPS:';
PRINT '1. Update PHP Models to use stored procedures';
PRINT '2. Test insert operations';
PRINT '3. Verify no ID collisions between regions';
PRINT '';
