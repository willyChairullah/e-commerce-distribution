-- ============================================
-- SQL MIGRATION - Add Missing Columns
-- Untuk menambahkan kolom tanpa DROP data
-- ============================================

USE warehouse_db;
GO

PRINT 'Adding categories table if not exists...';
GO

-- Create categories table if not exists
IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'categories')
BEGIN
    CREATE TABLE dbo.categories (
        category_id   INT IDENTITY(1,1) PRIMARY KEY,
        category_name NVARCHAR(100) NOT NULL,
        created_at    DATETIME DEFAULT GETDATE()
    );
    
    PRINT 'Categories table created!';
    
    -- Insert default categories
    INSERT INTO dbo.categories (category_name)
    VALUES 
        ('Elektronik'),
        ('Pakaian'),
        ('Makanan'),
        ('Minuman'),
        ('Peralatan Rumah Tangga');
    
    PRINT 'Default categories inserted!';
END
ELSE
BEGIN
    PRINT 'Categories table already exists.';
END
GO

-- Add photo_url column to products if not exists
IF NOT EXISTS (SELECT * FROM sys.columns 
               WHERE object_id = OBJECT_ID('dbo.products') 
               AND name = 'photo_url')
BEGIN
    PRINT 'Adding photo_url column to products...';
    ALTER TABLE dbo.products 
    ADD photo_url NVARCHAR(255) DEFAULT '/assets/img/products/default.svg';
    
    PRINT 'photo_url column added!';
END
ELSE
BEGIN
    PRINT 'photo_url column already exists.';
END
GO

-- Add category_id column to products if not exists
IF NOT EXISTS (SELECT * FROM sys.columns 
               WHERE object_id = OBJECT_ID('dbo.products') 
               AND name = 'category_id')
BEGIN
    PRINT 'Adding category_id column to products...';
    ALTER TABLE dbo.products 
    ADD category_id INT NULL;
    
    -- Add foreign key constraint
    ALTER TABLE dbo.products
    ADD CONSTRAINT FK_products_category
        FOREIGN KEY (category_id) REFERENCES dbo.categories(category_id) ON DELETE SET NULL;
    
    PRINT 'category_id column added with foreign key!';
END
ELSE
BEGIN
    PRINT 'category_id column already exists.';
END
GO

-- Create index on category_id if not exists
IF NOT EXISTS (SELECT * FROM sys.indexes 
               WHERE name = 'idx_products_category' 
               AND object_id = OBJECT_ID('dbo.products'))
BEGIN
    PRINT 'Creating index on category_id...';
    CREATE INDEX idx_products_category ON dbo.products(category_id);
    PRINT 'Index created!';
END
ELSE
BEGIN
    PRINT 'Index idx_products_category already exists.';
END
GO

-- Verify changes
PRINT '';
PRINT '============================================';
PRINT 'VERIFICATION';
PRINT '============================================';

PRINT 'Categories:';
SELECT category_id, category_name FROM dbo.categories;

PRINT '';
PRINT 'Products (showing new columns):';
SELECT product_id, product_name, price, category_id, photo_url FROM dbo.products;

PRINT '';
PRINT '============================================';
PRINT 'MIGRATION COMPLETED!';
PRINT '============================================';
GO
