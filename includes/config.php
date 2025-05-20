<?php
/**
 * Configuration settings
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

date_default_timezone_set('UTC'); // Example: 'America/New_York'

ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 1 : 0);

define('BOT_NAME', 'GFS Help Desk');
define('MAX_HISTORY', 50);

// Backend API Configuration
define('API_CHAT_URL', 'http://192.168.5.11:5000/chat'); // YOUR BACKEND API URL
define('API_TIMEOUT', 20);
?>