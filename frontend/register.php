<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join the Arena - G-Arena</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/gaming-theme.css">
    <style>
        /* Gaming Background Pattern */
        .bg-gaming-pattern {
            background: linear-gradient(135deg, #18122B 0%, #393053 50%, #18122B 100%);
            background-size: 400% 400%;
            animation: gradientShift 8s ease infinite;
        }
        
        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }
        
        .registration-glow {
            box-shadow: 
                0 0 30px rgba(138, 43, 226, 0.5),
                0 0 60px rgba(138, 43, 226, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body class="bg-gaming-pattern min-h-screen py-12 px-4 sm:px-6 lg:px-8 relative">
    <!-- Animated Background Particles -->
    <div class="particles">
        <div class="particle" style="left: 5%; animation-delay: 0s; animation-duration: 7s;"></div>
        <div class="particle" style="left: 15%; animation-delay: 1.5s; animation-duration: 9s;"></div>
        <div class="particle" style="left: 25%; animation-delay: 3s; animation-duration: 6s;"></div>
        <div class="particle" style="left: 35%; animation-delay: 0.8s; animation-duration: 8s;"></div>
        <div class="particle" style="left: 45%; animation-delay: 2.2s; animation-duration: 7s;"></div>
        <div class="particle" style="left: 55%; animation-delay: 1.8s; animation-duration: 9s;"></div>
        <div class="particle" style="left: 65%; animation-delay: 3.5s; animation-duration: 6s;"></div>
        <div class="particle" style="left: 75%; animation-delay: 0.3s; animation-duration: 8s;"></div>
        <div class="particle" style="left: 85%; animation-delay: 2.8s; animation-duration: 7s;"></div>
        <div class="particle" style="left: 95%; animation-delay: 1.2s; animation-duration: 9s;"></div>
    </div>

    <!-- Main Content Container -->
    <div class="flex items-center justify-center min-h-screen">
        <div class="max-w-md w-full space-y-8 relative z-10 page-transition">
        <!-- Header Section -->
        <div class="text-center">
            <div class="mx-auto h-24 w-24 flex items-center justify-center rounded-full bg-gradient-to-br from-purple-600 to-purple-800 registration-glow mb-6">
                <img src="../logo/logo.png" alt="G-Arena Logo" class="h-14 w-auto">
            </div>
            <h2 class="text-4xl font-bold neon-text mb-2">
                Join the Arena
            </h2>
            <p class="text-gaming-light text-lg mb-4">
                Begin your gaming journey today
            </p>
            <p class="text-center text-sm text-gaming-light">
                Already have an account?
                <a href="login.php" class="font-semibold text-purple-400 hover:text-purple-300 transition-colors duration-300 underline decoration-purple-500">
                    Launch in now
                </a>
            </p>
        </div>
        
        <!-- Registration Form Card -->
        <div class="gaming-card p-8">
            <form class="space-y-6" id="userRegisterForm">
                <div class="space-y-4">
                    <div class="relative">
                        <label for="username" class="block text-sm font-medium text-gaming-light mb-2">
                            <i class="fas fa-user mr-2"></i>Gamer Tag
                        </label>
                        <input id="username" name="username" type="text" required 
                               class="gaming-input w-full text-white placeholder-purple-300" 
                               placeholder="Choose your gamer tag">
                    </div>
                    
                    <div class="relative">
                        <label for="email" class="block text-sm font-medium text-gaming-light mb-2">
                            <i class="fas fa-envelope mr-2"></i>Email Address
                        </label>
                        <input id="email" name="email" type="email" required 
                               class="gaming-input w-full text-white placeholder-purple-300" 
                               placeholder="your@email.com">
                    </div>
                    
                    <div class="relative">
                        <label for="full_name" class="block text-sm font-medium text-gaming-light mb-2">
                            <i class="fas fa-id-card mr-2"></i>Full Name
                        </label>
                        <input id="full_name" name="full_name" type="text" required 
                               class="gaming-input w-full text-white placeholder-purple-300" 
                               placeholder="Your real name">
                    </div>
                    
                    <div class="relative">
                        <label for="phone" class="block text-sm font-medium text-gaming-light mb-2">
                            <i class="fas fa-phone mr-2"></i>Phone Number (Optional)
                        </label>
                        <input id="phone" name="phone" type="tel" 
                               class="gaming-input w-full text-white placeholder-purple-300" 
                               placeholder="Your contact number">
                    </div>
                    
                    <div class="relative">
                        <label for="password" class="block text-sm font-medium text-gaming-light mb-2">
                            <i class="fas fa-lock mr-2"></i>Password
                        </label>
                        <input id="password" name="password" type="password" required 
                               class="gaming-input w-full text-white placeholder-purple-300" 
                               placeholder="Create a strong password">
                        <p class="text-xs text-purple-300 mt-1">
                            <i class="fas fa-info-circle mr-1"></i>
                            Minimum 6 characters for ultimate security
                        </p>
                    </div>
                </div>

                <!-- Alert Messages -->
                <div id="errorMessage" class="hidden gaming-alert border-red-500 bg-red-900/50 text-red-200 px-4 py-3 rounded-lg">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <span></span>
                </div>
                <div id="successMessage" class="hidden gaming-alert border-green-500 bg-green-900/50 text-green-200 px-4 py-3 rounded-lg">
                    <i class="fas fa-check-circle mr-2"></i>
                    <span></span>
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit" class="gaming-btn w-full group relative overflow-hidden">
                        <span class="relative z-10 flex items-center justify-center">
                            <i class="fas fa-user-plus mr-2 group-hover:animate-pulse"></i>
                            Create My Arena Account
                        </span>
                    </button>
                </div>
            </form>
            
            <!-- Additional Links -->
            <div class="mt-6 pt-6 border-t border-purple-600/30">
                <div class="text-center space-y-3">
                    <a href="../index2.php" class="block text-purple-400 hover:text-purple-300 transition-colors duration-300">
                        <i class="fas fa-home mr-2"></i>Back to Home
                    </a>
                    <div class="text-xs text-gaming-light">
                        <i class="fas fa-shield-alt mr-1"></i>
                        Your data is secure with us
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div> <!-- End flex container -->

    <script>
        document.getElementById('userRegisterForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = {
                username: document.getElementById('username').value,
                email: document.getElementById('email').value,
                full_name: document.getElementById('full_name').value,
                phone: document.getElementById('phone').value,
                password: document.getElementById('password').value
            };
            
            const errorDiv = document.getElementById('errorMessage');
            const successDiv = document.getElementById('successMessage');
            
            // Hide previous messages
            errorDiv.classList.add('hidden');
            successDiv.classList.add('hidden');
            
            // Basic validation
            if (formData.password.length < 6) {
                errorDiv.querySelector('span').textContent = 'Password must be at least 6 characters long';
                errorDiv.classList.remove('hidden');
                return;
            }
            
            try {
                const response = await fetch('../backend/api/user_register.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(formData)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    successDiv.querySelector('span').textContent = data.message + ' Redirecting to login...';
                    successDiv.classList.remove('hidden');
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 2000);
                } else {
                    errorDiv.querySelector('span').textContent = data.message;
                    errorDiv.classList.remove('hidden');
                }
            } catch (error) {
                errorDiv.querySelector('span').textContent = 'Registration failed. Please try again.';
                errorDiv.classList.remove('hidden');
            }
        });
    </script>
</body>
</html>
