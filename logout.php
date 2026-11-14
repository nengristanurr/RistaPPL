<?php
/**
 * PawMate - Logout Handler
 */
require_once __DIR__ . '/config/auth.php';
logoutUser();
session_start();
setFlash('info', 'Berhasil Keluar', 'Anda telah keluar dari akun.');
header('Location: login.php');
exit;
