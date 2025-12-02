<?php

/**
 * User Model
 */

class User
{
    private $conn;
    private $table = "users";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create($data)
    {
        // Call stored procedure sp_InsertUser
        $sql = "{CALL sp_InsertUser(?, ?, ?, ?, ?, ?)}";
        
        $newUserId = '';
        $params = array(
            array($data['full_name'], SQLSRV_PARAM_IN),
            array($data['email'], SQLSRV_PARAM_IN),
            array(password_hash($data['password'], PASSWORD_DEFAULT), SQLSRV_PARAM_IN),
            array($data['region_code'], SQLSRV_PARAM_IN),
            array(isset($data['is_admin']) ? $data['is_admin'] : 0, SQLSRV_PARAM_IN),
            array(&$newUserId, SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_STRING(SQLSRV_ENC_CHAR), SQLSRV_SQLTYPE_VARCHAR(50))
        );

        $stmt = sqlsrv_query($this->conn, $sql, $params);
        
        if ($stmt === false) {
            error_log("User::create - SQL Error: " . print_r(sqlsrv_errors(), true));
            return false;
        }
        
        sqlsrv_free_stmt($stmt);
        return $newUserId; // Return generated user_id
    }

    public function findByEmail($email)
    {
        $sql = "SELECT * FROM {$this->table} WHERE email = ?";
        $stmt = sqlsrv_query($this->conn, $sql, array($email));

        if ($stmt === false) {
            return null;
        }

        return sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    }

    public function findById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = ?";
        $stmt = sqlsrv_query($this->conn, $sql, array($id));

        if ($stmt === false) {
            return null;
        }

        return sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    }

    public function getAll()
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY created_at DESC";
        $stmt = sqlsrv_query($this->conn, $sql);

        $users = array();
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $users[] = $row;
        }

        return $users;
    }

    public function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }
}
