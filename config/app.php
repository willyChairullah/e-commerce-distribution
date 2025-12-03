<?php

define('REGION_MODE', 'regional');
define('REGION_CODE', 'MDR');

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
