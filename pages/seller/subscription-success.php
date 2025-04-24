<?php 
include dirname(__FILE__) . '/../../includes/header.php';
require_once dirname(__FILE__) . '/../../config/fapshi.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('/login');
}

// Get transaction ID from URL
$transaction_id = isset($_GET['transId']) ? clean($_GET['transId']) : '';

// If no transaction ID, redirect to account
if (empty($transaction_id)) {
    redirect('/account#seller');
}

// Check payment status
$payment_status = check_payment_status($transaction_id);

// Log the payment status
if (!file_exists('../logs')) {
    mkdir('../logs', 0755, true);
}
file_put_contents('../logs/subscription_status.log', date('Y-m-d H:i:s') . ': ' . json_encode($payment_status) . PHP_EOL, FILE_APPEND);

// Check if payment is successful
$payment_successful = false;
$subscription_saved = false;

if (isset($payment_status['status']) && $payment_status['status'] === 'SUCCESSFUL') {
    $payment_successful = true;
    
    // Get subscription details from session
    if (isset($_SESSION['pending_subscription'])) {
        $pending_subscription = $_SESSION['pending_subscription'];
        $plan_id = $pending_subscription['plan_id'];
        $amount = $pending_subscription['amount'];
        $referral_code = $pending_subscription['referral_code'];
        
        // Get plan details
        $plan_query = "SELECT * FROM subscription_plans WHERE id = $plan_id";
        $plan_result = $conn->query($plan_query);
        
        if ($plan_result->num_rows > 0) {
            $plan = $plan_result->fetch_assoc();
            
            // Calculate subscription end date based on plan type
            $start_date = date('Y-m-d H:i:s');
            $end_date = null;
            
            if ($plan['duration_type'] === 'weekly') {
                $end_date = date('Y-m-d H:i:s', strtotime('+7 days'));
            } elseif ($plan['duration_type'] === 'monthly') {
                $end_date = date('Y-m-d H:i:s', strtotime('+30 days'));
            }
            
            // Save subscription
            $user_id = $_SESSION['user_id'];
            $subscription_saved = save_subscription($user_id, $plan_id, $amount, $transaction_id, $start_date, $end_date);
            
            // Save referral if provided
            if (!empty($referral_code) && $subscription_saved) {
                save_referral($referral_code, $user_id);
            }
            
            // Clear pending subscription from session
            unset($_SESSION['pending_subscription']);
        }
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
                    <span class="ml-2 text-sm">| Seller Activation</span>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-lock mr-2"></i>
                    <span class="text-sm">Secured by Fapshi</span>
                </div>
            </div>
            
            <?php if ($payment_successful && $subscription_saved): ?>
                <div class="p-8 text-center">
                    <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-check text-green-500 text-3xl"></i>
                    </div>
                    <h1 class="text-3xl font-bold mb-4 text-green-600">Subscription Successful!</h1>
                    <p class="text-gray-600 mb-8">Your seller account has been activated successfully. You can now start selling your products.</p>
                    
                    <div class="mb-8 bg-gray-50 p-4 rounded-lg">
                        <h3 class="font-semibold mb-2">Subscription Details</h3>
                        <p class="mb-1"><strong>Plan:</strong> <?php echo $plan['name']; ?></p>
                        <p class="mb-1"><strong>Amount:</strong> <?php echo format_currency($amount); ?></p>
                        <p class="mb-1"><strong>Transaction ID:</strong> <?php echo $transaction_id; ?></p>
                        <p class="mb-1"><strong>Start Date:</strong> <?php echo date('M d, Y', strtotime($start_date)); ?></p>
                        <?php if ($end_date): ?>
                        <p><strong>End Date:</strong> <?php echo date('M d, Y', strtotime($end_date)); ?></p>
                        <?php else: ?>
                        <p><strong>End Date:</strong> <span class="text-green-600">Lifetime</span></p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="flex flex-col space-y-4">
                        <a href="/seller/add-product" class="bg-primary text-white hover:bg-blue-800 py-3 px-6 rounded-md font-semibold inline-flex items-center justify-center">
                            <i class="fas fa-plus mr-2"></i> Add Your First Product
                        </a>
                        <a href="/account#seller" class="bg-gray-200 text-gray-800 hover:bg-gray-300 py-3 px-6 rounded-md font-semibold inline-flex items-center justify-center">
                            <i class="fas fa-user mr-2"></i> Go to Seller Dashboard
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="p-8 text-center">
                    <div class="w-20 h-20 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-exclamation-triangle text-yellow-500 text-3xl"></i>
                    </div>
                    <h1 class="text-3xl font-bold mb-4 text-yellow-600">Payment Processing</h1>
                    <p class="text-gray-600 mb-8">Your payment is being processed. Please wait a moment or check your account dashboard.</p>
                    
                    <div class="mb-8 bg-gray-50 p-4 rounded-lg">
                        <h3 class="font-semibold mb-2">Transaction Details</h3>
                        <p class="mb-1"><strong>Transaction ID:</strong> <?php echo $transaction_id; ?></p>
                        <p class="mb-1">
                            <strong>Status:</strong> 
                            <span class="animate-pulse inline-block px-3 py-1 rounded-full <?php echo isset($payment_status['status']) ? ($payment_status['status'] === 'SUCCESSFUL' ? 'bg-green-100 text-green-800' : ($payment_status['status'] === 'FAILED' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800')) : 'bg-yellow-100 text-yellow-800'; ?>">
                                <?php echo isset($payment_status['status']) ? $payment_status['status'] : 'PENDING'; ?>
                            </span>
                        </p>
                    </div>
                    
                    <div class="flex flex-col space-y-4">
                        <button id="check-status" class="bg-primary text-white hover:bg-blue-800 py-3 px-6 rounded-md font-semibold inline-flex items-center justify-center">
                            <i class="fas fa-sync-alt mr-2"></i> Check Payment Status
                        </button>
                        <a href="/account#seller" class="bg-gray-200 text-gray-800 hover:bg-gray-300 py-3 px-6 rounded-md font-semibold inline-flex items-center justify-center">
                            <i class="fas fa-user mr-2"></i> Go to Account
                        </a>
                    </div>
                </div>
                
                <script>
                    document.getElementById('check-status').addEventListener('click', function() {
                        // Show loading state
                        this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Checking...';
                        this.disabled = true;
                        
                        // Reload the page to check the status again
                        window.location.reload();
                    });
                </script>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include dirname(__FILE__) . '/../../includes/footer.php'; ?>
