<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>G-Arena - Gaming Arena Booking System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .hero-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card-hover:hover {
            transform: translateY(-5px);
            transition: all 0.3s ease;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <h1 class="text-2xl font-bold text-purple-600">
                            <i class="fas fa-gamepad mr-2"></i>G-Arena
                        </h1>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="frontend/login.php" class="text-gray-600 hover:text-purple-600 px-3 py-2 rounded-md text-sm font-medium">
                        <i class="fas fa-sign-in-alt mr-1"></i>User Login
                    </a>
                    <a href="frontend/register.php" class="bg-purple-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-purple-700">
                        <i class="fas fa-user-plus mr-1"></i>Register
                    </a>
                    <a href="frontend/admin_login.php" class="text-gray-600 hover:text-purple-600 px-3 py-2 rounded-md text-sm font-medium">
                        <i class="fas fa-user-shield mr-1"></i>Admin
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-bg text-white py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-5xl font-bold mb-6">Welcome to G-Arena</h2>
            <p class="text-xl mb-8 max-w-3xl mx-auto">
                Experience the ultimate gaming environment with our state-of-the-art gaming stations. 
                Book your gaming session today and immerse yourself in an unparalleled gaming experience.
            </p>
            <div class="space-x-4">
                <a href="frontend/register.php" class="bg-white text-purple-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition duration-300">
                    Get Started
                </a>
                <a href="#stations" class="border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-purple-600 transition duration-300">
                    View Stations
                </a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h3 class="text-3xl font-bold text-gray-900 mb-4">Why Choose G-Arena?</h3>
                <p class="text-lg text-gray-600">We provide the best gaming experience with premium equipment and services</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center p-6 rounded-lg card-hover">
                    <div class="bg-purple-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-desktop text-purple-600 text-2xl"></i>
                    </div>
                    <h4 class="text-xl font-semibold mb-2">High-End Gaming PCs</h4>
                    <p class="text-gray-600">Latest hardware with RTX graphics cards and high-refresh rate monitors</p>
                </div>
                
                <div class="text-center p-6 rounded-lg card-hover">
                    <div class="bg-purple-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-calendar-check text-purple-600 text-2xl"></i>
                    </div>
                    <h4 class="text-xl font-semibold mb-2">Easy Booking</h4>
                    <p class="text-gray-600">Simple and intuitive booking system for hassle-free reservations</p>
                </div>
                
                <div class="text-center p-6 rounded-lg card-hover">
                    <div class="bg-purple-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-headset text-purple-600 text-2xl"></i>
                    </div>
                    <h4 class="text-xl font-semibold mb-2">24/7 Support</h4>
                    <p class="text-gray-600">Round-the-clock technical support and assistance</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Gaming Stations Preview -->
    <section id="stations" class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h3 class="text-3xl font-bold text-gray-900 mb-4">Our Gaming Stations</h3>
                <p class="text-lg text-gray-600">Choose from our variety of gaming setups</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="bg-white rounded-lg shadow-md overflow-hidden card-hover">
                    <div class="h-48 bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center">
                        <i class="fas fa-desktop text-white text-6xl"></i>
                    </div>
                    <div class="p-6">
                        <h4 class="text-xl font-semibold mb-2">PC Gaming Stations</h4>
                        <p class="text-gray-600 mb-4">High-performance gaming PCs with latest graphics cards</p>
                        <div class="flex justify-between items-center">
                            <span class="text-2xl font-bold text-purple-600">$12-15/hr</span>
                            <span class="text-green-600 font-semibold">Available</span>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-md overflow-hidden card-hover">
                    <div class="h-48 bg-gradient-to-r from-green-500 to-blue-600 flex items-center justify-center">
                        <i class="fas fa-gamepad text-white text-6xl"></i>
                    </div>
                    <div class="p-6">
                        <h4 class="text-xl font-semibold mb-2">Console Gaming</h4>
                        <p class="text-gray-600 mb-4">PlayStation 5 and Xbox Series X with 4K displays</p>
                        <div class="flex justify-between items-center">
                            <span class="text-2xl font-bold text-purple-600">$10/hr</span>
                            <span class="text-green-600 font-semibold">Available</span>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-md overflow-hidden card-hover">
                    <div class="h-48 bg-gradient-to-r from-purple-500 to-pink-600 flex items-center justify-center">
                        <i class="fas fa-vr-cardboard text-white text-6xl"></i>
                    </div>
                    <div class="p-6">
                        <h4 class="text-xl font-semibold mb-2">VR Gaming</h4>
                        <p class="text-gray-600 mb-4">Immersive VR experience with Meta Quest 3</p>
                        <div class="flex justify-between items-center">
                            <span class="text-2xl font-bold text-purple-600">$20/hr</span>
                            <span class="text-green-600 font-semibold">Available</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-12">
                <a href="frontend/register.php" class="bg-purple-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-purple-700 transition duration-300">
                    Book Now
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h5 class="text-lg font-semibold mb-4">G-Arena</h5>
                    <p class="text-gray-400">Your ultimate gaming destination with state-of-the-art equipment and premium gaming experience.</p>
                </div>
                
                <div>
                    <h5 class="text-lg font-semibold mb-4">Quick Links</h5>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="frontend/login.php" class="hover:text-white">Login</a></li>
                        <li><a href="frontend/register.php" class="hover:text-white">Register</a></li>
                        <li><a href="#stations" class="hover:text-white">Gaming Stations</a></li>
                    </ul>
                </div>
                
                <div>
                    <h5 class="text-lg font-semibold mb-4">Contact Info</h5>
                    <ul class="space-y-2 text-gray-400">
                        <li><i class="fas fa-phone mr-2"></i>+1 (555) 123-4567</li>
                        <li><i class="fas fa-envelope mr-2"></i>info@g-arena.com</li>
                        <li><i class="fas fa-map-marker-alt mr-2"></i>123 Gaming St, City</li>
                    </ul>
                </div>
                
                <div>
                    <h5 class="text-lg font-semibold mb-4">Follow Us</h5>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-facebook text-xl"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-twitter text-xl"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-instagram text-xl"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-discord text-xl"></i></a>
                    </div>
                </div>
            </div>
            
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; 2025 G-Arena. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>
