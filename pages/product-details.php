<?php 
include 'includes/header.php';

// Get product slug from URL
$slug = isset($_GET['slug']) ? clean($_GET['slug']) : '';

// Get product details
$product = get_product_by_slug($slug);

// If product not found, redirect to 404
if (!$product) {
    header("HTTP/1.0 404 Not Found");
    include 'pages/404.php';
    exit;
}

// Get category slug
$category_query = "SELECT slug FROM categories WHERE id = " . $product['category_id'];
$category_result = $conn->query($category_query);
$category_data = $category_result->fetch_assoc();
$category_slug = $category_data['slug'];
?>

<div class="bg-gray-100 py-8">
    <div class="container mx-auto px-4">
        <!-- Breadcrumbs -->
        <div class="flex items-center text-sm text-gray-600 mb-6">
            <a href="/" class="hover:text-primary">Home</a>
            <i class="fas fa-chevron-right mx-2 text-xs"></i>
            <a href="/category/<?php echo $category_slug; ?>" class="hover:text-primary"><?php echo $product['category_name']; ?></a>
            <i class="fas fa-chevron-right mx-2 text-xs"></i>
            <span class="text-gray-800"><?php echo $product['name']; ?></span>
        </div>
        
        <!-- Rest of the file remains the same -->
        
        <!-- Product Details -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 p-8">
                <!-- Product Image -->
                <div>
                    <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" class="w-full h-auto rounded-lg shadow-md">
                    
                    <?php if ($product['preview_url']): ?>
                    <div class="mt-6">
                        <a href="<?php echo $product['preview_url']; ?>" target="_blank" class="bg-gray-800 text-white hover:bg-gray-900 py-2 px-4 rounded-md inline-flex items-center">
                            <i class="fas fa-eye mr-2"></i> Live Preview
                        </a>
                        <p class="text-sm text-gray-600 mt-2">
                            <i class="fas fa-info-circle mr-1"></i> The preview is limited and does not include all features.
                        </p>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Product Info -->
                <div>
                    <h1 class="text-3xl font-bold mb-4"><?php echo $product['name']; ?></h1>
                    <div class="flex items-center mb-4">
                        <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm"><?php echo $product['category_name']; ?></span>
                    </div>
                    
                    <div class="text-2xl font-bold text-primary mb-6">
                        <?php echo format_currency($product['price']); ?>
                    </div>
                    
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-2">Description</h3>
                        <p class="text-gray-700"><?php echo $product['description']; ?></p>
                    </div>
                    
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-2">Technologies</h3>
                        <div class="flex flex-wrap gap-2">
                            <?php 
                            $tech_stack = explode(',', $product['tech_stack']);
                            foreach ($tech_stack as $tech): 
                                $tech = trim($tech);
                            ?>
                            <span class="bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-sm"><?php echo $tech; ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold mb-2">Requirements</h3>
                        <p class="text-gray-700"><?php echo $product['requirements']; ?></p>
                    </div>
                    
                    <div>
                        <form action="/payment" method="post">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <button type="submit" class="w-full bg-primary text-white hover:bg-blue-800 py-3 px-6 rounded-md font-semibold inline-flex items-center justify-center">
                                <i class="fas fa-shopping-cart mr-2"></i> Purchase Now
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Additional Information -->
            <div class="border-t border-gray-200 p-8">
                <div class="mb-8">
                    <h3 class="text-xl font-semibold mb-4">What's Included</h3>
                    <ul class="list-disc pl-6 space-y-2 text-gray-700">
                        <li>Complete source code</li>
                        <li>Detailed documentation</li>
                        <li>Installation instructions</li>
                        <li>README.md file with deployment information</li>
                        <li>Free updates for 6 months</li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-xl font-semibold mb-4">Important Notes</h3>
                    <ul class="list-disc pl-6 space-y-2 text-gray-700">
                        <li>After purchase, you can download the project immediately</li>
                        <li>The code is provided as-is without warranty</li>
                        <li>You may use the code for both personal and commercial projects</li>
                        <li>You may not redistribute or resell the code</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
