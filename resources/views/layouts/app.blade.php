<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'FolderIn') - Knowledge & Context Bookmark Manager</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Tailwind Dark Mode Config -->
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom Styles -->
    <style>
        [x-cloak] { display: none !important; }
        
        /* Smooth transitions for dark mode */
        body {
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        
        .dark-mode-transition {
            transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
        }
    </style>
    
    <!-- Dark Mode Script - Load before body -->
    <script>
        // Check for saved theme preference or default to 'light'
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    
    @stack('styles')
</head>
<body class="bg-gray-50 dark:bg-gray-900 min-h-screen flex flex-col dark-mode-transition">
    <!-- Navbar -->
    <nav class="bg-white dark:bg-gray-800 shadow-md sticky top-0 z-50 dark-mode-transition">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <!-- Logo & Brand -->
                <div class="flex items-center space-x-2">
                    <a href="{{ route('folders.index') }}" class="flex items-center space-x-2 text-2xl font-bold text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition">
                        <i class="fas fa-folder-open"></i>
                        <span>FolderIn</span>
                    </a>
                </div>
                
                <!-- Navigation Links -->
                <div class="hidden md:flex items-center space-x-6">
                    <a href="{{ route('folders.index') }}" class="text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 font-medium transition {{ request()->routeIs('folders.index') ? 'text-blue-600 dark:text-blue-400 font-semibold' : '' }}">
                        <i class="fas fa-th-large mr-1"></i> Dashboard
                    </a>
                    <a href="{{ route('resources.index') }}" class="text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 font-medium transition {{ request()->routeIs('resources.*') ? 'text-blue-600 dark:text-blue-400 font-semibold' : '' }}">
                        <i class="fas fa-bookmark mr-1"></i> All Resources
                    </a>
                    
                    <!-- Dark Mode Toggle -->
                    <button id="theme-toggle" class="text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 transition p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" title="Toggle Dark Mode">
                        <i class="fas fa-moon dark:hidden text-lg"></i>
                        <i class="fas fa-sun hidden dark:inline text-lg"></i>
                    </button>
                    
                    <a href="{{ route('folders.create') }}" class="bg-blue-600 dark:bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-700 dark:hover:bg-blue-600 transition shadow-sm">
                        <i class="fas fa-plus mr-1"></i> New Folder
                    </a>
                </div>
                
                <!-- Mobile Menu Button -->
                <div class="md:hidden flex items-center space-x-3">
                    <!-- Mobile Dark Mode Toggle -->
                    <button id="theme-toggle-mobile" class="text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 transition p-2" title="Toggle Dark Mode">
                        <i class="fas fa-moon dark:hidden text-lg"></i>
                        <i class="fas fa-sun hidden dark:inline text-lg"></i>
                    </button>
                    
                    <button id="mobile-menu-button" class="text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 focus:outline-none">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
            
            <!-- Mobile Menu -->
            <div id="mobile-menu" class="hidden md:hidden pb-4">
                <a href="{{ route('folders.index') }}" class="block py-2 text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 {{ request()->routeIs('folders.index') ? 'text-blue-600 dark:text-blue-400 font-semibold' : '' }}">
                    <i class="fas fa-th-large mr-1"></i> Dashboard
                </a>
                <a href="{{ route('resources.index') }}" class="block py-2 text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 {{ request()->routeIs('resources.*') ? 'text-blue-600 dark:text-blue-400 font-semibold' : '' }}">
                    <i class="fas fa-bookmark mr-1"></i> All Resources
                </a>
                <a href="{{ route('folders.create') }}" class="block py-2 text-blue-600 dark:text-blue-400 font-semibold">
                    <i class="fas fa-plus mr-1"></i> New Folder
                </a>
            </div>
        </div>
    </nav>
    
    <!-- Flash Messages -->
    @include('partials.flash-messages')
    
    <!-- Main Content -->
    <main class="flex-1 py-8">
        <div class="container mx-auto px-4">
            @yield('content')
        </div>
    </main>
    
    <!-- Footer -->
    <footer class="bg-white dark:bg-gray-800 border-t dark:border-gray-700 mt-auto dark-mode-transition">
        <div class="container mx-auto px-4 py-6">
            <div class="flex flex-col md:flex-row justify-between items-center text-gray-600 dark:text-gray-400 text-sm">
                <div class="mb-2 md:mb-0">
                    <p>&copy; {{ date('Y') }} FolderIn. Knowledge & Context Bookmark Manager.</p>
                </div>
                <div class="flex items-center space-x-4">
                    <span>Built with <i class="fas fa-heart text-red-500"></i> using Laravel {{ app()->version() }}</span>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Scripts -->
    <script>
        // Dark Mode Toggle Function
        function toggleDarkMode() {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.theme = 'light';
            } else {
                document.documentElement.classList.add('dark');
                localStorage.theme = 'dark';
            }
        }
        
        // Dark Mode Toggle Buttons
        document.getElementById('theme-toggle')?.addEventListener('click', toggleDarkMode);
        document.getElementById('theme-toggle-mobile')?.addEventListener('click', toggleDarkMode);
        
        // Mobile menu toggle
        document.getElementById('mobile-menu-button')?.addEventListener('click', function() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        });
        
        // Auto-hide flash messages after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert-auto-dismiss');
            alerts.forEach(function(alert) {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);
    </script>
    
    @stack('scripts')
</body>
</html>
