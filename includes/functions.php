<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Handle language selection
if (isset($_GET['lang']) && in_array($_GET['lang'], ['fr', 'en'])) {
    $_SESSION['lang'] = $_GET['lang'];
}

// Set default language to French if not set
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'fr';
}

// Clean user input
function clean($string) {
    global $conn;
    $string = trim($string);
    $string = stripslashes($string);
    $string = htmlspecialchars($string);
    $string = mysqli_real_escape_string($conn, $string);
    return $string;
}

// Generate slug from string
function generate_slug($string) {
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9\-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    $string = trim($string, '-');
    return $string;
}

// Check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Redirect to another page
function redirect($location) {
    header("Location: $location");
    exit;
}

// Get all categories
function get_categories() {
    global $conn;
    $query = "SELECT * FROM categories ORDER BY name";
    $result = $conn->query($query);
    
    $categories = array();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
    }
    
    return $categories;
}

// Get products by category
function get_products_by_category($category_slug, $limit = 10) {
    global $conn;
    $category_slug = clean($category_slug);
    
    $query = "SELECT p.* FROM products p 
              JOIN categories c ON p.category_id = c.id 
              WHERE c.slug = '$category_slug' 
              ORDER BY p.created_at DESC 
              LIMIT $limit";
    
    $result = $conn->query($query);
    
    $products = array();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
    
    return $products;
}

// Get product by slug
function get_product_by_slug($slug) {
    global $conn;
    $slug = clean($slug);
    
    $query = "SELECT p.*, c.name as category_name FROM products p 
              JOIN categories c ON p.category_id = c.id 
              WHERE p.slug = '$slug'";
    
    $result = $conn->query($query);
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

// Get featured products
function get_featured_products($limit = 6) {
    global $conn;
    
    $query = "SELECT p.*, c.name as category_name FROM products p 
              JOIN categories c ON p.category_id = c.id 
              ORDER BY RAND() 
              LIMIT $limit";
    
    $result = $conn->query($query);
    
    $products = array();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
    
    return $products;
}

// Format currency
function format_currency($amount) {
    return number_format($amount, 0, '.', ' ') . ' XAF';
}

// Check if user has purchased a product
function has_purchased_product($user_id, $product_id) {
    global $conn;
    $user_id = (int)$user_id;
    $product_id = (int)$product_id;
    
    $query = "SELECT * FROM transactions 
              WHERE user_id = $user_id 
              AND product_id = $product_id 
              AND status = 'completed'";
    
    $result = $conn->query($query);
    
    return $result->num_rows > 0;
}


// Save transaction with external ID
function save_transaction($user_id, $product_id, $transaction_id, $amount, $payment_method, $status, $external_id = '') {
    global $conn;
    $user_id = (int)$user_id;
    $product_id = (int)$product_id;
    $transaction_id = clean($transaction_id);
    $amount = (float)$amount;
    $payment_method = clean($payment_method);
    $status = clean($status);
    $external_id = clean($external_id);
    
    $query = "INSERT INTO transactions (user_id, product_id, transaction_id, amount, payment_method, status, external_id) 
              VALUES ($user_id, $product_id, '$transaction_id', $amount, '$payment_method', '$status', '$external_id')";
    
    return $conn->query($query);
}


// Get user purchases
function get_user_purchases($user_id) {
    global $conn;
    $user_id = (int)$user_id;
    
    $query = "SELECT t.*, p.name as product_name, p.slug as product_slug, p.file_path 
              FROM transactions t 
              JOIN products p ON t.product_id = p.id 
              WHERE t.user_id = $user_id 
              AND t.status = 'completed' 
              ORDER BY t.created_at DESC";
    
    $result = $conn->query($query);
    
    $purchases = array();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $purchases[] = $row;
        }
    }
    
    return $purchases;
}

// Translate text based on current language
function translate($key) {
    $lang = $_SESSION['lang'] ?? 'fr';
    $translations = include 'lang/' . $lang . '.php';
    return $translations[$key] ?? $key;
}

// Generate a unique referral code
function generate_referral_code() {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $code = '';
    for ($i = 0; $i < 8; $i++) {
        $code .= $characters[rand(0, strlen($characters) - 1)];
    }
    
    global $conn;
    
    // Check if code already exists
    $query = "SELECT * FROM users WHERE referral_code = '$code'";
    $result = $conn->query($query);
    
    if ($result->num_rows > 0) {
        // If code exists, generate a new one recursively
        return generate_referral_code();
    }
    
    return $code;
}

