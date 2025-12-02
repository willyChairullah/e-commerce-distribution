# ============================================
# EXAMPLE CONFIG - LAPTOP 3 (DATABASE REGIONAL JAKARTA)
# ============================================
# Copy isi file ini ke config/app.php untuk deployment Jakarta

<?php

/**
 * KONFIGURASI UNTUK DATABASE REGIONAL JAKARTA
 * Database ini HANYA menyimpan data region JKT
 */

// Mode: regional (database regional)
define('REGION_MODE', 'regional');

// Region code: JKT (Jakarta)
define('REGION_CODE', 'JKT');

/**
 * Daftar Region yang Didukung
 */
define('AVAILABLE_REGIONS', [
    'JKT' => 'Jakarta',
    'BDG' => 'Bandung',
    'SBY' => 'Surabaya'
]);

/**
 * Admin Roles
 */
define('ROLE_SUPER_ADMIN', 1);      // Admin pusat (akses semua region)
define('ROLE_REGIONAL_ADMIN', 2);   // Admin regional (akses region tertentu)
define('ROLE_USER', 0);             // User biasa
