<?php
// Include required files
require_once '../config/database.php';
require_once '../config/fapshi.php';
require_once '../includes/functions.php';

// Create logs directory if it doesn't exist
if (!file_exists('../logs')) {
    mkdir('../logs', 0755, true);
}

// Log function
function log_message($message) {
    $log_file = '../logs/callback.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] $message" . PHP_EOL, FILE_APPEND);
}

// Set content type to JSON
header('Content-Type: application/json');

// Get the JSON payload
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Log the callback data
log_message("Callback received: " . $json);

// Verify the transaction
if (isset($data['transactionId'])) {
    $transaction_id = $data['transactionId'];
    log_message("Processing transaction ID: $transaction_id");
    
    try {
        // Verify the transaction with Fapshi
        $url = "https://api.fapshi.com/v1/payments/" . $transaction_id;
        
        // Initialize cURL session
        $ch = curl_init($url);
        
        // Set cURL options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . FAPSHI_API_KEY,
            'API-User: ' . FAPSHI_API_USER
        ));
        
        // Execute cURL request
        $response = curl_exec($ch);
        
        // Check for cURL errors
        if (curl_errno($ch)) {
            $curl_error = curl_error($ch);
            log_message("cURL error: " . $curl_error);
            throw new Exception('cURL error: ' . $curl_error);
        }
        
        // Close cURL session
        curl_close($ch);
        
        // Log the verification response
        log_message("Verification response: " . $response);
        
        // Decode the response
        $transaction_data = json_decode($response, true);
        
        // Check if transaction is successful
        if (isset($transaction_data['status']) && $transaction_data['status'] === 'success' && 
            isset($transaction_data['transaction']['status']) && $transaction_data['transaction']['status'] === 'successful') {
            
            log_message("Transaction verified as successful");
            
            // Get transaction details
            $amount = $transaction_data['transaction']['amount'];
            $payment_status = $transaction_data['transaction']['status'];
            
            // Start session to retrieve user information
            session_start();
            
            if (isset($_SESSION['pending_payment']) && $_SESSION['pending_payment']['transaction_id'] === $transaction_id) {
                $user_id = $_SESSION['user_id'];
                $product_id = $_SESSION['pending_payment']['product_id'];
                $payment_method = $_SESSION['pending_payment']['payment_method'];
                
                log_message("User ID: $user_id, Product ID: $product_id, Payment Method: $payment_method");
                
                // Save transaction in database
                $save_result = save_transaction($user_id, $product_id, $transaction_id, $amount, $payment_method, 'completed');
                
                if ($save_result) {
                    log_message("Transaction saved to database successfully");
                    
                    // Clear pending payment from session
                    unset($_SESSION['pending_payment']);
                    
                    // Return success response
                    echo json_encode(['status' => 'success']);
                    exit;
                } else {
                    log_message("Failed to save transaction to database");
                }
            } else {
                log_message("Session data not found or transaction ID mismatch");
            }
        } else {
            log_message("Transaction verification failed or transaction not successful");
        }
    } catch (Exception $e) {
        log_message("Exception: " . $e->getMessage());
    }
}

// Return error response if transaction verification fails
log_message("Returning error response");
echo json_encode(['status' => 'error']);
?>
