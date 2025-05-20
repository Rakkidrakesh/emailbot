<?php
declare(strict_types=1);

// --- Configuration and Setup ---
define('APP_BASE_PATH', rtrim(__DIR__, '/\\'));

require_once APP_BASE_PATH . '/includes/config.php';

// --- Debugging Output Function ---
function debug_log($message) {
    echo "<!-- DEBUG: " . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . " -->\n";
    error_log("CHATBOT_INDEX_DEBUG: " . $message);
}

debug_log("======================================================================");
debug_log("NEW REQUEST: " . ($_SERVER['REQUEST_URI'] ?? 'Unknown URI'));
debug_log("Script execution started in index.php.");
debug_log("APP_BASE_PATH defined as: " . APP_BASE_PATH);
debug_log("PHP's current working directory (getcwd()): " . getcwd());
debug_log("PHP Version: " . PHP_VERSION);
debug_log("Operating System: " . PHP_OS);


// --- Page Routing ---
$allowed_pages = ['about-us', 'service-routes', 'fleet', 'offices', 'career', 'contact-us', 'news', 'information'];
$current_page_param = $_GET['page'] ?? 'about-us';
$current_page = strtolower(trim($current_page_param));

debug_log("Requested page parameter via \$_GET['page']: " . $current_page_param);
debug_log("Normalized current page for processing: " . $current_page);

if (!in_array($current_page, $allowed_pages)) {
    debug_log("Requested page '{$current_page}' not in allowed_pages. Defaulting to 'about-us'.");
    $current_page = 'about-us';
}

$page_titles = [
    'about-us' => 'About Us',
    'service-routes' => 'Service Routes',
    'fleet' => 'Fleet',
    'offices' => 'Offices',
    'career' => 'Career',
    'contact-us' => 'Contact Us',
    'news' => 'News',
    'information' => 'Information'
];
$page_title = $page_titles[$current_page] ?? 'Welcome';
debug_log("Page title set to: " . $page_title);

// --- Include Header ---
$header_path = APP_BASE_PATH . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'header.php';
debug_log("Attempting to include header. Path expected: " . $header_path);
if (file_exists($header_path) && is_readable($header_path)) {
    include_once $header_path;
    debug_log("Header included successfully.");
} else {
    debug_log("CRITICAL ERROR: Header template file NOT FOUND or not readable at: " . $header_path);
    die("Critical error: Header template missing or not accessible. Please check the path and permissions. Path: " . htmlspecialchars($header_path));
}

// --- Construct Content File Path ---
$pages_content_dir_name = 'page_content'; // Ensure this matches your actual folder name
$pages_content_dir_path = APP_BASE_PATH . DIRECTORY_SEPARATOR . $pages_content_dir_name;
$content_file_name = $current_page . '.html';
$content_file_path = $pages_content_dir_path . DIRECTORY_SEPARATOR . $content_file_name;

debug_log("Expected pages content directory name: " . $pages_content_dir_name);
debug_log("Constructed pages content directory full path: " . $pages_content_dir_path);
debug_log("Expected content file name: " . $content_file_name);
debug_log("Constructed full content file path: " . $content_file_path);

// --- Detailed Directory and File Checks ---
debug_log("--- Starting File System Checks ---");

if (is_dir(APP_BASE_PATH)) {
    debug_log("Parent directory (APP_BASE_PATH) '" . APP_BASE_PATH . "' EXISTS.");
    debug_log("Scanning APP_BASE_PATH ('".APP_BASE_PATH."')...");
    $app_base_contents = @scandir(APP_BASE_PATH);
    if ($app_base_contents === false) {
        debug_log("Failed to scan APP_BASE_PATH. Check permissions for '".APP_BASE_PATH."'.");
    } else {
        debug_log("Contents of APP_BASE_PATH ('".APP_BASE_PATH."'): " . implode(', ', $app_base_contents));
        if (in_array($pages_content_dir_name, $app_base_contents, true)) {
            debug_log("'{$pages_content_dir_name}' IS FOUND by scandir within APP_BASE_PATH.");
        } else {
            debug_log("'{$pages_content_dir_name}' IS NOT FOUND by scandir within APP_BASE_PATH. Check exact name and case.");
        }
    }
} else {
    debug_log("Parent directory (APP_BASE_PATH) '" . APP_BASE_PATH . "' DOES NOT EXIST or is not a directory. This is a fundamental problem.");
}

