<?php
// Pengaman halaman pelanggan. Login admin menggunakan sesi terpisah.
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
if (empty($_SESSION['pelanggan_id']) || (int)$_SESSION['pelanggan_id'] < 1) {
    $tujuan = basename($_SERVER['PHP_SELF'] ?? 'index.php');
    $_SESSION['pelanggan_redirect'] = $tujuan === 'proses_pesanan.php' ? 'index.php' : $tujuan;
    header('Location: login_pelanggan.php');
    exit;
}
?>
