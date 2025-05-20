<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_NAME', 'blog');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
if ($conn->query($sql) === TRUE) {
    $conn->select_db(DB_NAME);
    $conn->set_charset("utf8");
    $conn->query("SET NAMES 'utf8'");
    $conn->query("SET CHARACTER SET 'utf8'");
} else {
    die("Error creating database: " . $conn->error);
}
