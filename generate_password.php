<?php

/**
 * Generate Password Hashes
 * Run this file once to generate proper password hashes for database
 */

echo "Password Hash Generator\n";
echo "======================\n\n";

$passwords = [
    'admin123',
    'user123'
];

foreach ($passwords as $password) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    echo "Password: {$password}\n";
    echo "Hash: {$hash}\n\n";
}

echo "\nCopy these hashes to your database.sql file\n";
echo "Update the INSERT INTO users statements with new hashes\n";
