# ============================================
# EXAMPLE CONFIG - LAPTOP 3 (DATABASE REGIONAL SURABAYA)
# ============================================
# Copy isi file ini ke config/app.php untuk deployment Surabaya

<?php

/**
 * KONFIGURASI UNTUK DATABASE REGIONAL SURABAYA
 * Database ini HANYA menyimpan data region SBY
 */

// Mode: regional (database regional)
define('REGION_MODE', 'regional');

// Region code: SBY (Surabaya)
define('REGION_CODE', 'SBY');

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
