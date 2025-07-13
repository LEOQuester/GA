<?php
// Debug script to test admin login and show hashed passwords
require_once 'backend/config/config.php';
require_once 'backend/config/database.php';

echo "<h2>Admin Debug Tool</h2>";

// Show all admins in database
echo "<h3>Current Admin Accounts:</h3>";
$sql = "SELECT id, username, email, password, created_at FROM admins";
$admins = fetchAllRows($sql);

if (empty($admins)) {
    echo "<p style='color: red;'>No admin accounts found! Use the secret registration page.</p>";
} else {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Password Hash</th><th>Created</th></tr>";
    
    foreach ($admins as $admin) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($admin['id']) . "</td>";
        echo "<td>" . htmlspecialchars($admin['username']) . "</td>";
        echo "<td>" . htmlspecialchars($admin['email']) . "</td>";
        echo "<td style='font-family: monospace; font-size: 10px;'>" . htmlspecialchars(substr($admin['password'], 0, 30)) . "...</td>";
        echo "<td>" . htmlspecialchars($admin['created_at']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Test password hashing
echo "<h3>Password Hash Test:</h3>";
$testPassword = "admin123";
$hash = password_hash($testPassword, PASSWORD_DEFAULT);
echo "<p><strong>Password:</strong> " . $testPassword . "</p>";
echo "<p><strong>Hash:</strong> " . $hash . "</p>";
echo "<p><strong>Verify Test:</strong> " . (password_verify($testPassword, $hash) ? "✅ SUCCESS" : "❌ FAILED") . "</p>";

echo "<br><hr>";
echo "<h3>Quick Actions:</h3>";
echo "<a href='frontend/secret_admin_register.php' style='background: #dc2626; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Create Admin Account</a> ";
echo "<a href='frontend/admin_login.php' style='background: #8b5cf6; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-left: 10px;'>Admin Login</a>";

echo "<br><br>";
echo "<p style='color: #dc2626; font-weight: bold;'>⚠️ DELETE THIS FILE AFTER SETUP IS COMPLETE!</p>";
?>
