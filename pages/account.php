<?php 
include 'includes/header.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('/login?redirect=account');
}

// Get user details
$user_id = $_SESSION['user_id'];
$user_query = "SELECT * FROM users WHERE id = $user_id";
$user_result = $conn->query($user_query);

if ($user_result->num_rows === 0) {
    // User not found, logout and redirect
    session_destroy();
    redirect('/login');
}

$user = $user_result->fetch_assoc();

// Update session with user data
$_SESSION['user_type'] = $user['user_type'];
$_SESSION['referral_code'] = $user['referral_code'];
$_SESSION['is_seller_active'] = $user['is_seller_active'];

// Get user purchases
$purchases = get_user_purchases($user_id);

// Get user subscription if they are a seller
$subscription = null;
if ($user['is_seller_active']) {
    $subscription = get_user_subscription($user_id);
}

// Get seller products if they are a seller
$seller_products = [];
if ($user['is_seller_active']) {
    $seller_products = get_seller_products($user_id);
}
?>

<div class="bg-gray-100 py-12">
    <div class="container mx-auto px-4">
        <h1 class="text-3xl font-bold mb-8">My Account</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <!-- Sidebar -->
            <div class="md:col-span-1">
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-center">
                            <div class="bg-blue-100 rounded-full p-3 mr-4">
                                <i class="fas fa-user text-primary text-xl"></i>
                            </div>
                            <div>
                                <h2 class="font-semibold"><?php echo $user['full_name']; ?></h2>
                                <p class="text-gray-600 text-sm"><?php echo $user['email']; ?></p>
                                <p class="text-gray-600 text-sm mt-1">
                                    <span class="inline-block px-2 py-1 bg-<?php echo $user['is_seller_active'] ? 'green' : 'gray'; ?>-100 text-<?php echo $user['is_seller_active'] ? 'green' : 'gray'; ?>-800 rounded-full text-xs">
                                        <?php echo $user['is_seller_active'] ? 'Seller' : ($user['user_type'] === 'seller' ? 'Inactive Seller' : 'Buyer'); ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        <nav>
                            <ul class="space-y-2">
                                <li>
                                    <a href="#purchases" class="flex items-center text-gray-800 hover:text-primary">
                                        <i class="fas fa-shopping-bag w-5 text-center mr-2"></i>
                                        <span>My Purchases</span>
                                    </a>
                                </li>
                                <?php if ($user['user_type'] === 'seller' || $user['is_seller_active']): ?>
                                <li>
                                    <a href="#seller" class="flex items-center text-gray-800 hover:text-primary">
                                        <i class="fas fa-store w-5 text-center mr-2"></i>
                                        <span>Seller Dashboard</span>
                                    </a>
                                </li>
                                <?php if ($user['is_seller_active']): ?>
                                <li>
                                    <a href="#products" class="flex items-center text-gray-800 hover:text-primary">
                                        <i class="fas fa-box w-5 text-center mr-2"></i>
                                        <span>My Products</span>
                                    </a>
                                </li>
                                <?php endif; ?>
                                <?php endif; ?>
                                <li>
                                    <a href="#referral" class="flex items-center text-gray-800 hover:text-primary">
                                        <i class="fas fa-user-plus w-5 text-center mr-2"></i>
                                        <span>Referral Program</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="/update-profile" class="flex items-center text-gray-800 hover:text-primary">
                                        <i class="fas fa-user-edit w-5 text-center mr-2"></i>
                                        <span>Edit Profile</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="/logout" class="flex items-center text-gray-800 hover:text-primary">
                                        <i class="fas fa-sign-out-alt w-5 text-center mr-2"></i>
                                        <span>Logout</span>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="md:col-span-3">
                <!-- Purchases Section -->
                <section id="purchases" class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-semibold">My Purchases</h2>
                    </div>
                    <div class="p-6">
                        <?php if (count($purchases) > 0): ?>
                            <div class="space-y-6">
                                <?php foreach ($purchases as $purchase): ?>
                                    <div class="border border-gray-200 rounded-lg p-4 flex flex-col md:flex-row md:items-center md:justify-between">
                                        <div class="flex-grow mb-4 md:mb-0">
                                            <h3 class="font-semibold"><?php echo $purchase['product_name']; ?></h3>
                                            <p class="text-gray-600 text-sm">
                                                Purchased on: <?php echo date('M d, Y', strtotime($purchase['created_at'])); ?>
                                            </p>
                                            <p class="text-gray-600 text-sm">
                                                Transaction ID: <?php echo $purchase['transaction_id']; ?>
                                            </p>
                                        </div>
                                        <div>
                                            <a href="/download?product=<?php echo $purchase['product_id']; ?>" class="bg-primary text-white hover:bg-blue-800 py-2 px-4 rounded-md inline-flex items-center">
                                                <i class="fas fa-download mr-2"></i> Download
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-8">
                                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-shopping-bag text-gray-400 text-xl"></i>
                                </div>
                                <h3 class="text-lg font-medium mb-2">No purchases yet</h3>
                                <p class="text-gray-600 mb-4">You haven't made any purchases yet. Browse our products to get started.</p>
                                <a href="/" class="bg-primary text-white hover:bg-blue-800 py-2 px-4 rounded-md inline-flex items-center">
                                    <i class="fas fa-search mr-2"></i> Browse Products
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>
                
                <?php if ($user['user_type'] === 'seller' || $user['is_seller_active']): ?>
                <!-- Seller Dashboard Section -->
                <section id="seller" class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-semibold">Seller Dashboard</h2>
                    </div>
                    <div class="p-6">
                        <?php if ($user['is_seller_active']): ?>
                            <!-- Active Seller Dashboard -->
                            <div class="mb-6">
                                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                                    <div class="flex">
                                        <div class="py-1"><i class="fas fa-check-circle mr-2"></i></div>
                                        <div>
                                            <p class="font-bold">Your seller account is active!</p>
                                            <p class="text-sm">You can now upload and sell your products.</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <?php if ($subscription): ?>
                                <div class="border border-gray-200 rounded-lg p-4 mb-4">
                                    <h3 class="font-semibold mb-2">Current Subscription</h3>
                                    <p><strong>Plan:</strong> <?php echo $subscription['plan_name']; ?></p>
                                    <p><strong>Start Date:</strong> <?php echo date('M d, Y', strtotime($subscription['start_date'])); ?></p>
                                    <?php if ($subscription['end_date']): ?>
                                    <p><strong>End Date:</strong> <?php echo date('M d, Y', strtotime($subscription['end_date'])); ?></p>
                                    <?php else: ?>
                                    <p><strong>End Date:</strong> <span class="text-green-600">Lifetime</span></p>
                                    <?php endif; ?>
                                    <p><strong>Status:</strong> 
                                        <span class="inline-block px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">
                                            Active
                                        </span>
                                    </p>
                                </div>
                                <?php endif; ?>
                                
                                <div class="flex flex-col md:flex-row gap-4 mt-6">
                                    <a href="/seller/add-product" class="bg-primary text-white hover:bg-blue-800 py-2 px-4 rounded-md inline-flex items-center justify-center">
                                        <i class="fas fa-plus mr-2"></i> Add New Product
                                    </a>
                                    <a href="/seller/sales" class="bg-gray-200 text-gray-800 hover:bg-gray-300 py-2 px-4 rounded-md inline-flex items-center justify-center">
                                        <i class="fas fa-chart-line mr-2"></i> View Sales
                                    </a>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- Inactive Seller Dashboard -->
                            <div class="text-center py-8">
                                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-store text-primary text-xl"></i>
                                </div>
                                <h3 class="text-lg font-medium mb-2">Activate Your Seller Account</h3>
                                <p class="text-gray-600 mb-4">Choose a subscription plan to start selling your products.</p>
                                
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
                                    <?php 
                                    $plans = get_subscription_plans();
                                    foreach ($plans as $plan): 
                                    ?>
                                    <div class="border border-gray-200 rounded-lg p-6 flex flex-col <?php echo $plan['duration_type'] === 'monthly' ? 'border-primary' : ''; ?>">
                                        <?php if ($plan['duration_type'] === 'monthly'): ?>
                                        <div class="bg-primary text-white text-xs font-bold uppercase px-3 py-1 rounded-full self-start mb-4">
                                            Popular
                                        </div>
                                        <?php endif; ?>
                                        <h4 class="text-xl font-bold mb-2"><?php echo $plan['name']; ?></h4>
                                        <div class="text-3xl font-bold mb-2"><?php echo format_currency($plan['price']); ?></div>
                                        <p class="text-gray-600 mb-4">
                                            <?php 
                                            if ($plan['duration_type'] === 'weekly') echo '/ week';
                                            elseif ($plan['duration_type'] === 'monthly') echo '/ month';
                                            else echo 'one-time payment';
                                            ?>
                                        </p>
                                        <ul class="mb-6 flex-grow">
                                            <li class="flex items-center mb-2">
                                                <i class="fas fa-check text-green-500 mr-2"></i>
                                                <span>Upload unlimited products</span>
                                            </li>
                                            <li class="flex items-center mb-2">
                                                <i class="fas fa-check text-green-500 mr-2"></i>
                                                <span>Detailed sales analytics</span>
                                            </li>
                                            <li class="flex items-center mb-2">
                                                <i class="fas fa-check text-green-500 mr-2"></i>
                                                <span>Priority support</span>
                                            </li>
                                        </ul>
                                        <a href="/seller/activate?plan=<?php echo $plan['id']; ?>" class="bg-primary text-white hover:bg-blue-800 py-2 px-4 rounded-md text-center">
                                            Choose Plan
                                        </a>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>
                <?php endif; ?>
                
                <?php if ($user['is_seller_active']): ?>
                <!-- Seller Products Section -->
                <section id="products" class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-semibold">My Products</h2>
                    </div>
                    <div class="p-6">
                        <?php if (count($seller_products) > 0): ?>
                            <div class="space-y-6">
                                <?php foreach ($seller_products as $product): ?>
                                    <div class="border border-gray-200 rounded-lg p-4 flex flex-col md:flex-row">
                                        <div class="md:w-24 md:h-24 mb-4 md:mb-0 md:mr-4">
                                            <?php if (!empty($product['image']) && file_exists($product['image'])): ?>
                                                <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" class="w-full h-full object-cover rounded">
                                            <?php else: ?>
                                                <div class="w-full h-full bg-gray-200 rounded flex items-center justify-center">
                                                    <i class="fas fa-image text-gray-400"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex-grow">
                                            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                                                <h3 class="font-semibold"><?php echo $product['name']; ?></h3>
                                                <div>
                                                    <span class="inline-block px-2 py-1 bg-<?php 
                                                        if ($product['status'] === 'approved') echo 'green';
                                                        elseif ($product['status'] === 'rejected') echo 'red';
                                                        else echo 'yellow';
                                                    ?>-100 text-<?php 
                                                        if ($product['status'] === 'approved') echo 'green';
                                                        elseif ($product['status'] === 'rejected') echo 'red';
                                                        else echo 'yellow';
                                                    ?>-800 rounded-full text-xs">
                                                        <?php echo ucfirst($product['status']); ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <p class="text-gray-600 text-sm mt-1">
                                                Category: <?php echo $product['category_name']; ?>
                                            </p>
                                            <p class="text-gray-600 text-sm">
                                                Price: <?php echo format_currency($product['price']); ?>
                                            </p>
                                            <div class="mt-4 flex flex-wrap gap-2">
                                                <a href="/seller/edit-product?id=<?php echo $product['id']; ?>" class="bg-primary text-white hover:bg-blue-800 py-1 px-3 rounded-md text-sm inline-flex items-center">
                                                    <i class="fas fa-edit mr-1"></i> Edit
                                                </a>
                                                <a href="/product/<?php echo $product['slug']; ?>" class="bg-gray-200 text-gray-800 hover:bg-gray-300 py-1 px-3 rounded-md text-sm inline-flex items-center">
                                                    <i class="fas fa-eye mr-1"></i> View
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-8">
                                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-box text-gray-400 text-xl"></i>
                                </div>
                                <h3 class="text-lg font-medium mb-2">No products yet</h3>
                                <p class="text-gray-600 mb-4">You haven't added any products yet. Start selling by adding your first product.</p>
                                <a href="/seller/add-product" class="bg-primary text-white hover:bg-blue-800 py-2 px-4 rounded-md inline-flex items-center">
                                    <i class="fas fa-plus mr-2"></i> Add New Product
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>
                <?php endif; ?>
                
                <!-- Referral Section -->
                <section id="referral" class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-semibold">Referral Program</h2>
                    </div>
                    <div class="p-6">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                            <h3 class="text-lg font-semibold mb-2">Your Referral Code</h3>
                            <div class="flex items-center">
                                <input type="text" value="<?php echo $user['referral_code']; ?>" class="w-full px-4 py-2 border border-gray-300 rounded-md bg-white" readonly id="referral-code">
                                <button onclick="copyReferralCode()" class="ml-2 bg-primary text-white hover:bg-blue-800 py-2 px-4 rounded-md">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                            <p class="text-sm text-gray-600 mt-2">
                                Share this code with your friends. When they use it during registration or subscription purchase, they'll get a 5% discount!
                            </p>
                        </div>
                        
                        <div class="border border-gray-200 rounded-lg p-6">
                            <h3 class="text-lg font-semibold mb-4">How It Works</h3>
                            <ul class="space-y-4">
                                <li class="flex">
                                    <div class="flex-shrink-0 h-8 w-8 bg-blue-100 text-primary rounded-full flex items-center justify-center mr-3">
                                        1
                                    </div>
                                    <div>
                                        <h4 class="font-medium">Share Your Code</h4>
                                        <p class="text-gray-600">Share your unique referral code with friends and colleagues.</p>
                                    </div>
                                </li>
                                <li class="flex">
                                    <div class="flex-shrink-0 h-8 w-8 bg-blue-100 text-primary rounded-full flex items-center justify-center mr-3">
                                        2
                                    </div>
                                    <div>
                                        <h4 class="font-medium">They Get a Discount</h4>
                                        <p class="text-gray-600">When they use your code, they'll receive a 5% discount on their subscription.</p>
                                    </div>
                                </li>
                                <li class="flex">
                                    <div class="flex-shrink-0 h-8 w-8 bg-blue-100 text-primary rounded-full flex items-center justify-center mr-3">
                                        3
                                    </div>
                                    <div>
                                        <h4 class="font-medium">You Earn Rewards</h4>
                                        <p class="text-gray-600">For each successful referral, you'll earn rewards (coming soon).</p>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </section>
                
                <!-- Profile Section -->
                <section id="profile" class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-semibold">Edit Profile</h2>
                    </div>
                    <div class="p-6">
                        <form method="post" action="/update-profile">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <label for="full_name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                                    <input type="text" id="full_name" name="full_name" value="<?php echo $user['full_name']; ?>" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                                    <input type="text" id="phone" name="phone" value="<?php echo $user['phone']; ?>" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                                    <input type="email" id="email" name="email" value="<?php echo $user['email']; ?>" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" readonly>
                                    <p class="text-sm text-gray-500 mt-1">Email cannot be changed</p>
                                </div>
                                <div>
                                    <label for="user_type" class="block text-sm font-medium text-gray-700 mb-1">Account Type</label>
                                    <select id="user_type" name="user_type" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                        <option value="buyer" <?php echo $user['user_type'] === 'buyer' ? 'selected' : ''; ?>>Buyer</option>
                                        <option value="seller" <?php echo $user['user_type'] === 'seller' ? 'selected' : ''; ?>>Seller</option>
                                    </select>
                                    <p class="text-sm text-gray-500 mt-1">You can change your account type at any time</p>
                                </div>
                            </div>
                            
                            <div class="border-t border-gray-200 pt-6">
                                <h3 class="text-lg font-medium mb-4">Change Password</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                    <div>
                                        <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                                        <input type="password" id="current_password" name="current_password" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    <div>
                                        <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                                        <input type="password" id="new_password" name="new_password" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    <div>
                                        <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                                        <input type="password" id="confirm_password" name="confirm_password" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-right">
                                <button type="submit" class="bg-primary text-white hover:bg-blue-800 py-2 px-6 rounded-md font-medium">
                                    Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>

<script>
function copyReferralCode() {
    var copyText = document.getElementById("referral-code");
    copyText.select();
    copyText.setSelectionRange(0, 99999);
    document.execCommand("copy");
    
    // Show a temporary message
    alert("Referral code copied to clipboard!");
}
</script>

<?php include 'includes/footer.php'; ?>
