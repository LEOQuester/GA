<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - G-Arena Gaming Center</title>
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
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }
        
        .reset-glow {
            box-shadow: 
                0 0 20px rgba(34, 197, 94, 0.4),
                0 0 40px rgba(34, 197, 94, 0.2),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
        }

        .password-strength-weak { color: #ef4444; }
        .password-strength-fair { color: #f59e0b; }
        .password-strength-good { color: #3b82f6; }
        .password-strength-strong { color: #10b981; }
    </style>
</head>
<body class="bg-gaming-pattern min-h-screen py-12 px-4 sm:px-6 lg:px-8 relative">
    <!-- Animated Background Particles -->
    <div class="particles">
        <div class="particle" style="left: 15%; animation-delay: 0s; animation-duration: 6s;"></div>
        <div class="particle" style="left: 25%; animation-delay: 1s; animation-duration: 8s;"></div>
        <div class="particle" style="left: 35%; animation-delay: 2s; animation-duration: 5s;"></div>
        <div class="particle" style="left: 45%; animation-delay: 0.5s; animation-duration: 7s;"></div>
        <div class="particle" style="left: 55%; animation-delay: 1.5s; animation-duration: 6s;"></div>
        <div class="particle" style="left: 65%; animation-delay: 2.5s; animation-duration: 8s;"></div>
        <div class="particle" style="left: 75%; animation-delay: 0.8s; animation-duration: 5s;"></div>
        <div class="particle" style="left: 85%; animation-delay: 2.2s; animation-duration: 7s;"></div>
    </div>

    <!-- Main Content Container -->
    <div class="flex items-center justify-center min-h-screen">
        <div class="max-w-md w-full space-y-8 relative z-10 page-transition">
            <!-- Header -->
            <div class="text-center">
                <div class="mx-auto h-24 w-24 flex items-center justify-center rounded-full bg-gradient-to-br from-green-600 to-green-800 reset-glow mb-6">
                    <img src="../logo/logo.png" alt="G-Arena Logo" class="h-14 w-auto">
                </div>
                <h2 class="text-4xl font-bold neon-text mb-2">
                    <i class="fas fa-key mr-3"></i>Reset Password
                </h2>
                <p class="text-gaming-light text-lg mb-4">
                    Create a new secure password for your arena account
                </p>
                <div class="flex items-center justify-center space-x-2 text-green-400">
                    <i class="fas fa-shield-alt"></i>
                    <span class="text-sm">Secure Password Reset</span>
                </div>
            </div>

            <!-- Reset Form Card -->
            <div class="gaming-card p-8" id="resetForm">
                <form class="space-y-6" id="passwordResetForm">
                    <input type="hidden" id="resetToken" name="token" value="">
                    
                    <div class="space-y-4">
                        <div class="relative">
                            <label for="newPassword" class="block text-sm font-medium text-gaming-light mb-2">
                                <i class="fas fa-lock mr-2"></i>New Password
                            </label>
                            <div class="relative">
                                <input id="newPassword" name="password" type="password" required 
                                       class="gaming-input w-full text-white placeholder-purple-300 pr-12" 
                                       placeholder="Enter your new password">
                                <button type="button" id="togglePassword1" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gaming-light hover:text-white">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div id="passwordStrength" class="mt-2 text-xs"></div>
                            <p class="text-xs text-purple-300 mt-1">
                                <i class="fas fa-info-circle mr-1"></i>
                                Minimum 6 characters. Use letters, numbers, and symbols for maximum security
                            </p>
                        </div>
                        
                        <div class="relative">
                            <label for="confirmPassword" class="block text-sm font-medium text-gaming-light mb-2">
                                <i class="fas fa-check-double mr-2"></i>Confirm New Password
                            </label>
                            <div class="relative">
                                <input id="confirmPassword" name="confirmPassword" type="password" required 
                                       class="gaming-input w-full text-white placeholder-purple-300 pr-12" 
                                       placeholder="Confirm your new password">
                                <button type="button" id="togglePassword2" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gaming-light hover:text-white">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div id="passwordMatch" class="mt-2 text-xs"></div>
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
                                <i class="fas fa-shield-alt mr-2 group-hover:animate-pulse"></i>
                                Update My Password
                            </span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Success Card (hidden initially) -->
            <div class="gaming-card p-8 hidden" id="successCard">
                <div class="text-center">
                    <div class="mx-auto h-20 w-20 flex items-center justify-center rounded-full bg-gradient-to-br from-green-500 to-green-700 mb-6 animate-pulse">
                        <i class="fas fa-check text-3xl text-white"></i>
                    </div>
                    <h3 class="text-3xl font-bold text-green-400 mb-3">
                        <i class="fas fa-trophy mr-2"></i>Password Updated!
                    </h3>
                    <p class="text-gaming-light text-lg mb-6">
                        Your password has been successfully updated. You're ready to jump back into the arena!
                    </p>
                    <div class="space-y-3">
                        <a href="login.php" class="gaming-btn inline-block w-full">
                            <i class="fas fa-gamepad mr-2"></i>Launch Into Arena
                        </a>
                        <p class="text-sm text-purple-300">
                            <i class="fas fa-info-circle mr-1"></i>
                            Use your new password to login
                        </p>
                    </div>
                </div>
            </div>

            <!-- Error Card (hidden initially) -->
            <div class="gaming-card p-8 hidden" id="errorCard">
                <div class="text-center">
                    <div class="mx-auto h-20 w-20 flex items-center justify-center rounded-full bg-gradient-to-br from-red-500 to-red-700 mb-6">
                        <i class="fas fa-exclamation-triangle text-3xl text-white"></i>
                    </div>
                    <h3 class="text-3xl font-bold text-red-400 mb-3">
                        <i class="fas fa-clock mr-2"></i>Link Expired
                    </h3>
                    <p class="text-gaming-light text-lg mb-6" id="errorCardMessage">
                        This password reset link has expired or is invalid. No worries - you can request a new one!
                    </p>
                    <div class="space-y-3">
                        <a href="login.php" class="gaming-btn inline-block w-full">
                            <i class="fas fa-arrow-left mr-2"></i>Back to Login
                        </a>
                        <p class="text-sm text-purple-300">
                            <i class="fas fa-lightbulb mr-1"></i>
                            Click "Forgot password?" to get a new reset link
                        </p>
                    </div>
                </div>
            </div>

            <!-- Additional Links -->
            <div class="text-center pt-6 border-t border-purple-600/30">
                <a href="login.php" class="text-purple-400 hover:text-purple-300 transition-colors duration-300">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Login
                </a>
            </div>
        </div>
    </div>

    <script>
        // Get token from URL
        const urlParams = new URLSearchParams(window.location.search);
        const token = urlParams.get('token');
        
        if (!token) {
            // No token provided, show error
            document.getElementById('resetForm').classList.add('hidden');
            document.getElementById('errorCard').classList.remove('hidden');
        } else {
            // Set token in hidden field
            document.getElementById('resetToken').value = token;
            
            // Verify token when page loads
            verifyToken(token);
        }

        async function verifyToken(token) {
            try {
                const response = await fetch('../backend/api/verify_reset_token.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ token: token })
                });
                
                const data = await response.json();
                
                if (!data.success) {
                    document.getElementById('resetForm').classList.add('hidden');
                    document.getElementById('errorCard').classList.remove('hidden');
                    document.getElementById('errorCardMessage').textContent = data.message;
                }
            } catch (error) {
                document.getElementById('resetForm').classList.add('hidden');
                document.getElementById('errorCard').classList.remove('hidden');
                document.getElementById('errorCardMessage').textContent = 'Unable to verify reset link. Please try again.';
            }
        }

        // Password visibility toggles
        document.getElementById('togglePassword1').addEventListener('click', function() {
            const passwordField = document.getElementById('newPassword');
            const icon = this.querySelector('i');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });

        document.getElementById('togglePassword2').addEventListener('click', function() {
            const passwordField = document.getElementById('confirmPassword');
            const icon = this.querySelector('i');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });

        // Enhanced password strength indicator
        document.getElementById('newPassword').addEventListener('input', function(e) {
            const password = e.target.value;
            const strengthDiv = document.getElementById('passwordStrength');
            
            if (password.length === 0) {
                strengthDiv.innerHTML = '';
                return;
            }
            
            let strength = 0;
            let strengthText = '';
            let strengthColor = '';
            let requirements = [];
            
            // Check requirements
            if (password.length >= 6) {
                strength++;
                requirements.push('✓ 6+ characters');
            } else {
                requirements.push('✗ 6+ characters');
            }
            
            if (password.match(/[a-z]/)) {
                strength++;
                requirements.push('✓ Lowercase letter');
            } else {
                requirements.push('✗ Lowercase letter');
            }
            
            if (password.match(/[A-Z]/)) {
                strength++;
                requirements.push('✓ Uppercase letter');
            } else {
                requirements.push('✗ Uppercase letter');
            }
            
            if (password.match(/[0-9]/)) {
                strength++;
                requirements.push('✓ Number');
            } else {
                requirements.push('✗ Number');
            }
            
            if (password.match(/[^a-zA-Z0-9]/)) {
                strength++;
                requirements.push('✓ Special character');
            } else {
                requirements.push('✗ Special character');
            }
            
            // Determine strength level
            switch (strength) {
                case 0:
                case 1:
                    strengthText = 'Very Weak';
                    strengthColor = 'password-strength-weak';
                    break;
                case 2:
                    strengthText = 'Weak';
                    strengthColor = 'password-strength-weak';
                    break;
                case 3:
                    strengthText = 'Fair';
                    strengthColor = 'password-strength-fair';
                    break;
                case 4:
                    strengthText = 'Good';
                    strengthColor = 'password-strength-good';
                    break;
                case 5:
                    strengthText = 'Strong';
                    strengthColor = 'password-strength-strong';
                    break;
            }
            
            strengthDiv.className = `mt-2 text-xs ${strengthColor}`;
            strengthDiv.innerHTML = `
                <div class="flex items-center mb-1">
                    <i class="fas fa-shield-alt mr-2"></i>
                    <span>Strength: ${strengthText}</span>
                </div>
                <div class="text-xs text-gaming-light">
                    ${requirements.join(' • ')}
                </div>
            `;
        });

        // Real-time password match validation
        document.getElementById('confirmPassword').addEventListener('input', function(e) {
            const password = document.getElementById('newPassword').value;
            const confirmPassword = e.target.value;
            const matchDiv = document.getElementById('passwordMatch');
            
            if (confirmPassword.length === 0) {
                matchDiv.innerHTML = '';
                e.target.classList.remove('border-red-500', 'border-green-500');
                return;
            }
            
            if (password === confirmPassword) {
                matchDiv.innerHTML = '<span class="text-green-400"><i class="fas fa-check mr-1"></i>Passwords match</span>';
                e.target.classList.remove('border-red-500');
                e.target.classList.add('border-green-500');
            } else {
                matchDiv.innerHTML = '<span class="text-red-400"><i class="fas fa-times mr-1"></i>Passwords do not match</span>';
                e.target.classList.remove('border-green-500');
                e.target.classList.add('border-red-500');
            }
        });

        // Password reset form handler
        document.getElementById('passwordResetForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const token = document.getElementById('resetToken').value;
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            const errorDiv = document.getElementById('errorMessage');
            const successDiv = document.getElementById('successMessage');
            const submitBtn = e.target.querySelector('button[type="submit"]');
            
            // Hide previous messages
            errorDiv.classList.add('hidden');
            successDiv.classList.add('hidden');
            
            // Disable submit button
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="relative z-10 flex items-center justify-center"><i class="fas fa-spinner animate-spin mr-2"></i>Updating Password...</span>';
            
            // Validate passwords
            if (newPassword.length < 6) {
                errorDiv.querySelector('span').textContent = 'Password must be at least 6 characters long';
                errorDiv.classList.remove('hidden');
                resetSubmitButton(submitBtn);
                return;
            }
            
            if (newPassword !== confirmPassword) {
                errorDiv.querySelector('span').textContent = 'Passwords do not match';
                errorDiv.classList.remove('hidden');
                resetSubmitButton(submitBtn);
                return;
            }
            
            try {
                const response = await fetch('../backend/api/reset_password.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        token: token,
                        password: newPassword,
                        confirmPassword: confirmPassword
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Hide form and show success card
                    document.getElementById('resetForm').classList.add('hidden');
                    document.getElementById('successCard').classList.remove('hidden');
                } else {
                    errorDiv.querySelector('span').textContent = data.message;
                    errorDiv.classList.remove('hidden');
                    resetSubmitButton(submitBtn);
                }
            } catch (error) {
                errorDiv.querySelector('span').textContent = 'Failed to reset password. Please try again.';
                errorDiv.classList.remove('hidden');
                resetSubmitButton(submitBtn);
            }
        });

        function resetSubmitButton(btn) {
            btn.disabled = false;
            btn.innerHTML = '<span class="relative z-10 flex items-center justify-center"><i class="fas fa-shield-alt mr-2"></i>Update My Password</span>';
        }
    </script>
</body>
</html>
