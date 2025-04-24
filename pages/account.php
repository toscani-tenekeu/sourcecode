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

// Get user purchases
$purchases = get_user_purchases($user_id);
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
                                <li>
                                    <a href="#profile" class="flex items-center text-gray-800 hover:text-primary">
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

<?php include 'includes/footer.php'; ?>
