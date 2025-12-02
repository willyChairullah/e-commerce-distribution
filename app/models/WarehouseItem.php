<?php

/**
 * WarehouseItem Model - Inventory Stock Management
 */

class WarehouseItem
{
    private $conn;
    private $table = "warehouse_items";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create($data)
    {
        // Call stored procedure sp_InsertWarehouseItem
        $sql = "{CALL sp_InsertWarehouseItem(?, ?, ?, ?)}";
        
        $newWarehouseItemId = '';
        $params = array(
            array($data['warehouse_id'], SQLSRV_PARAM_IN),
            array($data['product_id'], SQLSRV_PARAM_IN),
            array($data['stock'], SQLSRV_PARAM_IN),
            array(&$newWarehouseItemId, SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_STRING(SQLSRV_ENC_CHAR), SQLSRV_SQLTYPE_VARCHAR(50))
        );

        $stmt = sqlsrv_query($this->conn, $sql, $params);
        
        if ($stmt === false) {
            error_log("SQL Error in WarehouseItem::create: " . print_r(sqlsrv_errors(), true));
            return false;
        }
        
        sqlsrv_free_stmt($stmt);
        return $newWarehouseItemId; // Return generated ID
    }

    public function getAll()
    {
        $sql = "SELECT wi.*, w.warehouse_name, p.product_name 
                FROM {$this->table} wi
                LEFT JOIN warehouses w ON wi.warehouse_id = w.warehouse_id
                LEFT JOIN products p ON wi.product_id = p.product_id
                ORDER BY w.warehouse_name, p.product_name";
        $stmt = sqlsrv_query($this->conn, $sql);

        if ($stmt === false) {
            error_log("SQL Error in WarehouseItem::getAll: " . print_r(sqlsrv_errors(), true));
            return array();
        }

        $items = array();
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $items[] = $row;
        }

        return $items;
    }

    public function getByWarehouse($warehouseId)
    {
        $sql = "SELECT wi.*, p.product_name, p.price 
                FROM {$this->table} wi
                LEFT JOIN products p ON wi.product_id = p.product_id
                WHERE wi.warehouse_id = ?
                ORDER BY p.product_name";
        $stmt = sqlsrv_query($this->conn, $sql, array($warehouseId));

        if ($stmt === false) {
            error_log("SQL Error in WarehouseItem::getByWarehouse: " . print_r(sqlsrv_errors(), true));
            return array();
        }

        $items = array();
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $items[] = $row;
        }

        return $items;
    }

    public function getByProduct($productId, $regionCode = null)
    {
        $sql = "SELECT wi.*, w.warehouse_name, r.region_code, r.region_name 
                FROM {$this->table} wi
                LEFT JOIN warehouses w ON wi.warehouse_id = w.warehouse_id
                LEFT JOIN regions r ON w.region_id = r.region_id
                WHERE wi.product_id = ? AND wi.stock > 0";
        
        $params = array($productId);
        
        // Filter by region if specified
        if ($regionCode !== null) {
            $sql .= " AND r.region_code = ?";
            $params[] = $regionCode;
        }
        
        $sql .= " ORDER BY w.warehouse_name";
        
        $stmt = sqlsrv_query($this->conn, $sql, $params);

        if ($stmt === false) {
            error_log("SQL Error in WarehouseItem::getByProduct: " . print_r(sqlsrv_errors(), true));
            return array();
        }

        $items = array();
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $items[] = $row;
        }

        return $items;
    }

    public function findById($id)
    {
        $sql = "SELECT wi.*, w.warehouse_name, p.product_name 
                FROM {$this->table} wi
                LEFT JOIN warehouses w ON wi.warehouse_id = w.warehouse_id
                LEFT JOIN products p ON wi.product_id = p.product_id
                WHERE wi.warehouse_item_id = ?";
        $stmt = sqlsrv_query($this->conn, $sql, array($id));

        if ($stmt === false) {
            error_log("SQL Error in WarehouseItem::findById: " . print_r(sqlsrv_errors(), true));
            return null;
        }

        return sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    }

    public function updateStock($id, $stock)
    {
        $sql = "UPDATE {$this->table} SET stock = ? WHERE warehouse_item_id = ?";
        $stmt = sqlsrv_query($this->conn, $sql, array($stock, $id));
        return $stmt !== false;
    }

    public function addStock($id, $quantity)
    {
        $sql = "UPDATE {$this->table} SET stock = stock + ? WHERE warehouse_item_id = ?";
        $stmt = sqlsrv_query($this->conn, $sql, array($quantity, $id));
        return $stmt !== false;
    }

    public function reduceStock($id, $quantity)
    {
        $sql = "UPDATE {$this->table} SET stock = stock - ? WHERE warehouse_item_id = ? AND stock >= ?";
        $stmt = sqlsrv_query($this->conn, $sql, array($quantity, $id, $quantity));
        return $stmt !== false;
    }

    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE warehouse_item_id = ?";
        $stmt = sqlsrv_query($this->conn, $sql, array($id));
        return $stmt !== false;
    }

    public function getTotalStock()
    {
        $sql = "SELECT SUM(stock) as total FROM {$this->table}";
        $stmt = sqlsrv_query($this->conn, $sql);
        
        if ($stmt === false) {
            error_log("SQL Error in WarehouseItem::getTotalStock: " . print_r(sqlsrv_errors(), true));
            return 0;
        }
        
        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        return $row['total'] ?? 0;
    }

    public function checkStock($warehouseItemId, $quantity)
    {
        $sql = "SELECT stock FROM {$this->table} WHERE warehouse_item_id = ?";
        $stmt = sqlsrv_query($this->conn, $sql, array($warehouseItemId));

        if ($stmt === false) {
            error_log("SQL Error in WarehouseItem::checkStock: " . print_r(sqlsrv_errors(), true));
            return false;
        }

        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        return $row && $row['stock'] >= $quantity;
    }
}
