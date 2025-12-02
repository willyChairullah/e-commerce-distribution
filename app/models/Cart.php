<?php

/**
 * Cart Model
 */

class Cart
{
    private $conn;
    private $table = "cart_items";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function add($data)
    {
        // Check if item already exists in cart
        $existingSql = "SELECT * FROM {$this->table} 
                       WHERE user_id = ? AND warehouse_item_id = ?";
        $existingStmt = sqlsrv_query($this->conn, $existingSql, array($data['user_id'], $data['warehouse_item_id']));

        if ($existingStmt === false) {
            return false;
        }

        $existing = sqlsrv_fetch_array($existingStmt, SQLSRV_FETCH_ASSOC);

        if ($existing) {
            // Update quantity if exists
            $sql = "UPDATE {$this->table} SET qty = qty + ? WHERE cart_item_id = ?";
            $stmt = sqlsrv_query($this->conn, $sql, array($data['qty'], $existing['cart_item_id']));
        } else {
            // Insert new cart item
            $sql = "INSERT INTO {$this->table} (user_id, warehouse_item_id, qty, created_at) 
                    VALUES (?, ?, ?, GETDATE())";
            $stmt = sqlsrv_query($this->conn, $sql, array(
                $data['user_id'],
                $data['warehouse_item_id'],
                $data['qty']
            ));
        }

        return $stmt !== false;
    }

    public function getByUser($userId)
    {
        $sql = "SELECT ci.*, wi.stock, p.name as product_name, p.price, p.photo_url, 
                       w.warehouse_name, w.region_code
                FROM {$this->table} ci
                LEFT JOIN warehouse_items wi ON ci.warehouse_item_id = wi.warehouse_item_id
                LEFT JOIN products p ON wi.product_id = p.product_id
                LEFT JOIN warehouses w ON wi.warehouse_id = w.warehouse_id
                WHERE ci.user_id = ?
                ORDER BY ci.created_at DESC";
        $stmt = sqlsrv_query($this->conn, $sql, array($userId));

        $items = array();
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $items[] = $row;
        }

        return $items;
    }

    public function updateQuantity($cartItemId, $qty)
    {
        $sql = "UPDATE {$this->table} SET qty = ? WHERE cart_item_id = ?";
        $stmt = sqlsrv_query($this->conn, $sql, array($qty, $cartItemId));
        return $stmt !== false;
    }

    public function delete($cartItemId)
    {
        $sql = "DELETE FROM {$this->table} WHERE cart_item_id = ?";
        $stmt = sqlsrv_query($this->conn, $sql, array($cartItemId));
        return $stmt !== false;
    }

    public function clearUserCart($userId)
    {
        $sql = "DELETE FROM {$this->table} WHERE user_id = ?";
        $stmt = sqlsrv_query($this->conn, $sql, array($userId));
        return $stmt !== false;
    }

    public function getTotalItems($userId)
    {
        $sql = "SELECT SUM(qty) as total FROM {$this->table} WHERE user_id = ?";
        $stmt = sqlsrv_query($this->conn, $sql, array($userId));
        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        return $row['total'] ?? 0;
    }
}
