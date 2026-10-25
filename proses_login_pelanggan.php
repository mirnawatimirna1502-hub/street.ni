<?php
session_start(); require_once __DIR__ . '/config/koneksi.php'; require_once __DIR__ . '/includes/auth_pelanggan_helper.php';
if ($_SERVER['REQUEST_METHOD']!=='POST') { header('Location: login_pelanggan.php'); exit; }
if (!auth_check_csrf($_POST['csrf_token']??null)) { header('Location: login_pelanggan.php?error=login'); exit; }
$hp=preg_replace('/[\s()-]/','',trim($_POST['no_hp']??'')); $password=$_POST['password']??'';
$stmt=mysqli_prepare($koneksi,'SELECT id_pelanggan,nama,no_hp,password_hash FROM pelanggan WHERE no_hp=? LIMIT 1');
if (!$stmt) { http_response_code(500); exit('Tabel pelanggan belum siap. Jalankan database_login_pelanggan.sql terlebih dahulu.'); }
mysqli_stmt_bind_param($stmt,'s',$hp); mysqli_stmt_execute($stmt); $res=mysqli_stmt_get_result($stmt); $user=mysqli_fetch_assoc($res); mysqli_stmt_close($stmt);
if (!$user || !password_verify($password,$user['password_hash'])) { header('Location: login_pelanggan.php?error=login'); exit; }
session_regenerate_id(true); $_SESSION['pelanggan_id']=(int)$user['id_pelanggan']; $_SESSION['pelanggan_nama']=$user['nama']; $_SESSION['pelanggan_hp']=$user['no_hp']; auth_redirect_after_login();
?>
