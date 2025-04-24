<?php include 'includes/header.php'; ?>

<!-- Hero Section -->
<section class="bg-primary text-white py-20">
    <div class="container mx-auto px-4 text-center">
        <h1 class="text-4xl md:text-5xl font-bold mb-6">Premium Source Code & Templates</h1>
        <p class="text-xl mb-8">Accelerate your development with high-quality, ready-to-use source code and templates</p>
        <div class="flex flex-col md:flex-row justify-center gap-4">
            <a href="/category/complete-projects" class="bg-white text-primary hover:bg-gray-100 font-semibold py-3 px-6 rounded-md">
                <i class="fas fa-code mr-2"></i> Browse Projects
            </a>
            <a href="/category/templates" class="bg-transparent hover:bg-blue-800 border-2 border-white font-semibold py-3 px-6 rounded-md">
                <i class="fas fa-palette mr-2"></i> View Templates
            </a>
        </div>
    </div>
</section>

<!-- Featured Products -->
<section class="py-16">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold mb-8 text-center">Featured Projects</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php
            $featured_products = get_featured_products(6);
            foreach ($featured_products as $product):
            ?>
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
        </div>
        <div class="text-center mt-10">
            <a href="/category/complete-projects" class="bg-primary text-white hover:bg-blue-800 font-semibold py-3 px-6 rounded-md">
                View All Products
            </a>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="py-16 bg-gray-100">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold mb-12 text-center">Our Categories</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-8">
                    <h3 class="text-2xl font-semibold mb-4">Complete Projects</h3>
                    <p class="text-gray-600 mb-6">Full-stack projects with frontend and backend integration. Ready to deploy and customize for your business needs.</p>
                    <div class="flex flex-wrap gap-2 mb-6">
                        <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm">PHP</span>
                        <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm">MySQL</span>
                        <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm">JavaScript</span>
                        <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm">Tailwind CSS</span>
                    </div>
                    <a href="/category/complete-projects" class="bg-primary text-white hover:bg-blue-800 py-2 px-4 rounded-md inline-block">
                        Explore Projects
                    </a>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-8">
                    <h3 class="text-2xl font-semibold mb-4">Templates</h3>
                    <p class="text-gray-600 mb-6">Beautiful frontend templates for various technologies. Perfect for kickstarting your web project with stunning designs.</p>
                    <div class="flex flex-wrap gap-2 mb-6">
                        <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm">HTML</span>
                        <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm">CSS</span>
                        <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm">Tailwind</span>
                        <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm">React</span>
                        <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm">Next.js</span>
                    </div>
                    <a href="/category/templates" class="bg-primary text-white hover:bg-blue-800 py-2 px-4 rounded-md inline-block">
                        Browse Templates
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Benefits Section -->
<section class="py-16">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold mb-12 text-center">Why Choose Our Products</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="text-center">
                <div class="inline-block p-4 bg-blue-100 rounded-full mb-4">
                    <i class="fas fa-rocket text-primary text-3xl"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2">Save Development Time</h3>
                <p class="text-gray-600">Launch your projects faster by starting with our ready-to-use code.</p>
            </div>
            <div class="text-center">
                <div class="inline-block p-4 bg-blue-100 rounded-full mb-4">
                    <i class="fas fa-code text-primary text-3xl"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2">Clean & Modern Code</h3>
                <p class="text-gray-600">Well-structured, documented code that follows best practices.</p>
            </div>
            <div class="text-center">
                <div class="inline-block p-4 bg-blue-100 rounded-full mb-4">
                    <i class="fas fa-laptop-code text-primary text-3xl"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2">Easy to Customize</h3>
                <p class="text-gray-600">Easily adapt our code to fit your specific project requirements.</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-16 bg-gray-800 text-white">
    <div class="container mx-auto px-4 text-center">
        <h2 class="text-3xl font-bold mb-6">Ready to accelerate your development?</h2>
        <p class="text-xl mb-8">Browse our collection of high-quality source code and templates today.</p>
        <a href="/category/complete-projects" class="bg-primary hover:bg-blue-800 text-white font-semibold py-3 px-8 rounded-md text-lg">
            Get Started Now
        </a>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
