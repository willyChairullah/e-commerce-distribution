USE warehouse_3;
GO

-- Trigger
IF OBJECT_ID('dbo.trg_OrderItems_AfterInsert_UpdateStock', 'TR') IS NOT NULL
    DROP TRIGGER dbo.trg_OrderItems_AfterInsert_UpdateStock;
GO

-- Views
IF OBJECT_ID('dbo.v_CartDetails', 'V') IS NOT NULL
    DROP VIEW dbo.v_CartDetails;

IF OBJECT_ID('dbo.v_WarehouseStockDetail', 'V') IS NOT NULL
    DROP VIEW dbo.v_WarehouseStockDetail;

IF OBJECT_ID('dbo.v_UserOrdersSummary', 'V') IS NOT NULL
    DROP VIEW dbo.v_UserOrdersSummary;
GO

-- Stored Procedures
IF OBJECT_ID('dbo.sp_CheckoutFromCart_WithCursor', 'P') IS NOT NULL
    DROP PROCEDURE dbo.sp_CheckoutFromCart_WithCursor;

IF OBJECT_ID('dbo.sp_GetCartByUser', 'P') IS NOT NULL
    DROP PROCEDURE dbo.sp_GetCartByUser;

IF OBJECT_ID('dbo.sp_GetOrderDetail', 'P') IS NOT NULL
    DROP PROCEDURE dbo.sp_GetOrderDetail;

IF OBJECT_ID('dbo.sp_GetUserOrders', 'P') IS NOT NULL
    DROP PROCEDURE dbo.sp_GetUserOrders;

IF OBJECT_ID('dbo.sp_InsertOrderItem', 'P') IS NOT NULL
    DROP PROCEDURE dbo.sp_InsertOrderItem;

IF OBJECT_ID('dbo.sp_InsertOrder', 'P') IS NOT NULL
    DROP PROCEDURE dbo.sp_InsertOrder;

IF OBJECT_ID('dbo.sp_InsertCartItem', 'P') IS NOT NULL
    DROP PROCEDURE dbo.sp_InsertCartItem;

IF OBJECT_ID('dbo.sp_InsertWarehouseItem', 'P') IS NOT NULL
    DROP PROCEDURE dbo.sp_InsertWarehouseItem;

IF OBJECT_ID('dbo.sp_InsertWarehouse', 'P') IS NOT NULL
    DROP PROCEDURE dbo.sp_InsertWarehouse;

IF OBJECT_ID('dbo.sp_InsertUser', 'P') IS NOT NULL
    DROP PROCEDURE dbo.sp_InsertUser;
GO

-- Functions
IF OBJECT_ID('dbo.fn_GetRegionFromUserId', 'FN') IS NOT NULL
    DROP FUNCTION dbo.fn_GetRegionFromUserId;

IF OBJECT_ID('dbo.GetNextSequentialID', 'FN') IS NOT NULL
    DROP FUNCTION dbo.GetNextSequentialID;
GO

/* =====================================================
   1) FUNCTION – ID GENERATOR & BANTUAN REGION
   ===================================================== */

-- Fungsi ID generator: PREFIX-000001
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

-- Fungsi: ambil region dari user_id, contoh: JKT-U-000001 → JKT
CREATE FUNCTION dbo.fn_GetRegionFromUserId
(
    @user_id VARCHAR(50)
)
RETURNS NVARCHAR(10)
AS
BEGIN
    DECLARE @region NVARCHAR(10);

    SET @region = LEFT(@user_id, CHARINDEX('-', @user_id + '-') - 1);

    RETURN @region;
END;
GO

