<?php 
include 'includes/header.php';
require_once 'config/fapshi.php';

// Gestion de la langue
$lang = $_SESSION['lang'] ?? 'fr';
$translations = include 'lang/' . $lang . '.php';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = (int)$_POST['product_id'];
    
    // Get product details
    $product_query = "SELECT * FROM products WHERE id = $product_id";
    $product_result = $conn->query($product_query);
    
    if ($product_result->num_rows === 0) {
        redirect('/');
    }
    
    $product = $product_result->fetch_assoc();
    
    // Check if user is logged in
    if (!is_logged_in()) {
        // Store product ID in session for after login
        $_SESSION['pending_purchase'] = $product_id;
        redirect('/login?redirect=payment');
    }
    
    // Check if user has already purchased this product
    if (has_purchased_product($_SESSION['user_id'], $product_id)) {
        redirect('/download?product=' . $product_id);
    }
} else if (isset($_GET['product'])) {
    // Handle direct access via GET parameter
    $product_id = (int)$_GET['product'];
    
    // Get product details
    $product_query = "SELECT * FROM products WHERE id = $product_id";
    $product_result = $conn->query($product_query);
    
    if ($product_result->num_rows === 0) {
        redirect('/');
    }
    
    $product = $product_result->fetch_assoc();
    
    // Check if user is logged in
    if (!is_logged_in()) {
        // Store product ID in session for after login
        $_SESSION['pending_purchase'] = $product_id;
        redirect('/login?redirect=payment');
    }
    
    // Check if user has already purchased this product
    if (has_purchased_product($_SESSION['user_id'], $product_id)) {
        redirect('/download?product=' . $product_id);
    }
} else {
    redirect('/');
}

// Get user details
$user_id = $_SESSION['user_id'];
$user_query = "SELECT * FROM users WHERE id = $user_id";
$user_result = $conn->query($user_query);
$user = $user_result->fetch_assoc();
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
            
            <!-- Product Summary -->
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-semibold mb-4"><?php echo $translations['order_summary']; ?></h2>
                <div class="flex items-center">
                    <?php if (!empty($product['image']) && file_exists($product['image'])): ?>
                        <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" class="w-20 h-20 object-cover rounded mr-4">
                    <?php else: ?>
                        <div class="w-20 h-20 bg-gray-200 rounded mr-4 flex items-center justify-center">
                            <i class="fas fa-image text-gray-400 text-2xl"></i>
                        </div>
                    <?php endif; ?>
                    <div>
                        <h3 class="font-semibold"><?php echo $product['name']; ?></h3>
                        <p class="text-gray-600 text-sm"><?php echo substr($product['description'], 0, 100); ?>...</p>
                        <p class="text-primary font-bold mt-1"><?php echo format_currency($product['price']); ?></p>
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
        
        // Préparer les données
        const paymentData = {
            product_id: <?php echo $product_id; ?>,
            email: email,
            phone: phone
        };
        
        // Send AJAX request to initiate payment
        fetch('/api/payment-process.php', {
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
                // Option 1: Rediriger vers notre page de succès
                if (data.redirect_url) {
                    window.location.href = data.redirect_url;
                } 
                // Option 2: Rediriger directement vers Fapshi si redirect_url n'est pas disponible
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

<?php include 'includes/footer.php'; ?>
