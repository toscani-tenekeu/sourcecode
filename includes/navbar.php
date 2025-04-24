<nav class="bg-primary shadow-md">
    <div class="container mx-auto px-4">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="flex-shrink-0 flex items-center">
                    <a href="/" class="text-white text-xl font-bold">SourceCode</a>
                </div>
                <div class="hidden md:ml-6 md:flex md:space-x-4 md:items-center">
                    <a href="/" class="text-white hover:bg-blue-800 px-3 py-2 rounded-md text-sm font-medium">Accueil</a>
                    <a href="/category/complete-projects" class="text-white hover:bg-blue-800 px-3 py-2 rounded-md text-sm font-medium">Projets Complets</a>
                    <a href="/category/templates" class="text-white hover:bg-blue-800 px-3 py-2 rounded-md text-sm font-medium">Templates</a>
                </div>
            </div>
            <div class="hidden md:flex md:items-center md:space-x-4">
                <!-- Language Switcher -->
                <div class="relative">
                    <button id="language-switcher" class="text-white hover:bg-blue-800 px-3 py-2 rounded-md text-sm font-medium flex items-center">
                        <i class="fas fa-globe mr-1"></i> FR <i class="fas fa-chevron-down ml-1 text-xs"></i>
                    </button>
                    <div id="language-dropdown" class="hidden absolute right-0 mt-2 w-24 bg-white rounded-md shadow-lg z-10">
                        <a href="?lang=fr" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Français</a>
                        <a href="?lang=en" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">English</a>
                    </div>
                </div>
                
                <?php if (is_logged_in()): ?>
                    <a href="/account" class="text-white hover:bg-blue-800 px-3 py-2 rounded-md text-sm font-medium">Mon Compte</a>
                    <a href="/logout" class="text-white hover:bg-blue-800 px-3 py-2 rounded-md text-sm font-medium">Déconnexion</a>
                <?php else: ?>
                    <a href="/login" class="text-white hover:bg-blue-800 px-3 py-2 rounded-md text-sm font-medium">Connexion</a>
                    <a href="/register" class="bg-white text-blue-900 hover:bg-gray-100 px-3 py-2 rounded-md text-sm font-medium">Inscription</a>
                <?php endif; ?>
            </div>
            
            <!-- Mobile menu button -->
            <div class="flex items-center md:hidden">
                <button id="mobile-menu-button" type="button" class="text-white hover:bg-blue-800 p-2 rounded-md">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Mobile menu -->
    <div id="mobile-menu" class="hidden md:hidden">
        <div class="px-2 pt-2 pb-3 space-y-1">
            <a href="/" class="text-white hover:bg-blue-800 block px-3 py-2 rounded-md text-base font-medium">Accueil</a>
            <a href="/category/complete-projects" class="text-white hover:bg-blue-800 block px-3 py-2 rounded-md text-base font-medium">Projets Complets</a>
            <a href="/category/templates" class="text-white hover:bg-blue-800 block px-3 py-2 rounded-md text-base font-medium">Templates</a>
            
            <!-- Language Switcher (Mobile) -->
            <div class="flex justify-between items-center text-white hover:bg-blue-800 px-3 py-2 rounded-md text-base font-medium">
                <span>Langue</span>
                <div class="flex space-x-2">
                    <a href="?lang=fr" class="px-2 py-1 rounded <?php echo ($_SESSION['lang'] ?? 'fr') === 'fr' ? 'bg-blue-700' : ''; ?>">FR</a>
                    <a href="?lang=en" class="px-2 py-1 rounded <?php echo ($_SESSION['lang'] ?? 'fr') === 'en' ? 'bg-blue-700' : ''; ?>">EN</a>
                </div>
            </div>
            
            <?php if (is_logged_in()): ?>
                <a href="/account" class="text-white hover:bg-blue-800 block px-3 py-2 rounded-md text-base font-medium">Mon Compte</a>
                <a href="/logout" class="text-white hover:bg-blue-800 block px-3 py-2 rounded-md text-base font-medium">Déconnexion</a>
            <?php else: ?>
                <a href="/login" class="text-white hover:bg-blue-800 block px-3 py-2 rounded-md text-base font-medium">Connexion</a>
                <a href="/register" class="text-white hover:bg-blue-800 block px-3 py-2 rounded-md text-base font-medium">Inscription</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<script>
    // Toggle mobile menu
    document.getElementById('mobile-menu-button').addEventListener('click', function() {
        const mobileMenu = document.getElementById('mobile-menu');
        mobileMenu.classList.toggle('hidden');
    });
    
    // Toggle language dropdown
    document.getElementById('language-switcher').addEventListener('click', function() {
        const dropdown = document.getElementById('language-dropdown');
        dropdown.classList.toggle('hidden');
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const dropdown = document.getElementById('language-dropdown');
        const switcher = document.getElementById('language-switcher');
        
        if (!dropdown.contains(event.target) && !switcher.contains(event.target)) {
            dropdown.classList.add('hidden');
        }
    });
</script>
