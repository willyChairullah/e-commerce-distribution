<?php

/**
 * Utility Helper Functions
 */

function redirect($url)
{
    // If URL is already absolute, use it as-is
    if (strpos($url, 'http') === 0) {
        header("Location: " . $url);
        exit();
    }
    
    // Otherwise, make it relative to base URL
    $baseUrl = getBaseUrl();
    $fullUrl = $baseUrl . '/' . ltrim($url, '/');
    header("Location: " . $fullUrl);
    exit();
}

function getCurrentUrl()
{
    return isset($_GET['url']) ? $_GET['url'] : '';
}

function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

function isAdmin()
{
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

function requireLogin()
{
    if (!isLoggedIn()) {
        redirect('/login');
    }
}

function requireAdmin()
{
    if (!isAdmin()) {
        redirect('/');
    }
}

function sanitize($data)
{
    return htmlspecialchars(strip_tags(trim($data)));
}

/**
 * ============================================
 * REGION HELPER FUNCTIONS
 * ============================================
 */

/**
 * Get current region mode (central or regional)
 */
function isCentralMode()
{
    require_once __DIR__ . '/../config/app.php';
    return REGION_MODE === 'central';
}

/**
 * Get current region code
 * Returns null for central mode, region code for regional mode
 */
function getCurrentRegion()
{
    require_once __DIR__ . '/../config/app.php';
    return REGION_CODE;
}

/**
 * Get region name
 */
function getRegionName($regionCode = null)
{
    require_once __DIR__ . '/../config/app.php';
    
    if ($regionCode === null) {
        $regionCode = getCurrentRegion();
    }
    
    if ($regionCode === null) {
        return 'Semua Region';
    }
    
    return AVAILABLE_REGIONS[$regionCode] ?? $regionCode;
}

/**
 * Check if user can access specific region
 */
function canAccessRegion($regionCode)
{
    // Central mode can access all regions
    if (isCentralMode()) {
        return true;
    }
    
    // Regional mode can only access its own region
    return getCurrentRegion() === $regionCode;
}

/**
 * Get region filter for SQL queries
 * Returns empty string for central mode, WHERE clause for regional mode
 */
function getRegionFilter($tableAlias = '')
{
    if (isCentralMode()) {
        return '';
    }
    
    $regionCode = getCurrentRegion();
    $prefix = $tableAlias ? $tableAlias . '.' : '';
    
    return " AND {$prefix}region_code = '{$regionCode}'";
}

/**
 * Check if user is super admin (can access all regions)
 */
function isSuperAdmin()
{
    require_once __DIR__ . '/../config/app.php';
    return isset($_SESSION['is_admin']) && 
           $_SESSION['is_admin'] == ROLE_SUPER_ADMIN && 
           isCentralMode();
}

/**
 * Check if user is regional admin
 */
function isRegionalAdmin()
{
    require_once __DIR__ . '/../config/app.php';
    return isset($_SESSION['is_admin']) && 
           $_SESSION['is_admin'] >= ROLE_REGIONAL_ADMIN && 
           !isCentralMode();
}

function formatCurrency($amount)
{
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

function formatDate($date)
{
    // Handle DateTime object
    if ($date instanceof DateTime) {
        return $date->format('d-m-Y H:i');
    }
    
    // Handle string date
    if (is_string($date)) {
        return date('d-m-Y H:i', strtotime($date));
    }
    
    // Handle null or empty
    if (empty($date)) {
        return '-';
    }
    
    // Try to convert to string first
    return date('d-m-Y H:i', strtotime((string)$date));
}

function uploadImage($file, $directory = 'products')
{
    $targetDir = __DIR__ . "/../public/assets/img/" . $directory . "/";

    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $imageFileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $newFileName = uniqid() . '.' . $imageFileType;
    $targetFile = $targetDir . $newFileName;

    // Check if image
    $check = getimagesize($file["tmp_name"]);
    if ($check === false) {
        return false;
    }

    // Check file size (max 5MB)
    if ($file["size"] > 5000000) {
        return false;
    }

    // Allow certain file formats
    if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
        return false;
    }

    if (move_uploaded_file($file["tmp_name"], $targetFile)) {
        return '/assets/img/' . $directory . '/' . $newFileName;
    }

    return false;
}

function deleteImage($path)
{
    $fullPath = __DIR__ . "/../public" . $path;
    if (file_exists($fullPath)) {
        unlink($fullPath);
    }
}

function getBaseUrl()
{
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    
    // Get the script directory path
    $scriptPath = $_SERVER['SCRIPT_NAME'];
    
    // For /distribution/public/index.php, we want /distribution/public
    $basePath = dirname($scriptPath);
    
    // Normalize path separators
    $basePath = str_replace('\\', '/', $basePath);
    
    // Ensure we have the correct base path
    if ($basePath === '/' || empty($basePath)) {
        $basePath = '';
    }
    
    return $protocol . $host . $basePath;
}

function url($path = '')
{
    $baseUrl = getBaseUrl();
    if (empty($path)) {
        return $baseUrl . '/';
    }
    return $baseUrl . '/' . ltrim($path, '/');
}

function asset($path = '')
{
    $baseUrl = getBaseUrl();
    return $baseUrl . '/assets/' . ltrim($path, '/');
}

/**
 * ============================================
 * DISTRIBUTED ID HELPER FUNCTIONS
 * ============================================
 */

/**
 * Generate distributed ID with region prefix
 * Format: {REGION}-{TYPE}-{SEQUENCE}
 * 
 * @param string $type Type code (U=User, W=Warehouse, WI=WarehouseItem, CI=CartItem, O=Order, OI=OrderItem)
 * @param string|null $regionCode Optional region code, defaults to current region
 * @return string Generated ID (e.g., "BDG-U-000001")
 */
function generateDistributedId($type, $regionCode = null)
{
    if ($regionCode === null) {
        $regionCode = getCurrentRegion();
    }
    
    // If no region (central mode accessing global), use 'CENTRAL'
    if ($regionCode === null) {
        $regionCode = 'CENTRAL';
    }
    
    $prefix = $regionCode . '-' . $type . '-';
    
    // Generate random 6-digit number to avoid database lookup
    // In production, you may want to use stored procedures
    $sequence = str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
    
    return $prefix . $sequence;
}

/**
 * Generate User ID
 */
function generateUserId($regionCode = null)
{
    return generateDistributedId('U', $regionCode);
}

/**
 * Generate Warehouse ID
 */
function generateWarehouseId($regionCode = null)
{
    return generateDistributedId('W', $regionCode);
}

/**
 * Generate Warehouse Item ID
 */
function generateWarehouseItemId($regionCode = null)
{
    return generateDistributedId('WI', $regionCode);
}

/**
 * Generate Cart Item ID
 */
function generateCartItemId($regionCode = null)
{
    return generateDistributedId('CI', $regionCode);
}

/**
 * Generate Order ID
 */
function generateOrderId($regionCode = null)
{
    return generateDistributedId('O', $regionCode);
}

/**
 * Generate Order Item ID
 */
function generateOrderItemId($regionCode = null)
{
    return generateDistributedId('OI', $regionCode);
}

/**
 * Extract region code from distributed ID
 * 
 * @param string $id Distributed ID (e.g., "BDG-U-000001")
 * @return string|null Region code or null if invalid format
 */
function extractRegionFromId($id)
{
    if (empty($id) || !is_string($id)) {
        return null;
    }
    
    $parts = explode('-', $id);
    if (count($parts) < 3) {
        return null;
    }
    
    return $parts[0];
}

/**
 * Validate distributed ID format
 * 
 * @param string $id ID to validate
 * @param string|null $expectedType Expected type code (U, W, WI, CI, O, OI) or null to skip type check
 * @return bool True if valid format
 */
function isValidDistributedId($id, $expectedType = null)
{
    if (empty($id) || !is_string($id)) {
        return false;
    }
    
    $parts = explode('-', $id);
    
    // Must have 3 parts: REGION-TYPE-SEQUENCE
    if (count($parts) !== 3) {
        return false;
    }
    
    list($region, $type, $sequence) = $parts;
    
    // Validate region (3-10 chars)
    if (strlen($region) < 2 || strlen($region) > 10) {
        return false;
    }
    
    // Validate type if specified
    if ($expectedType !== null && $type !== $expectedType) {
        return false;
    }
    
    // Validate sequence (must be numeric, 1-10 digits)
    if (!ctype_digit($sequence) || strlen($sequence) < 1 || strlen($sequence) > 10) {
        return false;
    }
    
    return true;
}
