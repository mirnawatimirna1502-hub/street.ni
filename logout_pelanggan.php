<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
unset($_SESSION['pelanggan_id'],$_SESSION['pelanggan_nama'],$_SESSION['pelanggan_hp'],$_SESSION['pelanggan_email'],$_SESSION['pelanggan_redirect']);
// Jangan session_destroy(), karena sesi admin/pelanggan dapat berbagi cookie di browser.
header('Location: login_pelanggan.php'); exit;
?>
