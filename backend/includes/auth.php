<?php
require_once __DIR__ . '/../config/database.php';

function adminLogin($username, $password) {
    // Escape the input to prevent SQL injection
    $username = escapeString($username);
    
    // Build and execute query
    $sql = "SELECT * FROM admins WHERE username = '$username'";
    $admin = fetchSingleRow($sql);
    
    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION[ADMIN_SESSION_NAME] = [
            'id' => $admin['id'],
            'username' => $admin['username'],
            'email' => $admin['email']
        ];
        return true;
    }
    
    return false;
}

function userLogin($username, $password) {
    // Escape the input to prevent SQL injection
    $username = escapeString($username);
    
    // Build and execute query (checking both username and email)
    $sql = "SELECT * FROM users WHERE (username = '$username' OR email = '$username') AND status = 'active'";
    $user = fetchSingleRow($sql);
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION[USER_SESSION_NAME] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'full_name' => $user['full_name']
        ];
        return true;
    }
    
    return false;
}

function userRegister($username, $email, $password, $full_name, $phone = '') {
    // Escape all inputs to prevent SQL injection
    $username = escapeString($username);
    $email = escapeString($email);
    $full_name = escapeString($full_name);
    $phone = escapeString($phone);
    
    // Check if username or email already exists
    $sql = "SELECT id FROM users WHERE username = '$username' OR email = '$email'";
    $existing = fetchSingleRow($sql);
    
    if ($existing) {
        return ['success' => false, 'message' => 'Username or email already exists'];
    }
    
    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $hashedPassword = escapeString($hashedPassword);
    
    // Insert new user
    $sql = "INSERT INTO users (username, email, password, full_name, phone) 
            VALUES ('$username', '$email', '$hashedPassword', '$full_name', '$phone')";
    
    $result = executeQuery($sql);
    
    if ($result) {
        return ['success' => true, 'message' => 'Registration successful'];
    } else {
        return ['success' => false, 'message' => 'Registration failed'];
    }
}

function isAdminLoggedIn() {
    return isset($_SESSION[ADMIN_SESSION_NAME]);
}

function isUserLoggedIn() {
    return isset($_SESSION[USER_SESSION_NAME]);
}

function getAdminInfo() {
    return $_SESSION[ADMIN_SESSION_NAME] ?? null;
}

function getUserInfo() {
    return $_SESSION[USER_SESSION_NAME] ?? null;
}

function logout($type = 'user') {
    if ($type === 'admin') {
        unset($_SESSION[ADMIN_SESSION_NAME]);
    } else {
        unset($_SESSION[USER_SESSION_NAME]);
    }
}

function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        header('Location: ../admin_login.php');
        exit;
    }
}

function requireUserLogin() {
    if (!isUserLoggedIn()) {
        header('Location: ../login.php');
        exit;
    }
}
?>
