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
        $sql = "INSERT INTO {$this->table} (full_name, email, password, region_code, is_admin, created_at) 
                VALUES (?, ?, ?, ?, ?, GETDATE())";

        $params = array(
            $data['full_name'],
            $data['email'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['region_code'],
            isset($data['is_admin']) ? $data['is_admin'] : 0
        );

        $stmt = sqlsrv_query($this->conn, $sql, $params);
        return $stmt !== false;
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
