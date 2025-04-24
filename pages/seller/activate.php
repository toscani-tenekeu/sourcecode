<?php 
include dirname(__FILE__) . '/../../includes/header.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('/login?redirect=seller/activate');
}

// Check if user is already an active seller
if (isset($_SESSION['is_seller_active']) && $_SESSION['is_seller_active'] == 1) {
    redirect('/account#seller');
}

// Get plan ID from URL
$plan_id = isset($_GET['plan']) ? (int)$_GET['plan'] : 0;

// If no plan ID, redirect to account
if (empty($plan_id)) {
    redirect('/account#seller');
}

// Get plan details
$plan_query = "SELECT * FROM subscription_plans WHERE id = $plan_id";
$plan_result = $conn->query($plan_query);

if ($plan_result->num_rows === 0) {
    redirect('/account#seller');
}

$plan = $plan_result->fetch_assoc();

// Get user details
$user_id = $_SESSION['user_id'];
$user_query = "SELECT * FROM users WHERE id = $user_id";
$user_result = $conn->query($user_query);
$user = $user_result->fetch_assoc();

// Process form submission
$error = '';
$referral_code = '';
$discount_amount = 0;
$final_amount = $plan['price'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $referral_code = isset($_POST['referral_code']) ? clean($_POST['referral_code']) : '';
    
    // Calculate discount if referral code is provided
    if (!empty($referral_code)) {
        $discount_amount = calculate_discount($plan['price'], $referral_code);
        $final_amount = $plan['price'] - $discount_amount;
    }
    
    // Store subscription info in session for payment processing
    $_SESSION['pending_subscription'] = [
        'plan_id' => $plan_id,
        'amount' => $final_amount,
        'referral_code' => $referral_code
    ];
    
    // Redirect to payment page
    redirect('/seller/payment');
}
?>

<div class="bg-gray-100 py-12">
    <div class="container mx-auto px-4">
        <h1 class="text-3xl font-bold mb-8 text-center">Activate Seller Account</h1>
        
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
            
            <!-- Plan Summary -->
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-semibold mb-4">Subscription Plan</h2>
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                        <?php if ($plan['duration_type'] === 'weekly'): ?>
                            <i class="fas fa-calendar-week text-primary"></i>
                        <?php elseif ($plan['duration_type'] === 'monthly'): ?>
                            <i class="fas fa-calendar-alt text-primary"></i>
                        <?php else: ?>
                            <i class="fas fa-infinity text-primary"></i>
                        <?php endif; ?>
                    </div>
                    <div>
                        <h3 class="font-semibold"><?php echo $plan['name']; ?></h3>
                        <p class="text-gray-600 text-sm"><?php echo $plan['description']; ?></p>
                        <p class="text-primary font-bold mt-1"><?php echo format_currency($plan['price']); ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Referral Code Form -->
            <div class="p-6">
                <form method="post">
                    <div class="mb-6">
                        <label for="referral_code" class="block text-sm font-medium text-gray-700 mb-1">Referral Code (Optional)</label>
                        <input type="text" id="referral_code" name="referral_code" value="<?php echo $referral_code; ?>" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                        <p class="text-sm text-gray-500 mt-1">Enter a referral code to get a 5% discount</p>
                    </div>
                    
                    <div class="mb-6">
                        <h3 class="font-semibold mb-2">What You'll Get</h3>
                        <ul class="space-y-2 text-gray-700">
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-2"></i>
                                <span>Ability to upload and sell your products</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-2"></i>
                                <span>Access to seller dashboard and analytics</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-2"></i>
                                <span>Priority customer support</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-2"></i>
                                <span>
                                    <?php if ($plan['duration_type'] === 'weekly'): ?>
                                        7 days of seller privileges
                                    <?php elseif ($plan['duration_type'] === 'monthly'): ?>
                                        30 days of seller privileges
                                    <?php else: ?>
                                        Lifetime seller privileges
                                    <?php endif; ?>
                                </span>
                            </li>
                        </ul>
                    </div>
                    
                    <div class="mt-8">
                        <button type="submit" class="w-full bg-primary text-white hover:bg-blue-800 py-3 px-6 rounded-md font-semibold inline-flex items-center justify-center">
                            <i class="fas fa-lock mr-2"></i> Proceed to Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include dirname(__FILE__) . '/../../includes/footer.php'; ?>
