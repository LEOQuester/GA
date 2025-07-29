<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login - G-Arena</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/gaming-theme.css">
    <style>
        /* Custom Tailwind config */
        .bg-gaming-pattern {
            background: linear-gradient(135deg, #18122B 0%, #393053 50%, #18122B 100%);
            background-size: 400% 400%;
            animation: gradientShift 8s ease infinite;
        }

        @keyframes gradientShift {

            0%,
            100% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }
        }

        .login-glow {
            box-shadow:
                0 0 20px rgba(138, 43, 226, 0.4),
                0 0 40px rgba(138, 43, 226, 0.2),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
        }
    </style>
</head>

<body class="bg-gaming-pattern min-h-screen py-12 px-4 sm:px-6 lg:px-8 relative">
    <!-- Animated Background Particles -->
    <div class="particles">
        <div class="particle" style="left: 10%; animation-delay: 0s; animation-duration: 6s;"></div>
        <div class="particle" style="left: 20%; animation-delay: 1s; animation-duration: 8s;"></div>
        <div class="particle" style="left: 30%; animation-delay: 2s; animation-duration: 5s;"></div>
        <div class="particle" style="left: 40%; animation-delay: 0.5s; animation-duration: 7s;"></div>
        <div class="particle" style="left: 50%; animation-delay: 1.5s; animation-duration: 6s;"></div>
        <div class="particle" style="left: 60%; animation-delay: 2.5s; animation-duration: 8s;"></div>
        <div class="particle" style="left: 70%; animation-delay: 0.8s; animation-duration: 5s;"></div>
        <div class="particle" style="left: 80%; animation-delay: 2.2s; animation-duration: 7s;"></div>
        <div class="particle" style="left: 90%; animation-delay: 1.8s; animation-duration: 6s;"></div>
    </div>

    <!-- Main Content Container -->
    <div class="flex items-center justify-center min-h-screen">
        <div class="max-w-md w-full space-y-8 relative z-10 page-transition">
            <!-- Header Section -->
            <div class="text-center">
                <div class="mx-auto h-20 w-20 flex items-center justify-center rounded-full bg-gradient-to-br from-purple-600 to-purple-800 login-glow mb-6">
                    <img src="../logo/logo.png" alt="G-Arena Logo" class="h-12 w-auto">
                </div>
                <h2 class="text-4xl font-bold neon-text mb-2">
                    Welcome Back, Gamer
                </h2>
                <p class="text-gaming-light text-lg">
                    Ready to level up your experience?
                </p>
                <p class="mt-4 text-center text-sm text-gaming-light">
                    New to the arena?
                    <a href="register.php" class="font-semibold text-purple-400 hover:text-purple-300 transition-colors duration-300 underline decoration-purple-500">
                        Resigter
                    </a>
                </p>
            </div>

            <!-- Login Form Card -->
            <div class="gaming-card p-8">
                <form class="space-y-6" id="userLoginForm">
                    <div class="space-y-4">
                        <div class="relative">
                            <label for="username" class="block text-sm font-medium text-gaming-light mb-2">
                                <i class="fas fa-user mr-2"></i>Username or Email
                            </label>
                            <input id="username" name="username" type="text" required
                                class="gaming-input w-full text-white placeholder-purple-300"
                                placeholder="Enter your username or email">
                        </div>
                        <div class="relative">
                            <label for="password" class="block text-sm font-medium text-gaming-light mb-2">
                                <i class="fas fa-lock mr-2"></i>Password
                            </label>
                            <input id="password" name="password" type="password" required
                                class="gaming-input w-full text-white placeholder-purple-300"
                                placeholder="Enter your password">
                            <div class="text-right mt-2">
                                <a href="#" id="forgotPasswordLink" class="text-sm text-purple-400 hover:text-purple-300 transition-colors duration-300">
                                    <i class="fas fa-question-circle mr-1"></i>Forgot password?
                                </a>
                            </div>
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
                                <i class="fas fa-rocket mr-2 group-hover:animate-bounce"></i>
                                Launch Into Arena
                            </span>
                        </button>
                    </div>
                </form>

                <!-- Additional Links -->
                <div class="mt-6 pt-6 border-t border-purple-600/30">
                    <div class="text-center space-y-3">
                        <a href="../index.php" class="block text-purple-400 hover:text-purple-300 transition-colors duration-300">
                            <i class="fas fa-home mr-2"></i>Back to Home
                        </a>
                        <a href="admin_login.php" class="block text-purple-400 hover:text-purple-300 transition-colors duration-300 text-sm">
                            <i class="fas fa-cog mr-2"></i>Admin Access
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div> <!-- End flex container -->

    <!-- Forgot Password Modal -->
    <div id="forgotPasswordModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="gaming-card max-w-md w-full mx-auto p-4">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-2xl font-bold neon-text">
                    <i class="fas fa-key mr-2"></i>Reset Password
                </h3>
                <button id="closeModal" class="text-gaming-light hover:text-white transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form id="forgotPasswordForm" class="space-y-4">
                <div>
                    <label for="resetEmail" class="block text-sm font-medium text-gaming-light mb-2">
                        <i class="fas fa-envelope mr-2"></i>Email Address
                    </label>
                    <input id="resetEmail" name="email" type="email" required
                        class="gaming-input w-full text-white placeholder-purple-300"
                        placeholder="Enter your email address">
                </div>

                <div>
                    <label for="confirmResetEmail" class="block text-sm font-medium text-gaming-light mb-2">
                        <i class="fas fa-envelope-open mr-2"></i>Confirm Email Address
                    </label>
                    <input id="confirmResetEmail" name="confirmEmail" type="email" required
                        class="gaming-input w-full text-white placeholder-purple-300"
                        placeholder="Confirm your email address">
                </div>

                <!-- Modal Alert Messages -->
                <div id="modalErrorMessage" class="hidden gaming-alert border-red-500 bg-red-900/50 text-red-200 px-4 py-3 rounded-lg">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <span></span>
                </div>
                <div id="modalSuccessMessage" class="hidden gaming-alert border-green-500 bg-green-900/50 text-green-200 px-4 py-3 rounded-lg">
                    <i class="fas fa-check-circle mr-2"></i>
                    <span></span>
                </div>

                <div class="flex space-x-3">
                    <button type="button" id="cancelReset" class="flex-1 px-4 py-2 border border-purple-600 text-purple-400 rounded-lg hover:bg-purple-600/20 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 gaming-btn">
                        <i class="fas fa-paper-plane mr-2"></i>Send Reset Link
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('userLoginForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            const errorDiv = document.getElementById('errorMessage');
            const successDiv = document.getElementById('successMessage');

            // Hide previous messages
            errorDiv.classList.add('hidden');
            successDiv.classList.add('hidden');

            try {
                const response = await fetch('../backend/api/user_login.php', {
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

        // Check for logout success message
        window.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const logout = urlParams.get('logout');

            if (logout === 'success') {
                const successDiv = document.getElementById('successMessage');
                successDiv.querySelector('span').textContent = 'Logged out successfully! Ready for your next gaming session?';
                successDiv.classList.remove('hidden');

                // Auto-hide the message after 5 seconds
                setTimeout(() => {
                    successDiv.classList.add('hidden');
                }, 5000);

                // Clean up the URL without refreshing the page
                const url = new URL(window.location);
                url.searchParams.delete('logout');
                window.history.replaceState({}, document.title, url);
            }
        });

        // Forgot password modal handlers
        const forgotPasswordModal = document.getElementById('forgotPasswordModal');
        const forgotPasswordLink = document.getElementById('forgotPasswordLink');
        const closeModal = document.getElementById('closeModal');
        const cancelReset = document.getElementById('cancelReset');

        // Open modal
        forgotPasswordLink.addEventListener('click', function(e) {
            e.preventDefault();
            forgotPasswordModal.classList.remove('hidden');
            document.getElementById('resetEmail').focus();
        });

        // Close modal function
        function closeModalFunction() {
            forgotPasswordModal.classList.add('hidden');
            document.getElementById('forgotPasswordForm').reset();
            document.getElementById('modalErrorMessage').classList.add('hidden');
            document.getElementById('modalSuccessMessage').classList.add('hidden');
        }

        closeModal.addEventListener('click', closeModalFunction);
        cancelReset.addEventListener('click', closeModalFunction);

        // Close modal when clicking outside
        forgotPasswordModal.addEventListener('click', function(e) {
            if (e.target === forgotPasswordModal) {
                closeModalFunction();
            }
        });

        // Forgot password form handler
        document.getElementById('forgotPasswordForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const email = document.getElementById('resetEmail').value;
            const confirmEmail = document.getElementById('confirmResetEmail').value;

            const modalErrorDiv = document.getElementById('modalErrorMessage');
            const modalSuccessDiv = document.getElementById('modalSuccessMessage');

            // Hide previous messages
            modalErrorDiv.classList.add('hidden');
            modalSuccessDiv.classList.add('hidden');

            // Validate emails match
            if (email !== confirmEmail) {
                modalErrorDiv.querySelector('span').textContent = 'Email addresses do not match';
                modalErrorDiv.classList.remove('hidden');
                return;
            }

            // Validate email format
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                modalErrorDiv.querySelector('span').textContent = 'Please enter a valid email address';
                modalErrorDiv.classList.remove('hidden');
                return;
            }

            try {
                const response = await fetch('../backend/api/forgot_password.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        email: email
                    })
                });

                const data = await response.json();

                if (data.success) {
                    modalSuccessDiv.querySelector('span').textContent = data.message;
                    modalSuccessDiv.classList.remove('hidden');
                    setTimeout(() => {
                        closeModalFunction();
                    }, 3000);
                } else {
                    modalErrorDiv.querySelector('span').textContent = data.message;
                    modalErrorDiv.classList.remove('hidden');
                }
            } catch (error) {
                modalErrorDiv.querySelector('span').textContent = 'Failed to send reset email. Please try again.';
                modalErrorDiv.classList.remove('hidden');
            }
        });
    </script>
</body>

</html>