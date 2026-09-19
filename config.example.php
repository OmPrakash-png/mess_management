<?php
/**
 * config.example.php
 * Copy this file to config.php and fill in your database credentials.
 * DO NOT commit config.php — it is gitignored.
 */
session_start();

$host   = 'localhost';
$dbname = 'mess_db';
$user   = 'root';
$pass   = '';          // your MySQL password (blank for XAMPP default)

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die('<h3 style="color:red;font-family:sans-serif">DB Connection Failed: ' . $conn->connect_error . '</h3>');
}
$conn->set_charset('utf8mb4');
