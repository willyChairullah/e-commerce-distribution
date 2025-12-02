<?php
/**
 * Test URL Helper Functions
 */

// Start session (required by util.php)
session_start();

// Load helpers
require_once __DIR__ . '/helpers/util.php';

echo "<h1>Test URL Helper Functions</h1>";

echo "<h2>Server Information:</h2>";
echo "<pre>";
echo "HTTP_HOST: " . $_SERVER['HTTP_HOST'] . "\n";
echo "SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME'] . "\n";
echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "</pre>";

echo "<h2>Helper Functions:</h2>";
echo "<pre>";
echo "getBaseUrl(): " . getBaseUrl() . "\n";
echo "url(''): " . url('') . "\n";
echo "url('klien'): " . url('klien') . "\n";
echo "url('dashboard'): " . url('dashboard') . "\n";
echo "url('login'): " . url('login') . "\n";
echo "asset('css/style.css'): " . asset('css/style.css') . "\n";
echo "asset('css/client.css'): " . asset('css/client.css') . "\n";
echo "</pre>";

echo "<h2>Test Links:</h2>";
echo '<p><a href="' . url('') . '">Root (will redirect to klien)</a></p>';
echo '<p><a href="' . url('klien') . '">Klien Home</a></p>';
echo '<p><a href="' . url('dashboard') . '">Dashboard</a></p>';
echo '<p><a href="' . url('login') . '">Login</a></p>';
echo '<p><a href="' . url('register') . '">Register</a></p>';

echo "<h2>Expected Behavior:</h2>";
echo "<p>Semua link di atas harus mengarah ke path yang benar dengan /distribution/public/ sebagai base.</p>";
echo "<p>Contoh: url('klien') akan menghasilkan http://localhost/distribution/public/klien</p>";