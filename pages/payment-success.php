<?php 
include 'includes/header.php';
require_once 'config/fapshi.php';

// Gestion de la langue
$lang = $_SESSION['lang'] ?? 'fr';
$translations = include 'lang/' . $lang . '.php';

// Check if transaction ID is in the query string
$transaction_id = isset($_GET['transId']) ? clean($_GET['transId']) : '';

// If no transaction ID, check if it's in the session
if (empty($transaction_id) && isset($_SESSION['pending_payment']['transaction_id'])) {
    $transaction_id = $_SESSION['pending_payment']['transaction_id'];
}

// If still no transaction ID, redirect to home
if (empty($transaction_id)) {
    redirect('/');
}

// Get payment link from session or database
$payment_link = '';
if (isset($_SESSION['pending_payment']['payment_link'])) {
    $payment_link = $_SESSION['pending_payment']['payment_link'];
} else {
    // Try to get from database
    $query = "SELECT payment_link FROM pending_transactions WHERE transaction_id = '$transaction_id'";
    $result = $conn->query($query);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $payment_link = $row['payment_link'];
    }
}

// Check payment status
$payment_status = check_payment_status($transaction_id);

// Log the payment status
if (!file_exists('logs')) {
    mkdir('logs', 0755, true);
}
file_put_contents('logs/payment_status.log', date('Y-m-d H:i:s') . ': ' . json_encode($payment_status) . PHP_EOL, FILE_APPEND);

// Check if payment is successful
$payment_successful = false;
$product_id = 0;

if (isset($payment_status['status']) && $payment_status['status'] === 'SUCCESSFUL') {
    $payment_successful = true;
    
    // Get product ID from session or database
    if (isset($_SESSION['pending_payment']['product_id'])) {
        $product_id = $_SESSION['pending_payment']['product_id'];
    } else {
        // Try to get from database
        $query = "SELECT product_id FROM pending_transactions WHERE transaction_id = '$transaction_id'";
        $result = $conn->query($query);
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $product_id = $row['product_id'];
        }
    }
    
    if ($product_id > 0) {
        // Get product details
        $product_query = "SELECT * FROM products WHERE id = $product_id";
        $product_result = $conn->query($product_query);
        
        if ($product_result && $product_result->num_rows > 0) {
            $product = $product_result->fetch_assoc();
        }
        
        // Save transaction if not already saved
        $user_id = $_SESSION['user_id'];
        $amount = $payment_status['amount'];
        $payment_method = $payment_status['medium'] ?? 'fapshi';
        $external_id = $_SESSION['pending_payment']['external_id'] ?? '';
        
        // Check if transaction already exists
        $check_query = "SELECT * FROM transactions WHERE transaction_id = '$transaction_id'";
        $check_result = $conn->query($check_query);
        
        if ($check_result && $check_result->num_rows === 0) {
            save_transaction($user_id, $product_id, $transaction_id, $amount, $payment_method, 'completed', $external_id);
            
            // Delete from pending transactions
            $delete_query = "DELETE FROM pending_transactions WHERE transaction_id = '$transaction_id'";
            $conn->query($delete_query);
        }
        
        // Clear pending payment from session
        unset($_SESSION['pending_payment']);
    }
}
?>

