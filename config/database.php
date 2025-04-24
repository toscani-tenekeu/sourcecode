<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Replace with your MySQL username
define('DB_PASS', 'root'); // Replace with your MySQL password
define('DB_NAME', 'sourcecode_db');

// Create database connection
function db_connect() {
    $connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Check connection
    if ($connection->connect_error) {
        die("Connection failed: " . $connection->connect_error);
    }
    
    return $connection;
}

// Get database connection
$conn = db_connect();
?>
