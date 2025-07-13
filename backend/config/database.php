<?php
require_once 'config.php';

// Global database connection variable
$connection = null;

// Function to establish database connection
function getDbConnection() {
    global $connection;
    
    if ($connection === null) {
        $connection = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
        
        if (!$connection) {
            die("Database connection failed: " . mysqli_connect_error());
        }
        
        // Set charset to UTF-8
        mysqli_set_charset($connection, "utf8");
    }
    
    return $connection;
}

// Function to execute a query and return result
function executeQuery($sql) {
    $conn = getDbConnection();
    $result = mysqli_query($conn, $sql);
    
    if (!$result) {
        die("Query failed: " . mysqli_error($conn));
    }
    
    return $result;
}

// Function to fetch single row as associative array
function fetchSingleRow($sql) {
    $result = executeQuery($sql);
    $row = mysqli_fetch_assoc($result);
    mysqli_free_result($result);
    return $row;
}

// Function to fetch all rows as associative array
function fetchAllRows($sql) {
    $result = executeQuery($sql);
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    mysqli_free_result($result);
    return $rows;
}

// Function to escape string for SQL injection prevention
function escapeString($string) {
    $conn = getDbConnection();
    return mysqli_real_escape_string($conn, $string);
}

// Function to get last inserted ID
function getLastInsertId() {
    $conn = getDbConnection();
    return mysqli_insert_id($conn);
}

// Function to close database connection
function closeDbConnection() {
    global $connection;
    if ($connection) {
        mysqli_close($connection);
        $connection = null;
    }
}
?>