<div class="bg-gray-100 py-12">
    <div class="container mx-auto px-4">
        <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md overflow-hidden">
            <!-- ToscaniSoft Branding -->
            <div class="bg-primary text-white p-4 flex items-center justify-between">
                <div class="flex items-center">
                    <span class="font-bold text-xl">ToscaniSoft</span>
                    <span class="ml-2 text-sm">| <?php echo $translations['payment_status']; ?></span>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-lock mr-2"></i>
                    <span class="text-sm"><?php echo $translations['secured_by']; ?> Fapshi</span>
                </div>
            </div>
            
            <?php if ($payment_successful): ?>
                <div class="p-8 text-center">
                    <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-check text-green-500 text-3xl"></i>
                    </div>
                    <h1 class="text-3xl font-bold mb-4 text-green-600"><?php echo $translations['payment_successful']; ?></h1>
                    <p class="text-gray-600 mb-8"><?php echo $translations['thank_you_purchase']; ?></p>
                    
                    <?php if (isset($product)): ?>
                        <div class="mb-8 bg-gray-50 p-4 rounded-lg">
                            <h3 class="font-semibold mb-2"><?php echo $translations['purchase_details']; ?></h3>
                            <p class="mb-1"><strong><?php echo $translations['product']; ?>:</strong> <?php echo $product['name']; ?></p>
                            <p class="mb-1"><strong><?php echo $translations['amount']; ?>:</strong> <?php echo format_currency($payment_status['amount']); ?></p>
                            <p class="mb-1"><strong><?php echo $translations['transaction_id']; ?>:</strong> <?php echo $transaction_id; ?></p>
                            <p><strong><?php echo $translations['payment_method']; ?>:</strong> <?php echo ucfirst($payment_status['medium'] ?? 'Fapshi'); ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <div class="flex flex-col space-y-4">
                        <a href="/download?product=<?php echo $product_id; ?>" class="bg-primary text-white hover:bg-blue-800 py-3 px-6 rounded-md font-semibold inline-flex items-center justify-center">
                            <i class="fas fa-download mr-2"></i> <?php echo $translations['download_purchase']; ?>
                        </a>
                        <a href="/account" class="bg-gray-200 text-gray-800 hover:bg-gray-300 py-3 px-6 rounded-md font-semibold inline-flex items-center justify-center">
                            <i class="fas fa-user mr-2"></i> <?php echo $translations['view_account']; ?>
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="p-8 text-center">
                    <div class="w-20 h-20 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-credit-card text-yellow-500 text-3xl"></i>
                    </div>
                    <h1 class="text-3xl font-bold mb-4 text-yellow-600"><?php echo $translations['complete_your_payment']; ?></h1>
                    <p class="text-gray-600 mb-8"><?php echo $translations['payment_pending']; ?></p>
                    
                    <div class="mb-8 bg-gray-50 p-4 rounded-lg">
                        <h3 class="font-semibold mb-2"><?php echo $translations['transaction_details']; ?></h3>
                        <p class="mb-1"><strong><?php echo $translations['transaction_id']; ?>:</strong> <?php echo $transaction_id; ?></p>
                        <p class="mb-1">
                            <strong><?php echo $translations['status']; ?>:</strong> 
                            <span class="animate-pulse inline-block px-3 py-1 rounded-full <?php echo isset($payment_status['status']) ? ($payment_status['status'] === 'SUCCESSFUL' ? 'bg-green-100 text-green-800' : ($payment_status['status'] === 'FAILED' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800')) : 'bg-yellow-100 text-yellow-800'; ?>">
                                <?php echo isset($payment_status['status']) ? $payment_status['status'] : 'PENDING'; ?>
                            </span>
                        </p>
                    </div>
                    
                    <div class="flex flex-col space-y-4">
                        <?php if (!empty($payment_link)): ?>
                            <a href="<?php echo $payment_link; ?>" target="_blank" class="bg-primary text-white hover:bg-blue-800 py-3 px-6 rounded-md font-semibold inline-flex items-center justify-center">
                                <i class="fas fa-credit-card mr-2"></i> <?php echo $translations['proceed_to_payment']; ?>
                            </a>
                        <?php endif; ?>
                        
                        <button id="check-status" class="bg-gray-200 text-gray-800 hover:bg-gray-300 py-3 px-6 rounded-md font-semibold inline-flex items-center justify-center">
                            <i class="fas fa-sync-alt mr-2"></i> <?php echo $translations['check_payment_status']; ?>
                        </button>
                        
                        <a href="/account" class="bg-gray-200 text-gray-800 hover:bg-gray-300 py-3 px-6 rounded-md font-semibold inline-flex items-center justify-center">
                            <i class="fas fa-user mr-2"></i> <?php echo $translations['view_account']; ?>
                        </a>
                    </div>
                    
                    <div class="mt-6 text-center">
                        <p class="text-sm text-gray-600 mb-3"><?php echo $translations['payment_methods']; ?></p>
                        <div class="flex justify-center space-x-6">
                            <div class="flex flex-col items-center">
                                <img src="/assets/images/mtn_momo_logo.webp" alt="MTN Mobile Money" class="h-12 mb-1">
                                <span class="text-xs text-gray-500">MTN MoMo</span>
                            </div>
                            <div class="flex flex-col items-center">
                                <img src="/assets/images/orange_money_logo.webp" alt="Orange Money" class="h-12 mb-1">
                                <span class="text-xs text-gray-500">Orange Money</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <script>
                    document.getElementById('check-status').addEventListener('click', function() {
                        // Show loading state
                        this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> <?php echo $translations['checking']; ?>';
                        this.disabled = true;
                        
                        // Reload the page to check the status again
                        window.location.reload();
                    });
                </script>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
