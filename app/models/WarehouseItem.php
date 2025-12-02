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
        $sql = "INSERT INTO {$this->table} (warehouse_id, product_id, stock, created_at) 
                VALUES (?, ?, ?, GETDATE())";

        $params = array(
            $data['warehouse_id'],
            $data['product_id'],
            $data['stock']
        );

        $stmt = sqlsrv_query($this->conn, $sql, $params);
        return $stmt !== false;
    }

    public function getAll()
    {
        $sql = "SELECT wi.*, w.warehouse_name, p.name as product_name 
                FROM {$this->table} wi
                LEFT JOIN warehouses w ON wi.warehouse_id = w.warehouse_id
                LEFT JOIN products p ON wi.product_id = p.product_id
                ORDER BY w.warehouse_name, p.name";
        $stmt = sqlsrv_query($this->conn, $sql);

        $items = array();
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $items[] = $row;
        }

        return $items;
    }

    public function getByWarehouse($warehouseId)
    {
        $sql = "SELECT wi.*, p.name as product_name, p.price 
                FROM {$this->table} wi
                LEFT JOIN products p ON wi.product_id = p.product_id
                WHERE wi.warehouse_id = ?
                ORDER BY p.name";
        $stmt = sqlsrv_query($this->conn, $sql, array($warehouseId));

        $items = array();
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $items[] = $row;
        }

        return $items;
    }

    public function getByProduct($productId, $regionCode = null)
    {
        $sql = "SELECT wi.*, w.warehouse_name, w.region_code 
                FROM {$this->table} wi
                LEFT JOIN warehouses w ON wi.warehouse_id = w.warehouse_id
                WHERE wi.product_id = ? AND wi.stock > 0";
        
        $params = array($productId);
        
        // Filter by region if specified
        if ($regionCode !== null) {
            $sql .= " AND w.region_code = ?";
            $params[] = $regionCode;
        }
        
        $sql .= " ORDER BY w.warehouse_name";
        
        $stmt = sqlsrv_query($this->conn, $sql, $params);

        $items = array();
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $items[] = $row;
        }

        return $items;
    }

    public function findById($id)
    {
        $sql = "SELECT wi.*, w.warehouse_name, p.name as product_name 
                FROM {$this->table} wi
                LEFT JOIN warehouses w ON wi.warehouse_id = w.warehouse_id
                LEFT JOIN products p ON wi.product_id = p.product_id
                WHERE wi.warehouse_item_id = ?";
        $stmt = sqlsrv_query($this->conn, $sql, array($id));

        if ($stmt === false) {
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
        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        return $row['total'] ?? 0;
    }

    public function checkStock($warehouseItemId, $quantity)
    {
        $sql = "SELECT stock FROM {$this->table} WHERE warehouse_item_id = ?";
        $stmt = sqlsrv_query($this->conn, $sql, array($warehouseItemId));

        if ($stmt === false) {
            return false;
        }

        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        return $row && $row['stock'] >= $quantity;
    }
}
