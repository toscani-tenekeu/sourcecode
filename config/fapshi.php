<?php
// Fapshi API configuration
define('FAPSHI_API_KEY', 'FAK_6a45318c953f1169d80063ef550b2817');
define('FAPSHI_API_USER', '30e2b5b0-e729-4acc-842f-754590f98b00');
define('FAPSHI_BASE_URL', 'https://live.fapshi.com');

// Function to check payment status
function check_payment_status($transaction_id) {
    $url = FAPSHI_BASE_URL . "/payment-status/" . $transaction_id;
    
    // Initialize cURL session
    $ch = curl_init($url);
    
    // Set cURL options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Content-Type: application/json",
        "apiuser: " . FAPSHI_API_USER,
        "apikey: " . FAPSHI_API_KEY
    ));
    
    // Désactiver la vérification SSL pour le développement
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    
    // Execute cURL request
    $response = curl_exec($ch);
    
    // Check for cURL errors
    if (curl_errno($ch)) {
        error_log('Fapshi API cURL Error: ' . curl_error($ch));
        return array('status' => 'ERROR', 'message' => curl_error($ch));
    }
    
    curl_close($ch);
    
    // Decode the response
    $decoded = json_decode($response, true);
    
    // Check if JSON parsing failed
    if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
        error_log('JSON parsing error: ' . json_last_error_msg() . ' - Raw response: ' . $response);
        return array('status' => 'ERROR', 'message' => 'Invalid response format');
    }
    
    return $decoded;
}

// Function to get transaction history for a user
function get_user_transactions($user_id) {
    $url = FAPSHI_BASE_URL . "/transaction/" . $user_id;
    
    // Initialize cURL session
    $ch = curl_init($url);
    
    // Set cURL options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Content-Type: application/json",
        "apiuser: " . FAPSHI_API_USER,
        "apikey: " . FAPSHI_API_KEY
    ));
    
    // Désactiver la vérification SSL pour le développement
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    
    // Execute cURL request
    $response = curl_exec($ch);
    
    // Check for cURL errors
    if (curl_errno($ch)) {
        error_log('Fapshi API cURL Error: ' . curl_error($ch));
        return array();
    }
    
    curl_close($ch);
    
    // Decode the response
    $decoded = json_decode($response, true);
    
    // Check if JSON parsing failed
    if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
        error_log('JSON parsing error: ' . json_last_error_msg() . ' - Raw response: ' . $response);
        return array();
    }
    
    return $decoded;
}
?>
