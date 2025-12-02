# ============================================
# EXAMPLE CONFIG - LAPTOP 2 (DATABASE REGIONAL MADURA)
# ============================================
# Copy isi file ini ke config/app.php untuk Laptop 2

<?php

/**
 * KONFIGURASI UNTUK LAPTOP 2 - DATABASE REGIONAL MADURA
 * Database ini HANYA menyimpan data region MDR
 */

// Mode: regional (database regional)
define('REGION_MODE', 'regional');

// Region code: MDR (Madura)
// PENTING: Ubah ini sesuai region yang dikelola
// Pilihan: 'MDR', 'SBY'
define('REGION_CODE', 'MDR');

/**
 * Daftar Region yang Didukung
 * (Tetap sama di semua deployment)
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
