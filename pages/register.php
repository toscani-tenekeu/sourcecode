<?php 
include 'includes/header.php';

// Check if user is already logged in
if (is_logged_in()) {
    redirect('/account');
}

// Get redirect URL if provided
$redirect = isset($_GET['redirect']) ? clean($_GET['redirect']) : '';

// Process registration form
$error = '';
$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? clean($_POST['username']) : '';
    $email = isset($_POST['email']) ? clean($_POST['email']) : '';
    $full_name = isset($_POST['full_name']) ? clean($_POST['full_name']) : '';
    $phone = isset($_POST['phone']) ? clean($_POST['phone']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    $user_type = isset($_POST['user_type']) ? clean($_POST['user_type']) : 'buyer';
    $referral_code = isset($_POST['referral_code']) ? clean($_POST['referral_code']) : '';
    
    // Validate input
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all required fields';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } else {
        // Check if username already exists
        $query = "SELECT * FROM users WHERE username = '$username'";
        $result = $conn->query($query);
        
        if ($result->num_rows > 0) {
            $error = 'Username already taken, please choose another one';
        } else {
            // Check if email already exists
            $query = "SELECT * FROM users WHERE email = '$email'";
            $result = $conn->query($query);
            
            if ($result->num_rows > 0) {
                $error = 'Email already registered, please login or use another email';
            } else {
                // Generate unique referral code
                $new_referral_code = generate_referral_code();
                
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Check if referral code exists
                $referred_by = '';
                if (!empty($referral_code)) {
                    $check_query = "SELECT * FROM users WHERE referral_code = '$referral_code'";
                    $check_result = $conn->query($check_query);
                    
                    if ($check_result->num_rows > 0) {
                        $referred_by = $referral_code;
                    }
                }
                
                // Insert new user
                $query = "INSERT INTO users (username, email, password, full_name, phone, user_type, referral_code, referred_by) 
                          VALUES ('$username', '$email', '$hashed_password', '$full_name', '$phone', '$user_type', '$new_referral_code', '$referred_by')";
                
                if ($conn->query($query)) {
                    // Get user ID
                    $user_id = $conn->insert_id;
                    
                    // Set session variables
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['username'] = $username;
                    $_SESSION['email'] = $email;
                    $_SESSION['user_type'] = $user_type;
                    $_SESSION['referral_code'] = $new_referral_code;
                    $_SESSION['is_seller_active'] = 0;
                    
                    // Save referral if provided
                    if (!empty($referred_by)) {
                        save_referral($referred_by, $user_id);
                    }
                    
                    // Redirect based on the redirect parameter or to account
                    if ($redirect === 'payment' && isset($_SESSION['pending_purchase'])) {
                        redirect('/payment?product=' . $_SESSION['pending_purchase']);
                    } else if (!empty($redirect)) {
                        redirect('/' . $redirect);
                    } else {
                        $success = true;
                    }
                } else {
                    $error = 'Registration failed. Please try again later.';
                }
            }
        }
    }
}
?>

<div class="bg-gray-100 py-12">
    <div class="container mx-auto px-4">
        <div class="max-w-md mx-auto bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6">
                <h1 class="text-2xl font-bold text-center mb-6">Create an Account</h1>
                
                <?php if (!empty($error)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
                        <span class="block sm:inline"><?php echo $error; ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert">
                        <span class="block sm:inline">Registration successful! <a href="/account" class="underline">Go to your account</a></span>
                    </div>
                <?php else: ?>
                    <form method="post">
                        <div class="mb-4">
                            <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username*</label>
                            <input type="text" id="username" name="username" value="<?php echo isset($_POST['username']) ? $_POST['username'] : ''; ?>" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
                        </div>
                        
                        <div class="mb-4">
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address*</label>
                            <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? $_POST['email'] : ''; ?>" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
                        </div>
                        
                        <div class="mb-4">
                            <label for="full_name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                            <input type="text" id="full_name" name="full_name" value="<?php echo isset($_POST['full_name']) ? $_POST['full_name'] : ''; ?>" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div class="mb-4">
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                            <input type="text" id="phone" name="phone" value="<?php echo isset($_POST['phone']) ? $_POST['phone'] : ''; ?>" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Account Type*</label>
                            <div class="flex space-x-4">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="user_type" value="buyer" class="form-radio text-primary" <?php echo (!isset($_POST['user_type']) || $_POST['user_type'] === 'buyer') ? 'checked' : ''; ?>>
                                    <span class="ml-2">Buyer</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="user_type" value="seller" class="form-radio text-primary" <?php echo (isset($_POST['user_type']) && $_POST['user_type'] === 'seller') ? 'checked' : ''; ?>>
                                    <span class="ml-2">Seller</span>
                                </label>
                            </div>
                            <p class="text-sm text-gray-500 mt-1">You can always change your account type later</p>
                        </div>
                        
                        <div class="mb-4">
                            <label for="referral_code" class="block text-sm font-medium text-gray-700 mb-1">Referral Code (Optional)</label>
                            <input type="text" id="referral_code" name="referral_code" value="<?php echo isset($_POST['referral_code']) ? $_POST['referral_code'] : ''; ?>" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                            <p class="text-sm text-gray-500 mt-1">Enter a referral code if you have one</p>
                        </div>
                        
                        <div class="mb-4">
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password*</label>
                            <input type="password" id="password" name="password" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
                            <p class="text-sm text-gray-500 mt-1">Must be at least 6 characters long</p>
                        </div>
                        
                        <div class="mb-6">
                            <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password*</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
                        </div>
                        
                        <div class="mb-6">
                            <button type="submit" class="w-full bg-primary text-white hover:bg-blue-800 py-2 px-4 rounded-md font-medium">
                                Register
                            </button>
                        </div>
                        
                        <div class="text-center">
                            <p class="text-gray-600">Already have an account? <a href="/login<?php echo !empty($redirect) ? '?redirect=' . $redirect : ''; ?>" class="text-primary hover:underline">Login</a></p>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
