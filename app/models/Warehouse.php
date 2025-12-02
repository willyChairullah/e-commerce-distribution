<?php

/**
 * Warehouse Model
 */

class Warehouse
{
    private $conn;
    private $table = "warehouses";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} (warehouse_name, region_code, address, created_at) 
                VALUES (?, ?, ?, GETDATE())";

        $params = array(
            $data['warehouse_name'],
            $data['region_code'],
            $data['address']
        );

        $stmt = sqlsrv_query($this->conn, $sql, $params);
        return $stmt !== false;
    }

    public function getAll()
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY warehouse_name";
        $stmt = sqlsrv_query($this->conn, $sql);

        $warehouses = array();
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $warehouses[] = $row;
        }

        return $warehouses;
    }

    public function findById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE warehouse_id = ?";
        $stmt = sqlsrv_query($this->conn, $sql, array($id));

        if ($stmt === false) {
            return null;
        }

        return sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    }

    public function update($id, $data)
    {
        $sql = "UPDATE {$this->table} 
                SET warehouse_name = ?, region_code = ?, address = ? 
                WHERE warehouse_id = ?";

        $params = array(
            $data['warehouse_name'],
            $data['region_code'],
            $data['address'],
            $id
        );

        $stmt = sqlsrv_query($this->conn, $sql, $params);
        return $stmt !== false;
    }

    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE warehouse_id = ?";
        $stmt = sqlsrv_query($this->conn, $sql, array($id));
        return $stmt !== false;
    }

    public function getTotalWarehouses($regionCode = null)
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $params = array();
        
        if ($regionCode !== null) {
            $sql .= " WHERE region_code = ?";
            $params[] = $regionCode;
        }
        
        $stmt = sqlsrv_query($this->conn, $sql, $params);
        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        return $row['total'];
    }

    public function getAllByRegion($regionCode = null)
    {
        $sql = "SELECT * FROM {$this->table}";
        $params = array();
        
        if ($regionCode !== null) {
            $sql .= " WHERE region_code = ?";
            $params[] = $regionCode;
        }
        
        $sql .= " ORDER BY warehouse_name";
        $stmt = sqlsrv_query($this->conn, $sql, $params);

        $warehouses = array();
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $warehouses[] = $row;
        }

        return $warehouses;
    }
}
