<?php
/**
 * PawMate - Authentication & Session Helper
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/database.php';

function isLoggedIn() {
    return isset($_SESSION['user']) && !empty($_SESSION['user']['id']);
}

function getCurrentUser() {
    return $_SESSION['user'] ?? null;
}

function isAdmin() {
    $user = getCurrentUser();
    return $user && isset($user['role']) && $user['role'] === 'admin';
}

function requireLogin($redirect = 'login.php') {
    if (!isLoggedIn()) {
        header("Location: $redirect");
        exit;
    }
}

function requireAdmin($redirect = 'login.php') {
    if (!isAdmin()) {
        header("Location: $redirect");
        exit;
    }
}

function loginUser($user) {
    // Hilangkan password dari session untuk keamanan
    unset($user['password']);
    $_SESSION['user'] = $user;
}

function logoutUser() {
    $_SESSION['user'] = null;
    unset($_SESSION['user']);
    session_destroy();
}

function verifyUserPassword($inputPassword, $storedHashOrPlain) {
    if (password_verify($inputPassword, $storedHashOrPlain)) {
        return true;
    }
    // Fallback untuk password demo tanpa hash
    return $inputPassword === $storedHashOrPlain;
}

function setFlash($type, $title, $text = '') {
    $_SESSION['flash'] = [
        'type' => $type,
        'title' => $title,
        'text' => $text
    ];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
