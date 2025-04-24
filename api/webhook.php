<?php
// This file handles Fapshi webhook notifications

// Include required files
require_once '../config/database.php';
require_once '../config/fapshi.php';
require_once '../includes/functions.php';

// Create logs directory if it doesn't exist
if (!file_exists('../logs')) {
    mkdir('../logs', 0755, true);
}

// Log function
function log_webhook($message) {
    $log_file = '../logs/webhook.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] $message" . PHP_EOL, FILE_APPEND);
}

// Get the raw POST data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Log the webhook data
log_webhook("Webhook received: " . $json);

// Process the webhook data
if (isset($data['transId']) && isset($data['status'])) {
    $transaction_id = $data['transId'];
    $status = $data['status'];
    $external_id = $data['externalId'] ?? '';
    
    log_webhook("Processing transaction: $transaction_id, Status: $status, External ID: $external_id");
    
    // If the payment was successful
    if ($status === 'SUCCESSFUL') {
        // Find the transaction in our database by transaction ID
        $query = "SELECT * FROM pending_transactions WHERE transaction_id = '$transaction_id'";
        $result = $conn->query($query);
        
        if ($result && $result->num_rows > 0) {
            $pending_transaction = $result->fetch_assoc();
            $user_id = $pending_transaction['user_id'];
            $product_id = $pending_transaction['product_id'];
            $amount = $pending_transaction['amount'];
            
            // Check if transaction already exists in completed transactions
            $check_query = "SELECT * FROM transactions WHERE transaction_id = '$transaction_id'";
            $check_result = $conn->query($check_query);
            
            if ($check_result && $check_result->num_rows === 0) {
                // Save as completed transaction
                $payment_method = $data['medium'] ?? 'fapshi';
                save_transaction($user_id, $product_id, $transaction_id, $amount, $payment_method, 'completed', $external_id);
                log_webhook("Transaction saved as completed: User ID: $user_id, Product ID: $product_id");
                
                // Delete from pending transactions
                $delete_query = "DELETE FROM pending_transactions WHERE transaction_id = '$transaction_id'";
                $conn->query($delete_query);
            } else {
                log_webhook("Transaction already exists in completed transactions");
            }
        } else {
            log_webhook("Could not find pending transaction with ID: $transaction_id");
        }
    } else {
        log_webhook("Payment not successful, status: $status");
    }
    
    // Return a 200 OK response to acknowledge receipt of the webhook
    http_response_code(200);
    echo json_encode(['status' => 'success']);
} else {
    log_webhook("Invalid webhook data");
    
    // Return a 400 Bad Request response
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid webhook data']);
}
?>
