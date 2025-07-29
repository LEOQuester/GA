<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Command Center - G-Arena</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/gaming-theme.css">
    <style>
        .admin-glow {
            box-shadow:
                0 0 30px rgba(255, 0, 102, 0.4),
                0 0 60px rgba(255, 0, 102, 0.2),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
        }

        .admin-theme {
            --admin-color: #FF0066;
            --admin-glow: rgba(255, 0, 102, 0.4);
        }
    </style>
</head>

<body class="bg-gaming-pattern min-h-screen flex items-center justify-center relative overflow-hidden admin-theme">
    <!-- Animated Background Particles -->
    <div class="particles">
        <div class="particle" style="left: 8%; animation-delay: 0s; animation-duration: 7s; background: #FF0066;"></div>
        <div class="particle" style="left: 18%; animation-delay: 1.2s; animation-duration: 9s; background: #8A2BE2;"></div>
        <div class="particle" style="left: 28%; animation-delay: 2.4s; animation-duration: 6s; background: #FF0066;"></div>
        <div class="particle" style="left: 38%; animation-delay: 0.7s; animation-duration: 8s; background: #8A2BE2;"></div>
        <div class="particle" style="left: 48%; animation-delay: 1.9s; animation-duration: 7s; background: #FF0066;"></div>
        <div class="particle" style="left: 58%; animation-delay: 2.7s; animation-duration: 9s; background: #8A2BE2;"></div>
        <div class="particle" style="left: 68%; animation-delay: 0.4s; animation-duration: 6s; background: #FF0066;"></div>
        <div class="particle" style="left: 78%; animation-delay: 2.1s; animation-duration: 8s; background: #8A2BE2;"></div>
        <div class="particle" style="left: 88%; animation-delay: 1.6s; animation-duration: 7s; background: #FF0066;"></div>
    </div>

    <div class="max-w-md w-full space-y-8 relative z-10 page-transition">
        <!-- Header Section -->
        <div class="text-center">
            <div class="mx-auto h-24 w-24 flex items-center justify-center rounded-full bg-gradient-to-br from-red-600 to-pink-600 admin-glow mb-6">
                <img src="../logo/logo.png" alt="G-Arena Logo" class="h-14 w-auto">
            </div>
            <h2 class="text-4xl font-bold mb-2" style="color: white; text-shadow: 0 0 10px #FF0066, 0 0 20px #FF0066;">
                Admin Command Center
            </h2>
            <p class="text-gaming-light text-lg mb-4">
                Authorized personnel only
            </p>
            <p class="text-center text-sm text-gaming-light">
                <a href="../index.php" class="font-semibold text-red-400 hover:text-red-300 transition-colors duration-300 underline decoration-red-500">
                    <i class="fas fa-arrow-left mr-1"></i>Return to Arena
                </a>
            </p>
        </div>

        <!-- Admin Login Form Card -->
        <div class="gaming-card p-8" style="border-color: #FF0066; box-shadow: 0 0 20px rgba(255, 0, 102, 0.4);">
            <form class="space-y-6" id="adminLoginForm">
                <div class="space-y-4">
                    <div class="relative">
                        <label for="username" class="block text-sm font-medium text-gaming-light mb-2">
                            <i class="fas fa-user-cog mr-2"></i>Admin Username
                        </label>
                        <input id="username" name="username" type="text" required
                            class="gaming-input w-full text-white placeholder-red-300"
                            placeholder="Enter your admin username"
                            style="border-color: rgba(255, 0, 102, 0.3);"
                            onfocus="this.style.borderColor='#FF0066'; this.style.boxShadow='0 0 15px rgba(255, 0, 102, 0.4)'"
                            onblur="this.style.borderColor='rgba(255, 0, 102, 0.3)'; this.style.boxShadow='none'"
                            </div>
                        <div class="relative">
                            <label for="password" class="block text-sm font-medium text-gaming-light mb-2">
                                <i class="fas fa-shield-alt mr-2"></i>Security Key
                            </label>
                            <input id="password" name="password" type="password" required
                                class="gaming-input w-full text-white placeholder-red-300"
                                placeholder="Enter your security key"
                                style="border-color: rgba(255, 0, 102, 0.3);"
                                onfocus="this.style.borderColor='#FF0066'; this.style.boxShadow='0 0 15px rgba(255, 0, 102, 0.4)'"
                                onblur="this.style.borderColor='rgba(255, 0, 102, 0.3)'; this.style.boxShadow='none'"
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
                            <button type="submit" class="w-full group relative overflow-hidden"
                                style="background: linear-gradient(135deg, #FF0066 0%, #CC0052 100%); border: 2px solid #FF0066; color: white; padding: 12px 24px; border-radius: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; transition: all 0.3s ease; box-shadow: 0 0 20px rgba(255, 0, 102, 0.4);">
                                <span class="relative z-10 flex items-center justify-center">
                                    <i class="fas fa-key mr-2 group-hover:animate-spin"></i>
                                    Access Command Center
                                </span>
                            </button>
                        </div>
            </form>

            <!-- Additional Links -->
            <div class="mt-6 pt-6 border-t border-red-600/30">
                <div class="text-center space-y-3">
                    <a href="login.php" class="block text-red-400 hover:text-red-300 transition-colors duration-300">
                        <i class="fas fa-user mr-2"></i>User Login
                    </a>
                    <div class="text-xs text-gaming-light">
                        <i class="fas fa-shield-virus mr-1"></i>
                        Maximum security protocol active
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('adminLoginForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            const errorDiv = document.getElementById('errorMessage');
            const successDiv = document.getElementById('successMessage');

            // Hide previous messages
            errorDiv.classList.add('hidden');
            successDiv.classList.add('hidden');

            try {
                const response = await fetch('../backend/api/admin_login.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        username,
                        password
                    })
                });

                const data = await response.json();

                if (data.success) {
                    successDiv.querySelector('span').textContent = data.message;
                    successDiv.classList.remove('hidden');
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1000);
                } else {
                    errorDiv.querySelector('span').textContent = data.message;
                    errorDiv.classList.remove('hidden');
                }
            } catch (error) {
                errorDiv.querySelector('span').textContent = 'Connection error. Please try again.';
                errorDiv.classList.remove('hidden');
            }
        });
    </script>
</body>

</html>