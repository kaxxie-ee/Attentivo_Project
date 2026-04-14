<?php
/**
 * Database Configuration for Attentivo
 */
$host = 'localhost';
$dbname = 'attention_tracker';
$username = 'root';
$password = 'pedrigalako12';

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


// Set charset
$conn->set_charset("utf8mb4");
?>
