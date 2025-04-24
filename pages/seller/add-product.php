<?php 
include dirname(__FILE__) . '/../../includes/header.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('/login?redirect=seller/add-product');
}

// Check if user is an active seller
if (!isset($_SESSION['is_seller_active']) || $_SESSION['is_seller_active'] != 1) {
    redirect('/account#seller');
}

// Get categories
$categories = get_categories();

// Process form submission
$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = isset($_POST['name']) ? clean($_POST['name']) : '';
    $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
    $description = isset($_POST['description']) ? clean($_POST['description']) : '';
    $price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
    $tech_stack = isset($_POST['tech_stack']) ? clean($_POST['tech_stack']) : '';
    $requirements = isset($_POST['requirements']) ? clean($_POST['requirements']) : '';
    
    // Validate input
    if (empty($name) || empty($description) || $category_id === 0 || $price <= 0) {
        $error = 'Please fill in all required fields';
    } else {
        // Generate slug
        $slug = generate_slug($name);
        
        // Check if slug already exists
        $check_query = "SELECT * FROM products WHERE slug = '$slug'";
        $check_result = $conn->query($check_query);
        
        if ($check_result->num_rows > 0) {
            // Append random number to make slug unique
            $slug = $slug . '-' . rand(100, 999);
        }
        
        // Handle file upload
        $file_path = '';
        $image = '';
        
        // Handle product file upload
        if (isset($_FILES['file']) && $_FILES['file']['error'] === 0) {
            $upload_dir = '../uploads/products/';
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_name = $slug . '-' . basename($_FILES['file']['name']);
            $file_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['file']['tmp_name'], $file_path)) {
                $file_path = '/uploads/products/' . $file_name;
            } else {
                $error = 'Failed to upload product file';
            }
        } else {
            $error = 'Please upload a product file';
        }
        
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $upload_dir = '../uploads/images/';
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $image_name = $slug . '-' . basename($_FILES['image']['name']);
            $image_path = $upload_dir . $image_name;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
                $image = '/uploads/images/' . $image_name;
            } else {
                $error = 'Failed to upload product image';
            }
        } else {
            $error = 'Please upload a product image';
        }
        
        if (empty($error)) {
            // Insert product
            $user_id = $_SESSION['user_id'];
            $preview_url = isset($_POST['preview_url']) ? clean($_POST['preview_url']) : '';
            
            $query = "INSERT INTO products (seller_id, category_id, name, slug, description, price, preview_url, image, tech_stack, requirements, file_path, status, created_at) 
                      VALUES ($user_id, $category_id, '$name', '$slug', '$description', $price, '$preview_url', '$image', '$tech_stack', '$requirements', '$file_path', 'pending', NOW())";
            
            if ($conn->query($query)) {
                $success = true;
            } else {
                $error = 'Failed to add product. Please try again.';
            }
        }
    }
}
?>

<div class="bg-gray-100 py-12">
    <div class="container mx-auto px-4">
        <h1 class="text-3xl font-bold mb-8">Add New Product</h1>
        
        <?php if (!empty($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
                <span class="block sm:inline"><?php echo $error; ?></span>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert">
                <span class="block sm:inline">Product added successfully! It will be reviewed by our team before being published.</span>
                <div class="mt-2">
                    <a href="/account#products" class="text-green-700 underline">View your products</a>
                </div>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-6">
                    <form method="post" enctype="multipart/form-data">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Product Name*</label>
                                <input type="text" id="name" name="name" value="<?php echo isset($_POST['name']) ? $_POST['name'] : ''; ?>" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
                            </div>
                            
                            <div>
                                <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Category*</label>
                                <select id="category_id" name="category_id" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
                                    <option value="">Select a category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>" <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                            <?php echo $category['name']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="md:col-span-2">
                                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description*</label>
                                <textarea id="description" name="description" rows="5" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" required><?php echo isset($_POST['description']) ? $_POST['description'] : ''; ?></textarea>
                            </div>
                            
                            <div>
                                <label for="price" class="block text-sm font-medium text-gray-700 mb-1">Price (XAF)*</label>
                                <input type="number" id="price" name="price" value="<?php echo isset($_POST['price']) ? $_POST['price'] : ''; ?>" min="100" step="100" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
                            </div>
                            
                            <div>
                                <label for="preview_url" class="block text-sm font-medium text-gray-700 mb-1">Preview URL (Optional)</label>
                                <input type="url" id="preview_url" name="preview_url" value="<?php echo isset($_POST['preview_url']) ? $_POST['preview_url'] : ''; ?>" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                <p class="text-sm text-gray-500 mt-1">URL where buyers can preview your product</p>
                            </div>
                            
                            <div>
                                <label for="tech_stack" class="block text-sm font-medium text-gray-700 mb-1">Technologies Used*</label>
                                <input type="text" id="tech_stack" name="tech_stack" value="<?php echo isset($_POST['tech_stack']) ? $_POST['tech_stack'] : ''; ?>" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
                                <p class="text-sm text-gray-500 mt-1">Comma-separated list (e.g., PHP, MySQL, Tailwind CSS)</p>
                            </div>
                            
                            <div>
                                <label for="requirements" class="block text-sm font-medium text-gray-700 mb-1">Requirements*</label>
                                <input type="text" id="requirements" name="requirements" value="<?php echo isset($_POST['requirements']) ? $_POST['requirements'] : ''; ?>" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
                                <p class="text-sm text-gray-500 mt-1">System requirements (e.g., PHP 8.0+, MySQL 5.7+)</p>
                            </div>
                            
                            <div>
                                <label for="image" class="block text-sm font-medium text-gray-700 mb-1">Product Image*</label>
                                <input type="file" id="image" name="image" accept="image/*" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
                                <p class="text-sm text-gray-500 mt-1">Recommended size: 800x600 pixels</p>
                            </div>
                            
                            <div>
                                <label for="file" class="block text-sm font-medium text-gray-700 mb-1">Product File*</label>
                                <input type="file" id="file" name="file" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
                                <p class="text-sm text-gray-500 mt-1">ZIP file containing your product</p>
                            </div>
                        </div>
                        
                        <div class="border-t border-gray-200 pt-6">
                            <div class="flex items-center mb-4">
                                <input type="checkbox" id="terms" name="terms" class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded" required>
                                <label for="terms" class="ml-2 block text-sm text-gray-700">
                                    I confirm that I own the rights to sell this product and it does not violate any copyright laws.
                                </label>
                            </div>
                        </div>
                        
                        <div class="flex justify-end space-x-4">
                            <a href="/account#products" class="bg-gray-200 text-gray-800 hover:bg-gray-300 py-2 px-6 rounded-md font-medium">
                                Cancel
                            </a>
                            <button type="submit" class="bg-primary text-white hover:bg-blue-800 py-2 px-6 rounded-md font-medium">
                                Add Product
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include dirname(__FILE__) . '/../../includes/footer.php'; ?>
