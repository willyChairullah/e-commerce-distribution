<?php

/**
 * Test Database Connection
 * Run this file to test if SQL Server connection works
 */

// Load database config
require_once __DIR__ . '/config/database.php';

echo "Testing Database Connection...\n";
echo "================================\n\n";

try {
    $database = new Database();
    $conn = $database->connect();

    if ($conn) {
        echo "✓ SUCCESS: Connected to SQL Server!\n\n";

        // Test query
        $sql = "SELECT @@VERSION as version";
        $stmt = sqlsrv_query($conn, $sql);

        if ($stmt) {
            $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
            echo "SQL Server Version:\n";
            echo $row['version'] . "\n\n";
        }

        // Check if database exists
        $sql = "SELECT name FROM sys.databases WHERE name = 'warehouse_db'";
        $stmt = sqlsrv_query($conn, $sql);

        if ($stmt && sqlsrv_has_rows($stmt)) {
            echo "✓ Database 'warehouse_db' exists\n\n";

            // Check tables
            $sql = "SELECT TABLE_NAME FROM warehouse_db.INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE'";
            $stmt = sqlsrv_query($conn, $sql);

            echo "Tables found:\n";
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                echo "  - " . $row['TABLE_NAME'] . "\n";
            }
        } else {
            echo "✗ Database 'warehouse_db' not found\n";
            echo "  Please run database.sql script first\n";
        }

        $database->close();
        echo "\n✓ Connection closed successfully\n";
    } else {
        echo "✗ FAILED: Could not connect to SQL Server\n";
    }
} catch (Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
    echo "\nTroubleshooting:\n";
    echo "1. Check if SQL Server service is running\n";
    echo "2. Verify host, username, and password in config/database.php\n";
    echo "3. Check if PHP sqlsrv extension is installed: php -m | grep sqlsrv\n";
    echo "4. For Windows, download drivers from: https://docs.microsoft.com/en-us/sql/connect/php/download-drivers-php-sql-server\n";
}
