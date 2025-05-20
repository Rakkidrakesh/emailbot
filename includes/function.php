<?php
/**
 * Helper functions for the chatbot
 */

if (!function_exists('get_user_id')) {
    function get_user_id() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); } // Ensure session is started
        if (!isset($_SESSION['user_id'])) {
             $_SESSION['user_id'] = 'user_' . bin2hex(random_bytes(8));
        }
        return $_SESSION['user_id'];
    }
}

if (!function_exists('sanitize_input')) {
    function sanitize_input($text) {
        return htmlspecialchars(trim($text), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

/**
 * Processes the user message.
 * Handles simple local greetings or calls an external backend API for other queries.
 * Returns an array with 'text_html' for display, and optionally 'page_link' and 'link_text'.
 * @param string $message The sanitized user message.
 * @return array The bot's response data.
 */
function process_message($message) {
    $lowerMessage = strtolower($message);
    $bot_response_text_from_local_handler = "";

    // // Local simple greetings
    // if (strpos($lowerMessage, 'hello') !== false || strpos($lowerMessage, 'hi') !== false || $lowerMessage === 'hey') {
    //     $bot_response_text_from_local_handler = "Hello there! How can I assist you today?";
    // }
    // else if (strpos($lowerMessage, 'bye') !== false || strpos($lowerMessage, 'goodbye') !== false) {
    //     $bot_response_text_from_local_handler = "Goodbye! Have a great day.";
    // }

    // Initialize the data structure to be returned
    $bot_response_data = [
        'text' => "Sorry, I couldn't process that request at the moment.", // Default raw text
        'page_link' => null,
        'link_text' => null
    ];

    if (!empty($bot_response_text_from_local_handler)) {
        $bot_response_data['text'] = $bot_response_text_from_local_handler;
    } else {
        $history_for_api = "";
        $payload_array = ['message' => $message, 'history' => $history_for_api];
        $payload = json_encode($payload_array);

        if ($payload === false) {
            error_log("Chatbot Error (functions.php): Failed to encode JSON payload for API. Data: " . print_r($payload_array, true));
            $bot_response_data['text'] = "Sorry, there was an internal error preparing your request.";
        } else {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, API_CHAT_URL); // From config.php
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Accept: application/json',
                'Content-Length: ' . strlen($payload)
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, API_TIMEOUT); // From config.php
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

            $api_response_body = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curl_error_num = curl_errno($ch);
            $curl_error_msg = curl_error($ch);
            curl_close($ch);

            error_log("--- Chatbot API Interaction (User: '{$message}') ---");
            error_log("API Request Payload: " . $payload);
            error_log("API HTTP Code Received: " . $http_code);
            error_log("RAW API Response Body: " . $api_response_body);
            if ($curl_error_num > 0) {
                error_log("cURL Error Number: {$curl_error_num}, Message: {$curl_error_msg}");
            }
            error_log("--- End Chatbot API Interaction ---");

            if ($curl_error_num > 0) {
                $bot_response_data['text'] = "Sorry, I couldn't reach the help desk service (Network Error: {$curl_error_msg}). Please try again later.";
            } elseif ($http_code >= 400) {
                $bot_response_data['text'] = "Sorry, the help desk service reported an error (Code: {$http_code}). Please try again.";
            } elseif ($api_response_body === false || trim($api_response_body) === '') {
                 $bot_response_data['text'] = "Sorry, I received an empty or invalid response from the help desk.";
            } else {
                $decoded_response = json_decode($api_response_body, true);

                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded_response)) {
                    if (isset($decoded_response['reply']) && is_string($decoded_response['reply'])) {
                        $bot_response_data['text'] = $decoded_response['reply'];
                    } elseif (isset($decoded_response['response']) && is_string($decoded_response['response'])) {
                        $bot_response_data['text'] = $decoded_response['response'];
                    } elseif (isset($decoded_response['message']) && is_string($decoded_response['message'])) {
                        $bot_response_data['text'] = $decoded_response['message'];
                    } elseif (isset($decoded_response['answer']) && is_string($decoded_response['answer'])) {
                        $bot_response_data['text'] = $decoded_response['answer'];
                    } else {
                         error_log("Chatbot API Warning (functions.php): JSON response received, but expected message key not found.");
                         $bot_response_data['text'] = "Sorry, I received an unexpected response format (key missing).";
                    }

                    if (isset($decoded_response['page_link']) && is_string($decoded_response['page_link'])) {
                        // Basic validation for URL or relative path
                        if (filter_var($decoded_response['page_link'], FILTER_VALIDATE_URL) || preg_match('/^[a-zA-Z0-9_\-\?\.=&%#\/~:]+$/', $decoded_response['page_link'])) {
                           $bot_response_data['page_link'] = $decoded_response['page_link'];
                        } else {
                           error_log("Chatbot API Warning (functions.php): Invalid page_link received: " . $decoded_response['page_link']);
                        }
                    }
                    if (isset($decoded_response['link_text']) && is_string($decoded_response['link_text'])) {
                        $bot_response_data['link_text'] = sanitize_input($decoded_response['link_text']);
                    }

                } else {
                     if (is_string($api_response_body) && !str_starts_with(trim($api_response_body), '<')) {
                        $bot_response_data['text'] = $api_response_body;
                     } else {
                        error_log("Chatbot API Warning (functions.php): Non-JSON response. JSON Decode Error: " . json_last_error_msg());
                        $bot_response_data['text'] = "Sorry, I received an unexpected response format (not JSON).";
                     }
                }
            }
        }
    }

    if (!isset($bot_response_data['text']) || !is_string($bot_response_data['text'])) {
         error_log("Chatbot Critical (functions.php): Final bot response text was not a string. Data: " . print_r($bot_response_data, true));
         $bot_response_data['text'] = "Sorry, an internal error occurred while processing the response.";
    }

    if (session_status() === PHP_SESSION_NONE) { session_start(); } // Ensure session for history
    if (!isset($_SESSION['chat_history'])) { $_SESSION['chat_history'] = []; }
    $_SESSION['chat_history'][] = ['sender' => 'user', 'message' => $message, 'timestamp' => time()];
    $_SESSION['chat_history'][] = ['sender' => 'bot', 'message' => $bot_response_data['text'], 'timestamp' => time()];
    if (defined('MAX_HISTORY') && count($_SESSION['chat_history']) > MAX_HISTORY) {
       $_SESSION['chat_history'] = array_slice($_SESSION['chat_history'], -MAX_HISTORY);
    }

    $bot_response_data['text_html'] = nl2br(htmlspecialchars($bot_response_data['text'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));

    return $bot_response_data;
}

function clear_chat_history() {
    if (session_status() === PHP_SESSION_NONE) { session_start(); } // Ensure session
    if (isset($_SESSION['chat_history'])) {
        unset($_SESSION['chat_history']);
    }
}
?>