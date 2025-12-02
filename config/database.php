<?php

/**
 * Database Configuration for SQL Server
 * EXAMPLE FILE - Copy to database.php and update with your credentials
 */

class Database
{
    // SQL Server Configuration
    private $host = "127.0.0.1,1433";              // SQL Server host (e.g., localhost, 127.0.0.1, or server name)
    private $database = "warehouse_3";       // Database name
    private $username = "sa";                 // SQL Server username
    private $password = "123";   // SQL Server password
    private $conn = null;

    /**
     * Connect to SQL Server database
     */
    public function connect()
    {
        try {
            // SQL Server connection info
            $connectionInfo = array(
                "Database" => $this->database,
                "UID" => $this->username,
                "PWD" => $this->password,
                "CharacterSet" => "UTF-8"
            );

            // Attempt connection
            $this->conn = sqlsrv_connect($this->host, $connectionInfo);

            if ($this->conn === false) {
                throw new Exception("Database connection failed: " . print_r(sqlsrv_errors(), true));
            }

            return $this->conn;
        } catch (Exception $e) {
            die("Connection error: " . $e->getMessage());
        }
    }

    /**
     * Get database connection
     */
    public function getConnection()
    {
        if ($this->conn === null) {
            $this->connect();
        }
        return $this->conn;
    }

    /**
     * Close database connection
     */
    public function close()
    {
        if ($this->conn !== null) {
            sqlsrv_close($this->conn);
            $this->conn = null;
        }
    }
}
