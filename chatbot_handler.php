<?php
declare(strict_types=1);

// --- Debugging Start (Optional: Remove or comment out when stable) ---
error_log("--- chatbot_handler.php Execution Start ---");
// ... (rest of the debug block from the version where we fixed the function.php include) ...
// For brevity, not repeating the full debug block here, but it should be the one that
// correctly identifies your 'function.php' (singular) or 'functions.php' (plural).
// Ensure the require_once lines below match your actual filename.
// --- Debugging End ---

// Adjust the filename here if your file is actually 'function.php' (singular)
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'config.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'function.php'; // Or functions.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');
    $response = [
        'status' => 'error',
        'message_html' => 'Invalid request data.',
        'page_link' => null,
        'link_text' => null
    ];
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (is_array($data) && isset($data['action'])) {
        try {
            switch ($data['action']) {
                case 'send_message':
                    if (isset($data['message']) && is_string($data['message']) && !empty(trim($data['message']))) {
                        $user_message = sanitize_input($data['message']);
                        $bot_reply_data_array = process_message($user_message);

                        $response = [
                            'status' => 'success',
                            'message_html' => $bot_reply_data_array['text_html'] ?? 'Error: No message content.',
                            'page_link'    => $bot_reply_data_array['page_link'] ?? null,
                            'link_text'    => $bot_reply_data_array['link_text'] ?? 'More Details'
                        ];
                    } else {
                        $response['message_html'] = 'Message cannot be empty or is invalid.';
                    }
                    break;
                case 'clear_history':
                    clear_chat_history();
                    $response = [
                        'status' => 'success',
                        'message_html' => 'Chat history cleared.' // No link needed for clear
                    ];
                    break;
                default:
                     $response['message_html'] = 'Unknown action specified.';
            }
        } catch (Exception $e) {
             error_log("Chatbot AJAX Error (chatbot_handler.php): " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
             $response['message_html'] = 'An internal error occurred.';
             http_response_code(500);
        }
    } else {
         $response['message_html'] = 'Invalid JSON input or missing action.';
         http_response_code(400);
    }
    echo json_encode($response);
    error_log("--- chatbot_handler.php Execution End (Sent JSON Response) ---");
    exit;

} else {
    header("HTTP/1.1 405 Method Not Allowed");
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['status' => 'error', 'message_html' => 'Only POST requests are accepted.']);
    error_log("--- chatbot_handler.php Execution End (Method Not Allowed) ---");
    exit;
}
?>