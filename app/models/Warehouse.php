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
        // Call stored procedure sp_InsertWarehouse
        $sql = "{CALL sp_InsertWarehouse(?, ?, ?, ?)}";
        
        $newWarehouseId = '';
        $params = array(
            array($data['warehouse_name'], SQLSRV_PARAM_IN),
            array($data['region_code'], SQLSRV_PARAM_IN),
            array($data['address'], SQLSRV_PARAM_IN),
            array(&$newWarehouseId, SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_STRING(SQLSRV_ENC_CHAR), SQLSRV_SQLTYPE_VARCHAR(50))
        );

        $stmt = sqlsrv_query($this->conn, $sql, $params);
        
        if ($stmt === false) {
            error_log("SQL Error in Warehouse::create: " . print_r(sqlsrv_errors(), true));
            return false;
        }
        
        sqlsrv_free_stmt($stmt);
        return $newWarehouseId; // Return generated ID
    }

    public function getAll()
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY warehouse_name";
        $stmt = sqlsrv_query($this->conn, $sql);

        if ($stmt === false) {
            error_log("SQL Error in Warehouse::getAll: " . print_r(sqlsrv_errors(), true));
            return array();
        }

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
            error_log("SQL Error in Warehouse::findById: " . print_r(sqlsrv_errors(), true));
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
