<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (empty($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../config/koneksi.php';
?>
