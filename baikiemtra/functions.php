<?php
// functions.php - Các hàm hỗ trợ
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getUserRole() {
    return $_SESSION['role'] ?? null;
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Kiểm tra quyền
function checkAdmin() {
    if (getUserRole() !== 'admin') {
        redirect('index.php');
    }
}

function checkManagerOrAdmin() {
    $role = getUserRole();
    if ($role !== 'admin' && $role !== 'manager') {
        redirect('index.php');
    }
}

function checkUser() {
    if (!isLoggedIn()) {
        redirect('login.php');
    }
}