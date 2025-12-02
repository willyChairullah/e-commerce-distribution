<?php

/**
 * Order Model
 */

class Order
{
    private $conn;
    private $orderTable = "orders";
    private $orderItemTable = "order_items";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create($userId, $totalAmount)
    {
        // Call stored procedure sp_InsertOrder
        $sql = "{CALL sp_InsertOrder(?, ?, ?)}";
        
        $newOrderId = '';
        $params = array(
            array($userId, SQLSRV_PARAM_IN),
            array($totalAmount, SQLSRV_PARAM_IN),
            array(&$newOrderId, SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_STRING(SQLSRV_ENC_CHAR), SQLSRV_SQLTYPE_VARCHAR(50))
        );

        $stmt = sqlsrv_query($this->conn, $sql, $params);

        if ($stmt === false) {
            error_log("SQL Error in Order::create: " . print_r(sqlsrv_errors(), true));
            return false;
        }

        sqlsrv_free_stmt($stmt);
        return $newOrderId; // Return generated ID
    }

    public function addOrderItem($orderId, $warehouseItemId, $qty, $price)
    {
        // Call stored procedure sp_InsertOrderItem
        $sql = "{CALL sp_InsertOrderItem(?, ?, ?, ?, ?)}";
        
        $newOrderItemId = '';
        $params = array(
            array($orderId, SQLSRV_PARAM_IN),
            array($warehouseItemId, SQLSRV_PARAM_IN),
            array($qty, SQLSRV_PARAM_IN),
            array($price, SQLSRV_PARAM_IN),
            array(&$newOrderItemId, SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_STRING(SQLSRV_ENC_CHAR), SQLSRV_SQLTYPE_VARCHAR(50))
        );

        $stmt = sqlsrv_query($this->conn, $sql, $params);
        
        if ($stmt === false) {
            error_log("SQL Error in Order::addOrderItem: " . print_r(sqlsrv_errors(), true));
            return false;
        }
        
        sqlsrv_free_stmt($stmt);
        return $stmt !== false;
    }

    public function getAll($regionCode = null)
    {
        $sql = "SELECT o.*, u.full_name, u.email, u.region_code 
                FROM {$this->orderTable} o
                LEFT JOIN users u ON o.user_id = u.user_id";
        
        $params = array();
        if ($regionCode !== null) {
            $sql .= " WHERE u.region_code = ?";
            $params[] = $regionCode;
        }
        
        $sql .= " ORDER BY o.order_date DESC";
        $stmt = sqlsrv_query($this->conn, $sql, $params);

        if ($stmt === false) {
            error_log("SQL Error in Order::getAll: " . print_r(sqlsrv_errors(), true));
            return array();
        }

        $orders = array();
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $orders[] = $row;
        }

        return $orders;
    }

    public function getByUser($userId)
    {
        $sql = "SELECT * FROM {$this->orderTable} 
                WHERE user_id = ? 
                ORDER BY order_date DESC";
        $stmt = sqlsrv_query($this->conn, $sql, array($userId));

        if ($stmt === false) {
            error_log("SQL Error in Order::getByUser: " . print_r(sqlsrv_errors(), true));
            return array();
        }

        $orders = array();
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $orders[] = $row;
        }

        return $orders;
    }

    public function findById($orderId)
    {
        $sql = "SELECT o.*, u.full_name, u.email, u.region_code 
                FROM {$this->orderTable} o
                LEFT JOIN users u ON o.user_id = u.user_id
                WHERE o.order_id = ?";
        $stmt = sqlsrv_query($this->conn, $sql, array($orderId));

        if ($stmt === false) {
            error_log("SQL Error in Order::findById: " . print_r(sqlsrv_errors(), true));
            return null;
        }

        return sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    }

    public function getOrderItems($orderId)
    {
        $sql = "SELECT oi.*, p.product_name, p.photo_url, 
                       w.warehouse_name
                FROM {$this->orderItemTable} oi
                LEFT JOIN warehouse_items wi ON oi.warehouse_item_id = wi.warehouse_item_id
                LEFT JOIN products p ON wi.product_id = p.product_id
                LEFT JOIN warehouses w ON wi.warehouse_id = w.warehouse_id
                WHERE oi.order_id = ?";
        $stmt = sqlsrv_query($this->conn, $sql, array($orderId));

        if ($stmt === false) {
            error_log("SQL Error in Order::getOrderItems: " . print_r(sqlsrv_errors(), true));
            return array();
        }

        $items = array();
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $items[] = $row;
        }

        return $items;
    }

    public function getTotalOrders($regionCode = null)
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->orderTable} o";
        $params = array();
        
        if ($regionCode !== null) {
            $sql .= " LEFT JOIN users u ON o.user_id = u.user_id WHERE u.region_code = ?";
            $params[] = $regionCode;
        }
        
        $stmt = sqlsrv_query($this->conn, $sql, $params);
        
        if ($stmt === false) {
            error_log("SQL Error in Order::getTotalOrders: " . print_r(sqlsrv_errors(), true));
            return 0;
        }
        
        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        return $row ? $row['total'] : 0;
    }

    public function getMonthlyOrders()
    {
        $sql = "SELECT 
                    YEAR(order_date) as year, 
                    MONTH(order_date) as month, 
                    COUNT(*) as total_orders,
                    SUM(total_amount) as total_revenue
                FROM {$this->orderTable}
                GROUP BY YEAR(order_date), MONTH(order_date)
                ORDER BY year DESC, month DESC";
        $stmt = sqlsrv_query($this->conn, $sql);

        $reports = array();
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $reports[] = $row;
        }

        return $reports;
    }

    /**
     * Checkout from cart using stored procedure with cursor
     * This SP will:
     * 1. Calculate total from cart
     * 2. Create order
     * 3. Iterate cart items with cursor
     * 4. Insert order items (triggers stock reduction)
     * 5. Clear cart
     * 
     * @param string $userId User ID
     * @return string|false Order ID on success, false on failure
     */
    public function checkoutFromCart($userId)
    {
        // Call stored procedure sp_CheckoutFromCart_WithCursor
        $sql = "{CALL sp_CheckoutFromCart_WithCursor(?, ?)}";
        
        $newOrderId = '';
        $params = array(
            array($userId, SQLSRV_PARAM_IN),
            array(&$newOrderId, SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_STRING(SQLSRV_ENC_CHAR), SQLSRV_SQLTYPE_VARCHAR(50))
        );

        $stmt = sqlsrv_query($this->conn, $sql, $params);
        
        if ($stmt === false) {
            error_log("SQL Error in Order::checkoutFromCart: " . print_r(sqlsrv_errors(), true));
            return false;
        }
        
        sqlsrv_free_stmt($stmt);
        return $newOrderId; // Return generated order_id
    }
}
