<?php
/**
 * PawMate - Vercel Serverless Front Controller & Router
 * Mengarahkan setiap request di Vercel ke file PHP yang sesuai
 */

// Ubah working directory ke root proyek agar include file berjalan normal
chdir(dirname(__DIR__));

$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($requestUri, PHP_URL_PATH);
$path = trim($path, '/');

// Routing halaman default
if ($path === '' || $path === 'index.html') {
    require __DIR__ . '/../index.php';
    exit;
}

// Cek apakah file yang diminta ada secara langsung
$baseDir = dirname(__DIR__);
$target = $baseDir . '/' . $path;

if (file_exists($target) && is_file($target)) {
    // Jika file PHP
    if (substr($target, -4) === '.php') {
        require $target;
        exit;
    }
    
    // Jika file statis (CSS, JS, gambar)
    $ext = pathinfo($target, PATHINFO_EXTENSION);
    $mimeMap = [
        'css' => 'text/css',
        'js' => 'application/javascript',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'ico' => 'image/x-icon',
        'json' => 'application/json'
    ];
    if (isset($mimeMap[$ext])) {
        header("Content-Type: " . $mimeMap[$ext]);
    }
    readfile($target);
    exit;
}

// Cek jika diakses tanpa ekstensi .php (contoh: /animals atau /login)
if (file_exists($target . '.php') && is_file($target . '.php')) {
    require $target . '.php';
    exit;
}

// Cek di subfolder api/ jika ada
if (file_exists(__DIR__ . '/' . $path) && is_file(__DIR__ . '/' . $path)) {
    require __DIR__ . '/' . $path;
    exit;
}

// Fallback ke beranda
require __DIR__ . '/../index.php';
