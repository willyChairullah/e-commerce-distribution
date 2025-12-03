<?php

/**
 * Product Model
 */

class Product
{
    private $conn;
    private $table = "products";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} (product_name, price, photo_url, category_id, created_at) 
                VALUES (?, ?, ?, ?, GETDATE())";

        $params = array(
            $data['name'],
            $data['price'],
            $data['photo_url'],
            $data['category_id']
        );

        $stmt = sqlsrv_query($this->conn, $sql, $params);
        return $stmt !== false;
    }

    public function getAll()
    {
        $sql = "SELECT p.*, c.category_name 
                FROM {$this->table} p 
                LEFT JOIN categories c ON p.category_id = c.category_id 
                ORDER BY p.created_at DESC";
        $stmt = sqlsrv_query($this->conn, $sql);

        if ($stmt === false) {
            // Log error for debugging
            error_log("SQL Error in Product::getAll: " . print_r(sqlsrv_errors(), true));
            return array();
        }

        $products = array();
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $products[] = $row;
        }

        return $products;
    }

    public function getByCategory($categoryId)
    {
        $sql = "SELECT p.*, c.category_name 
                FROM {$this->table} p 
                LEFT JOIN categories c ON p.category_id = c.category_id 
                WHERE p.category_id = ?
                ORDER BY p.product_name";
        $stmt = sqlsrv_query($this->conn, $sql, array($categoryId));

        if ($stmt === false) {
            error_log("SQL Error in Product::getByCategory: " . print_r(sqlsrv_errors(), true));
            return array();
        }

        $products = array();
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $products[] = $row;
        }

        return $products;
    }

    public function findById($id)
    {
        $sql = "SELECT p.*, c.category_name 
                FROM {$this->table} p 
                LEFT JOIN categories c ON p.category_id = c.category_id 
                WHERE p.product_id = ?";
        $stmt = sqlsrv_query($this->conn, $sql, array($id));

        if ($stmt === false) {
            return null;
        }

        return sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    }

    public function update($id, $data)
    {
        $sql = "UPDATE {$this->table} 
                SET product_name = ?, price = ?, photo_url = ?, category_id = ? 
                WHERE product_id = ?";

        $params = array(
            $data['name'],
            $data['price'],
            $data['photo_url'],
            $data['category_id'],
            $id
        );

        $stmt = sqlsrv_query($this->conn, $sql, $params);
        return $stmt !== false;
    }

    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE product_id = ?";
        $stmt = sqlsrv_query($this->conn, $sql, array($id));
        return $stmt !== false;
    }

    public function getTotalProducts()
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $stmt = sqlsrv_query($this->conn, $sql);

        if ($stmt === false) {
            error_log("SQL Error in Product::getTotalProducts: " . print_r(sqlsrv_errors(), true));
            return 0;
        }

        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        return $row ? $row['total'] : 0;
    }

    /**
     * Get all products with stock availability info for specific region
     */
    public function getAllWithRegionStock($regionCode = null)
    {
        if ($regionCode === null) {
            // Central mode: return all products
            return $this->getAll();
        }

        // Regional mode: return products with stock in region
        $sql = "SELECT 
            p.product_id,
            p.product_name,
            p.price,
            p.photo_url,
            p.category_id,
            p.created_at,
            c.category_name,
            SUM(wi.stock) AS total_stock,
            COUNT(DISTINCT wi.warehouse_id) AS warehouse_count
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.category_id
        LEFT JOIN warehouse_items wi ON p.product_id = wi.product_id
        LEFT JOIN warehouses w ON wi.warehouse_id = w.warehouse_id
        LEFT JOIN regions r ON w.region_id = r.region_id 
            AND r.region_code = 'MDR'
        GROUP BY 
            p.product_id,
            p.product_name,
            p.price,
            p.photo_url,
            p.category_id,
            p.created_at,
            c.category_name
        HAVING 
            SUM(wi.stock) > 0 OR SUM(wi.stock) IS NULL
        ORDER BY 
            p.created_at DESC;";

        $stmt = sqlsrv_query($this->conn, $sql, array($regionCode));

        if ($stmt === false) {
            error_log("SQL Error in Product::getAllWithRegionStock: " . print_r(sqlsrv_errors(), true));
            return array();
        }

        $products = array();
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $products[] = $row;
        }

        return $products;
    }

    /**
     * Get products by category with region stock
     */
    public function getByCategoryWithRegionStock($categoryId, $regionCode = null)
    {
        if ($regionCode === null) {
            // Central mode
            return $this->getByCategory($categoryId);
        }

        // Regional mode: filter by region stock
        $sql = "SELECT 
                    p.product_id,
                    p.product_name,
                    p.price,
                    p.photo_url,
                    p.category_id,
                    p.created_at,
                    c.category_name,
                    SUM(wi.stock) AS total_stock
                FROM products p
                LEFT JOIN categories c 
                    ON p.category_id = c.category_id
                LEFT JOIN warehouse_items wi 
                    ON p.product_id = wi.product_id
                LEFT JOIN warehouses w 
                    ON wi.warehouse_id = w.warehouse_id
                LEFT JOIN regions r 
                    ON w.region_id = r.region_id 
                    AND r.region_code = ?
                WHERE p.category_id = ?
                GROUP BY 
                    p.product_id,
                    p.product_name,
                    p.price,
                    p.photo_url,
                    p.category_id,
                    p.created_at,
                    c.category_name
                HAVING SUM(wi.stock) > 0
                ORDER BY p.product_name;";

        $stmt = sqlsrv_query($this->conn, $sql, array($regionCode, $categoryId));

        if ($stmt === false) {
            error_log("SQL Error in Product::getByCategoryWithRegionStock: " . print_r(sqlsrv_errors(), true));
            return array();
        }

        $products = array();
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $products[] = $row;
        }

        return $products;
    }
}