// Check if user is a seller
function is_seller() {
    return isset($_SESSION['user_id']) && isset($_SESSION['is_seller_active']) && $_SESSION['is_seller_active'] == 1;
}

// Check if user has an active subscription
function has_active_subscription($user_id) {
    global $conn;
    $user_id = (int)$user_id;
    
    $query = "SELECT * FROM subscriptions 
              WHERE user_id = $user_id 
              AND (status = 'active' AND (end_date IS NULL OR end_date > NOW()))
              ORDER BY created_at DESC LIMIT 1";
    
    $result = $conn->query($query);
    
    return $result->num_rows > 0;
}

// Get user's subscription details
function get_user_subscription($user_id) {
    global $conn;
    $user_id = (int)$user_id;
    
    $query = "SELECT s.*, p.name as plan_name, p.duration_type 
              FROM subscriptions s
              JOIN subscription_plans p ON s.plan_id = p.id
              WHERE s.user_id = $user_id 
              AND (s.status = 'active' AND (s.end_date IS NULL OR s.end_date > NOW()))
              ORDER BY s.created_at DESC LIMIT 1";
    
    $result = $conn->query($query);
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

// Get subscription plans
function get_subscription_plans() {
    global $conn;
    
    $query = "SELECT * FROM subscription_plans ORDER BY price";
    $result = $conn->query($query);
    
    $plans = array();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $plans[] = $row;
        }
    }
    
    return $plans;
}

// Calculate discount amount based on referral code
function calculate_discount($amount, $referral_code) {
    global $conn;
    
    if (empty($referral_code)) {
        return 0;
    }
    
    // Check if referral code exists
    $query = "SELECT * FROM users WHERE referral_code = '$referral_code'";
    $result = $conn->query($query);
    
    if ($result->num_rows > 0) {
        // 5% discount
        return $amount * 0.05;
    }
    
    return 0;
}

// Get seller products
function get_seller_products($seller_id) {
    global $conn;
    $seller_id = (int)$seller_id;
    
    $query = "SELECT p.*, c.name as category_name 
              FROM products p
              JOIN categories c ON p.category_id = c.id
              WHERE p.seller_id = $seller_id
              ORDER BY p.created_at DESC";
    
    $result = $conn->query($query);
    
    $products = array();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
    
    return $products;
}

// Save subscription
function save_subscription($user_id, $plan_id, $amount, $transaction_id, $start_date, $end_date = null) {
    global $conn;
    $user_id = (int)$user_id;
    $plan_id = (int)$plan_id;
    $amount = (float)$amount;
    $transaction_id = clean($transaction_id);
    
    // Format dates for MySQL
    $start_date_formatted = date('Y-m-d H:i:s', strtotime($start_date));
    $end_date_formatted = $end_date ? date('Y-m-d H:i:s', strtotime($end_date)) : "NULL";
    
    if ($end_date) {
        $query = "INSERT INTO subscriptions (user_id, plan_id, amount, start_date, end_date, transaction_id) 
                  VALUES ($user_id, $plan_id, $amount, '$start_date_formatted', '$end_date_formatted', '$transaction_id')";
    } else {
        $query = "INSERT INTO subscriptions (user_id, plan_id, amount, start_date, transaction_id) 
                  VALUES ($user_id, $plan_id, $amount, '$start_date_formatted', '$transaction_id')";
    }
    
    if ($conn->query($query)) {
        // Update user as seller
        $update_query = "UPDATE users SET is_seller_active = 1, seller_activated_at = NOW() WHERE id = $user_id";
        $conn->query($update_query);
        
        // Update session
        $_SESSION['is_seller_active'] = 1;
        
        return true;
    }
    
    return false;
}

// Save referral
function save_referral($referrer_code, $referred_id) {
    global $conn;
    $referred_id = (int)$referred_id;
    $referrer_code = clean($referrer_code);
    
    // Get referrer ID
    $query = "SELECT id FROM users WHERE referral_code = '$referrer_code'";
    $result = $conn->query($query);
    
    if ($result->num_rows > 0) {
        $referrer = $result->fetch_assoc();
        $referrer_id = $referrer['id'];
        
        // Save referral
        $query = "INSERT INTO referrals (referrer_id, referred_id, used_at) 
                  VALUES ($referrer_id, $referred_id, NOW())";
        
        return $conn->query($query);
    }
    
    return false;
}
?>
