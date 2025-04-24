<?php 
include 'includes/header.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('/login?redirect=download');
}

// Check if product ID is in the query string
$product_id = isset($_GET['product']) ? (int)$_GET['product'] : 0;

// If no product ID, redirect to account
if (empty($product_id)) {
    redirect('/account');
}

// Check if user has purchased this product
if (!has_purchased_product($_SESSION['user_id'], $product_id)) {
    redirect('/product/' . $slug);
}

// Get product details
$product_query = "SELECT * FROM products WHERE id = $product_id";
$product_result = $conn->query($product_query);

if ($product_result->num_rows === 0) {
    redirect('/account');
}

$product = $product_result->fetch_assoc();

// Check if the download button was clicked
if (isset($_POST['download'])) {
    $file_path = $product['file_path'];
    
    // Check if file exists
    if (file_exists($file_path)) {
        // Set headers for file download
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file_path));
        flush();
        readfile($file_path);
        exit;
    }
}
?>

<div class="bg-gray-100 py-12">
    <div class="container mx-auto px-4">
        <h1 class="text-3xl font-bold mb-8 text-center">Download Your Purchase</h1>
        
        <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-8 text-center">
                <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-download text-primary text-3xl"></i>
                </div>
                
                <h2 class="text-2xl font-bold mb-4"><?php echo $product['name']; ?></h2>
                
                <div class="bg-gray-50 p-4 rounded-lg mb-8 text-left">
                    <h3 class="font-semibold mb-2">Important Information</h3>
                    <ul class="list-disc pl-6 space-y-2 text-gray-700">
                        <li>The download will start automatically after clicking the button below.</li>
                        <li>Please refer to the README.md file for installation and deployment instructions.</li>
                        <li>If you have any issues with the download, please contact our support team.</li>
                    </ul>
                </div>
                
                <form method="post">
                    <button type="submit" name="download" class="bg-primary text-white hover:bg-blue-800 py-4 px-8 rounded-md font-semibold inline-flex items-center justify-center text-lg">
                        <i class="fas fa-download mr-2"></i> Download Now
                    </button>
                </form>
                
                <div class="mt-8">
                    <a href="/account" class="text-primary hover:underline">
                        <i class="fas fa-arrow-left mr-1"></i> Back to My Account
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
