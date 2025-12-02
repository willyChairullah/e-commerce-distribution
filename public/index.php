<?php

/**
 * Main Entry Point - Public Index
 */

// Start session
session_start();

// Load application config (region mode)
require_once __DIR__ . '/../config/app.php';

// Load helpers
require_once __DIR__ . '/../helpers/csrf.php';
require_once __DIR__ . '/../helpers/util.php';

// Load routes
require_once __DIR__ . '/../routes.php';

// Get URL from query string
$url = isset($_GET['url']) ? $_GET['url'] : '';

// Route the request
route($url);