if (is_dir($pages_content_dir_path)) {
    debug_log("Content directory '{$pages_content_dir_path}' EXISTS according to is_dir().");
    debug_log("Scanning content directory ('{$pages_content_dir_path}')...");
    $pages_content_contents = @scandir($pages_content_dir_path);
    if ($pages_content_contents === false) {
        debug_log("Failed to scan content directory '{$pages_content_dir_path}'. Check permissions.");
    } else {
        debug_log("Contents of '{$pages_content_dir_path}': " . implode(', ', $pages_content_contents));
        if (in_array($content_file_name, $pages_content_contents, true)) {
             debug_log("Expected content file '{$content_file_name}' IS FOUND by scandir within '{$pages_content_dir_path}'.");
        } else {
             debug_log("Expected content file '{$content_file_name}' IS NOT FOUND by scandir within '{$pages_content_dir_path}'. Check exact name and case.");
        }
    }
} else {
    debug_log("Content directory '{$pages_content_dir_path}' DOES NOT EXIST or is not a directory according to is_dir().");
}

if (file_exists($content_file_path)) {
    debug_log("Specific content file '{$content_file_path}' EXISTS according to file_exists().");
    if (is_readable($content_file_path)) {
        debug_log("Specific content file '{$content_file_path}' is also READABLE.");
    } else {
        debug_log("Specific content file '{$content_file_path}' EXISTS but is NOT READABLE. Check permissions.");
    }
} else {
    debug_log("Specific content file '{$content_file_path}' DOES NOT EXIST according to file_exists().");
}
debug_log("--- Finished File System Checks ---");


// --- Output Page Content ---
echo '<div class="page-content-wrapper">';

if (file_exists($content_file_path) && is_readable($content_file_path)) {
    debug_log("Attempting to readfile: " . $content_file_path);
    if ($current_page === 'service-routes') {
        $page_data_path = APP_BASE_PATH . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'page_data.php';
        debug_log("Current page is 'service-routes'. Attempting to include page data: " . $page_data_path);
        if(file_exists($page_data_path) && is_readable($page_data_path)) {
            include_once $page_data_path;
            debug_log("Page data file '{$page_data_path}' included.");
        } else {
            debug_log("WARNING: Page data file for service routes NOT FOUND or not readable: " . $page_data_path);
        }
    }

    $readfile_status = readfile($content_file_path);
    if ($readfile_status === false) {
        $last_error = error_get_last();
        $readfile_error_message = $last_error ? $last_error['message'] : 'Unknown readfile error';
        debug_log("readfile() FAILED for '{$content_file_path}'. Error: " . $readfile_error_message);
        echo '<p>Error: Could not read the content for this page. Please check server logs.</p>';
    } else {
        debug_log("readfile() successful for '{$content_file_path}'. Bytes read: " . $readfile_status);
    }
} else {
    echo '<p>Sorry, the content for the page "'.htmlspecialchars($current_page, ENT_QUOTES, 'UTF-8').'" could not be loaded.</p>';
    echo '<p>The system attempted to load the file from: <code>'.htmlspecialchars($content_file_path, ENT_QUOTES, 'UTF-8').'</code></p>';
    if (!is_dir($pages_content_dir_path)){
        echo '<p>The directory <code>'.htmlspecialchars($pages_content_dir_path, ENT_QUOTES, 'UTF-8').'</code> itself was not found or is not accessible.</p>';
    } elseif (!file_exists($content_file_path)) {
        echo '<p>The file <code>'.htmlspecialchars($content_file_name, ENT_QUOTES, 'UTF-8').'</code> was not found inside the content directory.</p>';
    } elseif (!is_readable($content_file_path)) {
        echo '<p>The file <code>'.htmlspecialchars($content_file_name, ENT_QUOTES, 'UTF-8').'</code> was found but is not readable by the web server.</p>';
    }
}
echo '</div>';

// --- Include Footer ---
$footer_path = APP_BASE_PATH . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'footer.php';
debug_log("Attempting to include footer. Path expected: " . $footer_path);
if (file_exists($footer_path) && is_readable($footer_path)) {
    include_once $footer_path;
    debug_log("Footer included successfully.");
} else {
    debug_log("CRITICAL ERROR: Footer template file NOT FOUND or not readable at: " . $footer_path);
    die("Critical error: Footer template missing or not accessible. Path: " . htmlspecialchars($footer_path));
}

debug_log("Script execution finished successfully for index.php.");
debug_log("======================================================================");
?>