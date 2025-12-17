<?php
// config.php - Kết nối database
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Thay bằng user MySQL của bạn
define('DB_PASS', ''); // Thay bằng password MySQL
define('DB_NAME', 'library_management');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Kết nối thất bại: " . $e->getMessage());
}