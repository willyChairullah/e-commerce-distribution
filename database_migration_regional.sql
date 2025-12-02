-- ============================================
-- SQL MIGRATION - UPDATE REGIONAL SYSTEM
-- ============================================
-- Jalankan script ini untuk update database yang sudah ada

-- ============================================
-- 1. Update users yang belum punya region_code
-- ============================================

-- Set default region untuk user yang belum ada region
-- Sesuaikan dengan region deployment
UPDATE users 
SET region_code = 'JKT' 
WHERE region_code IS NULL OR region_code = '';

-- Atau bisa disesuaikan per kebutuhan:
-- UPDATE users SET region_code = 'BDG' WHERE ...
-- UPDATE users SET region_code = 'SBY' WHERE ...

-- ============================================
-- 2. Update warehouses yang belum punya region_code
-- ============================================

-- Set region untuk warehouse yang belum ada
UPDATE warehouses 
SET region_code = 'JKT' 
WHERE region_code IS NULL OR region_code = '';

-- Atau update manual per warehouse:
-- UPDATE warehouses SET region_code = 'BDG' WHERE warehouse_name LIKE '%Bandung%';
-- UPDATE warehouses SET region_code = 'SBY' WHERE warehouse_name LIKE '%Surabaya%';

-- ============================================
-- 3. Verifikasi Data
-- ============================================

-- Cek jumlah users per region
SELECT region_code, COUNT(*) as total_users 
FROM users 
GROUP BY region_code;

-- Cek jumlah warehouses per region
SELECT region_code, COUNT(*) as total_warehouses 
FROM warehouses 
GROUP BY region_code;

-- Cek jumlah orders per region (via users)
SELECT u.region_code, COUNT(o.order_id) as total_orders 
FROM orders o
LEFT JOIN users u ON o.user_id = u.user_id
GROUP BY u.region_code;

-- ============================================
-- 4. Sample Data untuk Testing (Optional)
-- ============================================

-- Insert sample warehouses untuk testing
-- Hanya jika belum ada data

-- Warehouse Jakarta
IF NOT EXISTS (SELECT 1 FROM warehouses WHERE warehouse_name = 'Gudang Jakarta Pusat')
BEGIN
    INSERT INTO warehouses (warehouse_name, region_code, address, created_at) 
    VALUES ('Gudang Jakarta Pusat', 'JKT', 'Jl. Sudirman No. 1, Jakarta', GETDATE());
END

-- Warehouse Bandung
IF NOT EXISTS (SELECT 1 FROM warehouses WHERE warehouse_name = 'Gudang Bandung Utara')
BEGIN
    INSERT INTO warehouses (warehouse_name, region_code, address, created_at) 
    VALUES ('Gudang Bandung Utara', 'BDG', 'Jl. Dago No. 10, Bandung', GETDATE());
END

-- Warehouse Surabaya
IF NOT EXISTS (SELECT 1 FROM warehouses WHERE warehouse_name = 'Gudang Surabaya Timur')
BEGIN
    INSERT INTO warehouses (warehouse_name, region_code, address, created_at) 
    VALUES ('Gudang Surabaya Timur', 'SBY', 'Jl. Ahmad Yani No. 5, Surabaya', GETDATE());
END

-- ============================================
-- 5. Index untuk Performance (Optional tapi Recommended)
-- ============================================

-- Index untuk filter region pada users
IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name = 'idx_users_region_code' AND object_id = OBJECT_ID('users'))
BEGIN
    CREATE INDEX idx_users_region_code ON users(region_code);
END

-- Index untuk filter region pada warehouses
IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name = 'idx_warehouses_region_code' AND object_id = OBJECT_ID('warehouses'))
BEGIN
    CREATE INDEX idx_warehouses_region_code ON warehouses(region_code);
END

-- ============================================
-- 6. Validasi Constraint (Optional)
-- ============================================

-- Pastikan region_code hanya berisi nilai yang valid
-- ALTER TABLE users ADD CONSTRAINT chk_users_region_code 
--     CHECK (region_code IN ('JKT', 'BDG', 'SBY'));

-- ALTER TABLE warehouses ADD CONSTRAINT chk_warehouses_region_code 
--     CHECK (region_code IN ('JKT', 'BDG', 'SBY'));

PRINT 'Migration completed successfully!';
PRINT 'Silakan cek hasil verifikasi di atas.';
