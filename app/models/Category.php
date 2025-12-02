<?php

/**
 * Category Model
 */

class Category
{
    private $conn;
    private $table = "categories";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} (category_name, created_at) VALUES (?, GETDATE())";
        $stmt = sqlsrv_query($this->conn, $sql, array($data['category_name']));
        return $stmt !== false;
    }

    public function getAll()
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY category_name";
        $stmt = sqlsrv_query($this->conn, $sql);

        $categories = array();
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $categories[] = $row;
        }

        return $categories;
    }

    public function findById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE category_id = ?";
        $stmt = sqlsrv_query($this->conn, $sql, array($id));

        if ($stmt === false) {
            return null;
        }

        return sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    }

    public function update($id, $data)
    {
        $sql = "UPDATE {$this->table} SET category_name = ? WHERE category_id = ?";
        $stmt = sqlsrv_query($this->conn, $sql, array($data['category_name'], $id));
        return $stmt !== false;
    }

    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE category_id = ?";
        $stmt = sqlsrv_query($this->conn, $sql, array($id));
        return $stmt !== false;
    }
}
