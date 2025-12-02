# ============================================
# EXAMPLE CONFIG - LAPTOP 1 (DATABASE PUSAT)
# ============================================
# Copy isi file ini ke config/app.php untuk Laptop 1

<?php

/**
 * KONFIGURASI UNTUK LAPTOP 1 - DATABASE PUSAT
 * Database ini menyimpan SEMUA data dari semua region
 */

// Mode: central (database pusat)
define('REGION_MODE', 'central');

// Region code: null (karena ini pusat, bukan regional spesifik)
define('REGION_CODE', null);

/**
 * Daftar Region yang Didukung
 */
define('AVAILABLE_REGIONS', [
    'MDR' => 'Madura',
    'SBY' => 'Surabaya'
]);

/**
 * Admin Roles
 */
define('ROLE_SUPER_ADMIN', 1);      // Admin pusat (akses semua region)
define('ROLE_REGIONAL_ADMIN', 2);   // Admin regional (akses region tertentu)
define('ROLE_USER', 0);             // User biasa
