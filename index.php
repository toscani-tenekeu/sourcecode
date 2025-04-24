<?php
// Route the request
$request_uri = $_SERVER['REQUEST_URI'];
$base_path = '';  // Set this if your site is in a subdirectory

// Remove base path and query string from the URI
$uri = strtok(str_replace($base_path, '', $request_uri), '?');
$uri = rtrim($uri, '/');

// Set default route if empty
if (empty($uri)) {
    $uri = '/';
}

// Define routes
switch ($uri) {
    case '/':
        include 'pages/home.php';
        break;
        
    case '/login':
        include 'pages/login.php';
        break;
        
    case '/register':
        include 'pages/register.php';
        break;
        
    case '/account':
        include 'pages/account.php';
        break;
        
    case '/logout':
        // Destroy session and redirect to home
        session_start();
        session_destroy();
        header('Location: /');
        exit;
        break;
        
    case '/payment':
        include 'pages/payment.php';
        break;
        
    case '/payment/success':
        include 'pages/payment-success.php';
        break;
        
    case '/download':
        include 'pages/download.php';
        break;
        
    case '/seller/activate':
        include 'pages/seller/activate.php';
        break;
        
    case '/seller/payment':
        include 'pages/seller/payment.php';
        break;
        
    case '/seller/subscription-success':
        include 'pages/seller/subscription-success.php';
        break;
        
    case '/seller/add-product':
        include 'pages/seller/add-product.php';
        break;
        
    case '/seller/edit-product':
        include 'pages/seller/edit-product.php';
        break;
        
    case '/seller/sales':
        include 'pages/seller/sales.php';
        break;
        
    case '/update-profile':
        include 'pages/update-profile.php';
        break;
        
    default:
        // Check if it's a category page
        if (preg_match('/^\/category\/(.+)$/', $uri, $matches)) {
            $_GET['slug'] = $matches[1];
            include 'pages/category.php';
        }
        // Check if it's a product page
        elseif (preg_match('/^\/product\/(.+)$/', $uri, $matches)) {
            $_GET['slug'] = $matches[1];
            include 'pages/product-details.php';
        }
        // Check if it's a payment success page with transaction ID
        elseif (preg_match('/^\/payment\/success$/', $uri)) {
            include 'pages/payment-success.php';
        }
        // 404 page not found
        else {
            header("HTTP/1.0 404 Not Found");
            include 'pages/404.php';
        }
        break;
}
?>
