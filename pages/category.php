<?php 
include 'includes/header.php';

// Get category slug from URL
$slug = isset($_GET['slug']) ? clean($_GET['slug']) : '';

// Get category details
$category_query = "SELECT * FROM categories WHERE slug = '$slug'";
$category_result = $conn->query($category_query);

// If category not found, redirect to 404
if ($category_result->num_rows === 0) {
    header("HTTP/1.0 404 Not Found");
    include 'pages/404.php';
    exit;
}

$category = $category_result->fetch_assoc();

// Get products in this category
$products_query = "SELECT * FROM products WHERE category_id = " . $category['id'];
$products_result = $conn->query($products_query);

$products = array();
if ($products_result->num_rows > 0) {
    while ($row = $products_result->fetch_assoc()) {
        $products[] = $row;
    }
}
?>

<div class="bg-gray-100 py-8">
    <div class="container mx-auto px-4">
        <!-- Breadcrumbs -->
        <div class="flex items-center text-sm text-gray-600 mb-6">
            <a href="/" class="hover:text-primary">Home</a>
            <i class="fas fa-chevron-right mx-2 text-xs"></i>
            <span class="text-gray-800"><?php echo $category['name']; ?></span>
        </div>
        
        <!-- Category Header -->
        <div class="bg-white rounded-lg shadow-md p-8 mb-8">
            <h1 class="text-3xl font-bold mb-4"><?php echo $category['name']; ?></h1>
            <p class="text-gray-600"><?php echo $category['description']; ?></p>
        </div>
        
        <!-- Products Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php if (count($products) > 0): ?>
                <?php foreach ($products as $product): ?>
                    <div class="bg-white rounded-lg overflow-hidden shadow-md">
                        <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" class="w-full h-48 object-cover">
                        <div class="p-6">
                            <h3 class="text-xl font-semibold mb-2"><?php echo $product['name']; ?></h3>
                            <p class="text-gray-600 mb-4"><?php echo substr($product['description'], 0, 100); ?>...</p>
                            <div class="flex justify-between items-center">
                                <span class="text-primary font-bold"><?php echo format_currency($product['price']); ?></span>
                                <a href="/product/<?php echo $product['slug']; ?>" class="bg-primary text-white hover:bg-blue-800 py-2 px-4 rounded-md">
                                    <i class="fas fa-eye mr-1"></i> View Details
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-span-3 text-center py-12">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-box-open text-gray-400 text-xl"></i>
                    </div>
                    <h3 class="text-lg font-medium mb-2">No products found</h3>
                    <p class="text-gray-600">There are no products in this category yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
