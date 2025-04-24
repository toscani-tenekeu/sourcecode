<?php 
include 'includes/header.php';

// Check if user is already logged in
if (is_logged_in()) {
    redirect('/account');
}

// Get redirect URL if provided
$redirect = isset($_GET['redirect']) ? clean($_GET['redirect']) : '';

// Process login form
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? clean($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    // Validate input
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password';
    } else {
        // Check if user exists
        $query = "SELECT * FROM users WHERE email = '$email'";
        $result = $conn->query($query);
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                
                // Redirect based on the redirect parameter or to account
                if ($redirect === 'payment' && isset($_SESSION['pending_purchase'])) {
                    redirect('/payment?product=' . $_SESSION['pending_purchase']);
                } else if (!empty($redirect)) {
                    redirect('/' . $redirect);
                } else {
                    redirect('/account');
                }
            } else {
                $error = 'Invalid email or password';
            }
        } else {
            $error = 'Invalid email or password';
        }
    }
}
?>

<div class="bg-gray-100 py-12">
    <div class="container mx-auto px-4">
        <div class="max-w-md mx-auto bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6">
                <h1 class="text-2xl font-bold text-center mb-6">Login to Your Account</h1>
                
                <?php if (!empty($error)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
                        <span class="block sm:inline"><?php echo $error; ?></span>
                    </div>
                <?php endif; ?>
                
                <form method="post">
                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                        <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? $_POST['email'] : ''; ?>" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                    
                    <div class="mb-6">
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input type="password" id="password" name="password" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                    
                    <div class="mb-6">
                        <button type="submit" class="w-full bg-primary text-white hover:bg-blue-800 py-2 px-4 rounded-md font-medium">
                            Login
                        </button>
                    </div>
                    
                    <div class="text-center">
                        <p class="text-gray-600">Don't have an account? <a href="/register<?php echo !empty($redirect) ? '?redirect=' . $redirect : ''; ?>" class="text-primary hover:underline">Register</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
