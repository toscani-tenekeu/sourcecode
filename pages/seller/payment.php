<?php 
include dirname(__FILE__) . '/../../includes/header.php';
require_once dirname(__FILE__) . '/../../config/fapshi.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('/login?redirect=seller/payment');
}

// Check if there's a pending subscription
if (!isset($_SESSION['pending_subscription'])) {
    redirect('/account#seller');
}

$pending_subscription = $_SESSION['pending_subscription'];
$plan_id = $pending_subscription['plan_id'];
$amount = $pending_subscription['amount'];
$referral_code = $pending_subscription['referral_code'];

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

// Calculate discount
$discount_amount = 0;
if (!empty($referral_code)) {
    $discount_amount = calculate_discount($plan['price'], $referral_code);
}

// Calculate final amount
$final_amount = $plan['price'] - $discount_amount;

// Ensure minimum amount
if ($final_amount < 100) {
    $final_amount = 100;
}

// Gestion de la langue
$lang = $_SESSION['lang'] ?? 'fr';
$translations = include dirname(__FILE__) . '/../../lang/' . $lang . '.php';
?>

<div class="bg-gray-100 py-12">
    <div class="container mx-auto px-4">
        <h1 class="text-3xl font-bold mb-8 text-center"><?php echo $translations['complete_your_purchase']; ?></h1>
        
        <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md overflow-hidden">
            <!-- ToscaniSoft Branding -->
            <div class="bg-primary text-white p-4 flex items-center justify-between">
                <div class="flex items-center">
                    <span class="font-bold text-xl">ToscaniSoft</span>
                    <span class="ml-2 text-sm">| <?php echo $translations['secure_payment']; ?></span>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-lock mr-2"></i>
                    <span class="text-sm"><?php echo $translations['secured_by']; ?> Fapshi</span>
                </div>
            </div>
            
            <!-- Subscription Summary -->
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-semibold mb-4"><?php echo $translations['order_summary']; ?></h2>
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
                        <div class="mt-2">
                            <p class="flex justify-between">
                                <span>Base Price:</span>
                                <span><?php echo format_currency($plan['price']); ?></span>
                            </p>
                            <?php if ($discount_amount > 0): ?>
                            <p class="flex justify-between text-green-600">
                                <span>Referral Discount (5%):</span>
                                <span>-<?php echo format_currency($discount_amount); ?></span>
                            </p>
                            <?php endif; ?>
                            <p class="flex justify-between font-bold text-primary">
                                <span>Total:</span>
                                <span><?php echo format_currency($final_amount); ?></span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Payment Options -->
            <div class="p-6">
                <h2 class="text-xl font-semibold mb-4"><?php echo $translations['payment_information']; ?></h2>
                
                <div class="mb-6">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1"><?php echo $translations['email_address']; ?></label>
                    <input type="email" id="email" name="email" value="<?php echo $user['email']; ?>" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                    <p class="text-sm text-gray-500 mt-1"><?php echo $translations['email_confirmation']; ?></p>
                </div>
                
                <div class="mb-6">
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1"><?php echo $translations['phone_number']; ?></label>
                    <input type="text" id="phone" name="phone" value="<?php echo $user['phone']; ?>" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" placeholder="ex: 6XXXXXXXX">
                    <p class="text-sm text-gray-500 mt-1"><?php echo $translations['phone_for_payment']; ?></p>
                </div>
                
                <div class="mb-6">
                    <div class="flex items-center">
                        <input type="checkbox" id="terms" name="terms" class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                        <label for="terms" class="ml-2 block text-sm text-gray-700">
                            <?php echo $translations['agree_to']; ?> <a href="#" class="text-primary hover:underline"><?php echo $translations['terms_of_service']; ?></a> <?php echo $translations['and']; ?> <a href="#" class="text-primary hover:underline"><?php echo $translations['privacy_policy']; ?></a>
                        </label>
                    </div>
                </div>
                
                <div id="payment-error" class="mt-4 text-red-600 hidden"></div>
                <div id="payment-debug" class="mt-4 text-gray-600 text-sm hidden"></div>
                
                <div class="mt-8">
                    <button id="pay-button" class="w-full bg-primary text-white hover:bg-blue-800 py-3 px-6 rounded-md font-semibold inline-flex items-center justify-center">
                        <i class="fas fa-lock mr-2"></i> <?php echo $translations['pay_now']; ?>
                    </button>
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
        </div>
    </div>
</div>

<script>
    document.getElementById('pay-button').addEventListener('click', function() {
        const email = document.getElementById('email').value.trim();
        const phone = document.getElementById('phone').value.trim();
        const termsChecked = document.getElementById('terms').checked;
        const errorElement = document.getElementById('payment-error');
        const debugElement = document.getElementById('payment-debug');
        
        // Reset error message
        errorElement.classList.add('hidden');
        errorElement.textContent = '';
        
        // Validate terms
        if (!termsChecked) {
            errorElement.textContent = "<?php echo $translations['must_agree_terms']; ?>";
            errorElement.classList.remove('hidden');
            return;
        }
        
        // Validate phone number
        if (!phone) {
            errorElement.textContent = '<?php echo $translations['enter_phone']; ?>';
            errorElement.classList.remove('hidden');
            return;
        }
        
        // Validate phone number format (Cameroon mobile numbers)
        const phoneRegex = /^(6|7)\d{8}$/;
        if (!phoneRegex.test(phone)) {
            errorElement.textContent = '<?php echo $translations['valid_phone_format']; ?>';
            errorElement.classList.remove('hidden');
            return;
        }
        
        // Show loading state
        this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> <?php echo $translations['processing']; ?>';
        this.disabled = true;
        
        // Prepare data
        const paymentData = {
            plan_id: <?php echo $plan_id; ?>,
            email: email,
            phone: phone,
            amount: <?php echo $final_amount; ?>,
            referral_code: '<?php echo $referral_code; ?>'
        };
        
        // Send AJAX request to initiate payment
        fetch('/api/subscription-process.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(paymentData),
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Server responded with status: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Option 1: Redirect to our success page
                if (data.redirect_url) {
                    window.location.href = data.redirect_url;
                } 
                // Option 2: Redirect directly to Fapshi if redirect_url isn't available
                else if (data.payment_link) {
                    window.location.href = data.payment_link;
                }
                else {
                    throw new Error('<?php echo $translations['no_redirect_url']; ?>');
                }
            } else {
                // Show error message
                errorElement.textContent = data.message || '<?php echo $translations['payment_failed']; ?>';
                errorElement.classList.remove('hidden');
                
                // Log detailed error for debugging
                console.error('Payment error:', data);
                
                // Reset button
                this.innerHTML = '<i class="fas fa-lock mr-2"></i> <?php echo $translations['pay_now']; ?>';
                this.disabled = false;
            }
        })
        .catch(error => {
            // Show error message
            errorElement.textContent = '<?php echo $translations['error_occurred']; ?>: ' + error.message;
            errorElement.classList.remove('hidden');
            
            // Log error for debugging
            console.error('Error:', error);
            
            // Reset button
            this.innerHTML = '<i class="fas fa-lock mr-2"></i> <?php echo $translations['pay_now']; ?>';
            this.disabled = false;
        });
    });
</script>

<?php include dirname(__FILE__) . '/../../includes/footer.php'; ?>
