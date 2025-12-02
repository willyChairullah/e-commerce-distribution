<?php
$serverName = "127.0.0.1,1433";
$connectionInfo = array("Database" => "test", "UID" => "sa", "PWD" => "123", "TrustServerCertificate" => true);

$conn = sqlsrv_connect($serverName, $connectionInfo);

if ($conn) {
    echo "Koneksi berhasil!<br>";
} else {
    die(print_r(sqlsrv_errors(), true));
}

$sql = "SELECT * FROM users";
$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    print_r($row);
    echo "<br>";
}
