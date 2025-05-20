<?php
// Ensure config.php is included. If index.php already includes it, this might be redundant
// but it's safe to include_once.
require_once __DIR__ . '/../includes/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8') : 'Global Feeder Shipping'; ?> - GFS</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo filemtime(__DIR__ . '/../assets/css/style.css'); // Cache busting ?>">
    <link rel="stylesheet" href="assets/css/chatbot_style.css?v=<?php echo filemtime(__DIR__ . '/../assets/css/chatbot_style.css'); // Cache busting ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" integrity="sha512-xh6O/CkQoPOWDdYTDqeRdPCVd1SpvCA9XXcUnZS2FmJNp1coAFzvtCN9BmamE+4aHK8yyUHUSCcJHgXloTyT2A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
<div class="site-container">
    <header class="site-header">
        <div class="header-container">
            <div class="logo">
                <a href="index.php">
                    <!-- Ensure logo.png is in assets/images/ -->
                    <img src="assets/images/logo.png" alt="Global Feeder Shipping Logo">
                </a>
            </div>
            <div class="contact-info-header">
                <div class="info-item">
                    <i class="fas fa-clock"></i>
                    <div>
                        <strong>DUBAI</strong><br>
                        MON-FRI 8:00 AM TO 5:00 PM<br>
                        <span class="closed-text">Saturday-Sunday CLOSED</span>
                    </div>
                </div>
                <div class="info-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <div>
                        BUILDING 04, BAY SQUARE, AL ASAYEL STREET<br>
                        BUSINESS BAY, DUBAI - UAE
                    </div>
                </div>
            </div>
        </div>
    </header>
    <?php include_once __DIR__ . '/navigation.php'; // Include navigation menu ?>
    <main id="middle" class="middle-content-area">
        <div class="middle_inner"> <!-- This div might be from original HTML, keep if styles depend on it -->
            <div class="content_wrap"> <!-- This div might be from original HTML -->
                <!-- Page specific content will be loaded here by index.php -->