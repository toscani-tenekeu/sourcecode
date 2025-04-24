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
?>
