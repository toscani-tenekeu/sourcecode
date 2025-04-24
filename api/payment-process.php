<?php
// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Autoriser les requêtes AJAX depuis votre domaine
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Si c'est une requête OPTIONS (preflight), renvoyer juste les headers
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Start session
session_start();

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
    $log_file = '../logs/payment.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] $message" . PHP_EOL, FILE_APPEND);
}

// Log the request
log_message("Payment process started");

// Check if user is logged in
if (!is_logged_in()) {
    log_message("Error: User not logged in");
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Get JSON data
$json = file_get_contents('php://input');
log_message("Raw input: " . $json);

// Fallback to POST data if JSON is empty
if (empty($json)) {
    $data = $_POST;
    log_message("Using POST data: " . json_encode($data));
} else {
    $data = json_decode($json, true);
    
    // Check if JSON parsing failed
    if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
        log_message("JSON parsing error: " . json_last_error_msg());
        log_message("Raw input was: " . $json);
        echo json_encode(['success' => false, 'message' => 'Invalid JSON data: ' . json_last_error_msg()]);
        exit;
    }
}

// Log the request data
log_message("Processed request data: " . json_encode($data));

// Validate input data
if (!isset($data['product_id']) || !isset($data['email'])) {
    log_message("Error: Missing required fields");
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Get product details
$product_id = (int)$data['product_id'];
$product_query = "SELECT * FROM products WHERE id = $product_id";
$product_result = $conn->query($product_query);

if ($product_result->num_rows === 0) {
    log_message("Error: Product not found (ID: $product_id)");
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit;
}

$product = $product_result->fetch_assoc();

// Get user details
$user_id = $_SESSION['user_id'];
$user_query = "SELECT * FROM users WHERE id = $user_id";
$user_result = $conn->query($user_query);

if ($user_result->num_rows === 0) {
    log_message("Error: User not found (ID: $user_id)");
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit;
}

$user = $user_result->fetch_assoc();

// Prepare data for Fapshi API
$amount = (int)$product['price'];
$email = $data['email'];

// Make sure amount is at least 100 XAF (Fapshi minimum)
if ($amount < 100) {
    $amount = 100;
}

// Generate a unique external ID based on timestamp
$external_id = date('mdHis') . rand(100, 999);

// Prepare the message for the payment
$message = "Purchase of " . $product['name'] . " from SourceCode";

// Prepare the redirect URL
$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
$redirect_url = $base_url . "/payment/success";

log_message("Preparing payment: Amount: $amount XAF, Email: $email, External ID: $external_id");

try {
    // Initialize cURL session
    $ch = curl_init(FAPSHI_BASE_URL . "/initiate-pay");
    
    // Prepare request data
    $request_data = array(
        "amount" => $amount,
        "email" => $email,
        "redirectUrl" => $redirect_url,
        "userId" => (string)$user_id,
        "externalId" => $external_id,
        "message" => $message
    );
    
    log_message("Request data for Fapshi: " . json_encode($request_data));
    
    // Set cURL options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Content-Type: application/json",
        "apiuser: " . FAPSHI_API_USER,
        "apikey: " . FAPSHI_API_KEY
    ));
    
    // IMPORTANT: Désactiver la vérification SSL pour le développement
    // NE PAS UTILISER EN PRODUCTION SANS CONFIGURATION APPROPRIÉE
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    
    // Execute cURL request
    $response_json = curl_exec($ch);
    
    // Check for cURL errors
    if (curl_errno($ch)) {
        $curl_error = curl_error($ch);
        log_message("cURL error: " . $curl_error);
        echo json_encode(['success' => false, 'message' => 'Payment service error: ' . $curl_error]);
        exit;
    }
    
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    log_message("HTTP response code: " . $http_code);
    
    curl_close($ch);
    
    // Log the raw response
    log_message("Fapshi API raw response: " . $response_json);
    
    // Decode the response
    $response = json_decode($response_json, true);
    
    // Check if JSON parsing failed
    if ($response === null && json_last_error() !== JSON_ERROR_NONE) {
        log_message("JSON parsing error in response: " . json_last_error_msg());
        log_message("Raw response was: " . $response_json);
        echo json_encode(['success' => false, 'message' => 'Invalid response from payment service']);
        exit;
    }
    
    // Check if the request was successful
    if (isset($response['message']) && $response['message'] === 'Request successful') {
        // Store payment information in session
        $_SESSION['pending_payment'] = [
            'product_id' => $product_id,
            'amount' => $amount,
            'transaction_id' => $response['transId'],
            'external_id' => $external_id,
            'payment_method' => 'fapshi',
            'payment_link' => $response['link']
        ];
        
        // Check if pending_transactions table exists
        $table_check = $conn->query("SHOW TABLES LIKE 'pending_transactions'");
        if ($table_check->num_rows > 0) {
            // Save the pending transaction in the database
            $payment_link = $conn->real_escape_string($response['link']);
            $transaction_id = $conn->real_escape_string($response['transId']);
            
            $query = "INSERT INTO pending_transactions (user_id, product_id, transaction_id, external_id, amount, payment_link, created_at) 
                      VALUES ($user_id, $product_id, '$transaction_id', '$external_id', $amount, '$payment_link', NOW())";
            
            if (!$conn->query($query)) {
                log_message("Database error: " . $conn->error);
            }
        } else {
            log_message("Warning: pending_transactions table does not exist");
        }
        
        log_message("Payment initiated successfully. Transaction ID: " . $response['transId']);
        
        // Return success response with redirect URL to our payment page
        echo json_encode([
            'success' => true,
            'redirect_url' => $redirect_url . '?transId=' . $response['transId'],
            'payment_link' => $response['link'] // Inclure le lien de paiement directement
        ]);
    } else {
        // Log the error
        log_message("Payment initiation failed: " . ($response['message'] ?? 'Unknown error'));
        
        // Return error response
        echo json_encode([
            'success' => false,
            'message' => isset($response['message']) ? $response['message'] : 'Payment initiation failed',
            'details' => $response
        ]);
    }
} catch (Exception $e) {
    // Log the exception
    log_message("Exception: " . $e->getMessage());
    
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
}
?>
