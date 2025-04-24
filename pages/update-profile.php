<?php 
include 'includes/header.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('/login?redirect=update-profile');
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

// Process form submission
$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = isset($_POST['full_name']) ? clean($_POST['full_name']) : '';
    $phone = isset($_POST['phone']) ? clean($_POST['phone']) : '';
    $user_type = isset($_POST['user_type']) ? clean($_POST['user_type']) : 'buyer';
    $current_password = isset($_POST['current_password']) ? $_POST['current_password'] : '';
    $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    
    // Update basic info
    $update_query = "UPDATE users SET full_name = '$full_name', phone = '$phone', user_type = '$user_type' WHERE id = $user_id";
    
    if ($conn->query($update_query)) {
        $success = true;
        
        // Update password if provided
        if (!empty($current_password) && !empty($new_password) && !empty($confirm_password)) {
            // Verify current password
            if (password_verify($current_password, $user['password'])) {
                // Check if new passwords match
                if ($new_password === $confirm_password) {
                    // Check password length
                    if (strlen($new_password) >= 6) {
                        // Hash new password
                        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                        
                        // Update password
                        $password_query = "UPDATE users SET password = '$hashed_password' WHERE id = $user_id";
                        
                        if ($conn->query($password_query)) {
                            $success = true;
                        } else {
                            $error = 'Failed to update password. Please try again.';
                        }
                    } else {
                        $error = 'Password must be at least 6 characters long.';
                    }
                } else {
                    $error = 'New passwords do not match.';
                }
            } else {
                $error = 'Current password is incorrect.';
            }
        }
        
        // Refresh user data
        $user_result = $conn->query($user_query);
        $user = $user_result->fetch_assoc();
    } else {
        $error = 'Failed to update profile. Please try again.';
    }
}
?>

<div class="bg-gray-100 py-12">
    <div class="container mx-auto px-4">
        <h1 class="text-3xl font-bold mb-8">Edit Profile</h1>
        
        <?php if (!empty($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
                <span class="block sm:inline"><?php echo $error; ?></span>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert">
                <span class="block sm:inline">Profile updated successfully!</span>
            </div>
        <?php endif; ?>
        
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-semibold">Edit Profile</h2>
            </div>
            <div class="p-6">
                <form method="post">
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
                            <input type="email" id="email" name="email" value="<?php echo $user['email']; ?>" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 bg-gray-100" readonly>
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
                    
                    <div class="flex justify-between">
                        <a href="/account" class="bg-gray-200 text-gray-800 hover:bg-gray-300 py-2 px-6 rounded-md font-medium">
                            Back to Account
                        </a>
                        <button type="submit" class="bg-primary text-white hover:bg-blue-800 py-2 px-6 rounded-md font-medium">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