/* =====================================================
   2) STORED PROCEDURE – INSERT
   ===================================================== */

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
    
    -- Lookup region_id from region_code
    DECLARE @region_id INT;
    SELECT @region_id = region_id FROM dbo.regions WHERE region_code = @region_code;
    
    IF @region_id IS NULL
    BEGIN
        RAISERROR('Region code tidak valid: %s', 16, 1, @region_code);
        RETURN;
    END;
    
    DECLARE @prefix VARCHAR(20) = @region_code + '-U-';
    SET @new_user_id = dbo.GetNextSequentialID(@prefix, 'users');
    
    INSERT INTO dbo.users (user_id, full_name, email, password, region_id, is_admin, created_at)
    VALUES (@new_user_id, @full_name, @email, @password, @region_id, @is_admin, GETDATE());
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
    
    -- Lookup region_id from region_code
    DECLARE @region_id INT;
    SELECT @region_id = region_id FROM dbo.regions WHERE region_code = @region_code;
    
    IF @region_id IS NULL
    BEGIN
        RAISERROR('Region code tidak valid: %s', 16, 1, @region_code);
        RETURN;
    END;
    
    DECLARE @prefix VARCHAR(20) = @region_code + '-W-';
    SET @new_warehouse_id = dbo.GetNextSequentialID(@prefix, 'warehouses');
    
    INSERT INTO dbo.warehouses (warehouse_id, warehouse_name, region_id, address, created_at)
    VALUES (@new_warehouse_id, @warehouse_name, @region_id, @address, GETDATE());
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

/* =====================================================
   3) VIEWS
   ===================================================== */

-- v_UserOrdersSummary
CREATE VIEW dbo.v_UserOrdersSummary
AS
SELECT 
    u.user_id,
    u.full_name,
    u.email,
    r.region_code,
    r.region_name,
    COUNT(o.order_id)                AS total_orders,
    ISNULL(SUM(o.total_amount), 0)   AS total_spent
FROM dbo.users u
LEFT JOIN dbo.regions r
    ON u.region_id = r.region_id
LEFT JOIN dbo.orders o
    ON u.user_id = o.user_id
GROUP BY 
    u.user_id, u.full_name, u.email, r.region_code, r.region_name;
GO

-- v_WarehouseStockDetail
CREATE VIEW dbo.v_WarehouseStockDetail
AS
SELECT
    w.warehouse_id,
    w.warehouse_name,
    r.region_code,
    r.region_name,
    wi.warehouse_item_id,
    wi.product_id,
    p.product_name,
    p.price,
    wi.stock,
    wi.created_at AS added_at
FROM dbo.warehouses w
LEFT JOIN dbo.regions r
    ON w.region_id = r.region_id
JOIN dbo.warehouse_items wi
    ON w.warehouse_id = wi.warehouse_id
JOIN dbo.products p
    ON wi.product_id = p.product_id;
GO

-- v_CartDetails
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
   4) TRIGGER – UPDATE STOK SETELAH ORDER_ITEMS INSERT
   ===================================================== */

CREATE TRIGGER dbo.trg_OrderItems_AfterInsert_UpdateStock
ON dbo.order_items
AFTER INSERT
AS
BEGIN
    SET NOCOUNT ON;

    -- Cek stok cukup
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
   5) STORED PROCEDURE – SELECT / REPORT
   ===================================================== */

-- sp_GetUserOrders: list pesanan 1 user
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

-- sp_GetOrderDetail: header + detail
CREATE PROCEDURE dbo.sp_GetOrderDetail
    @order_id VARCHAR(50)
AS
BEGIN
    SET NOCOUNT ON;

    -- Header
    SELECT 
        o.order_id,
        o.user_id,
        u.full_name,
        o.order_date,
        o.total_amount
    FROM dbo.orders o
    JOIN dbo.users u ON o.user_id = u.user_id
    WHERE o.order_id = @order_id;

    -- Detail
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

-- sp_GetCartByUser
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
   6) STORED PROCEDURE DENGAN CURSOR – CHECKOUT
   ===================================================== */

CREATE PROCEDURE dbo.sp_CheckoutFromCart_WithCursor
    @user_id       VARCHAR(50),
    @new_order_id  VARCHAR(50) OUTPUT
AS
BEGIN
    SET NOCOUNT ON;

    DECLARE @total_amount DECIMAL(12,2) = 0;
    DECLARE @tmp_order_id VARCHAR(50);

    -- Hitung total dari cart
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

        -- Buat order
        EXEC dbo.sp_InsertOrder
            @user_id       = @user_id,
            @total_amount  = @total_amount,
            @new_order_id  = @tmp_order_id OUTPUT;

        -- Cursor untuk iterasi cart
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
