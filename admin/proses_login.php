<?php
session_start();
require __DIR__ . '/../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    header('Location: login.php?gagal=1');
    exit;
}

$stmt = mysqli_prepare($koneksi, "SELECT id_admin, username, password FROM admin WHERE username = ? LIMIT 1");
if (!$stmt) {
    exit('Query login gagal. Pastikan tabel admin sudah dibuat melalui buat_admin.php.');
}
mysqli_stmt_bind_param($stmt, 's', $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$admin = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if ($admin && password_verify($password, $admin['password'])) {
    session_regenerate_id(true);
    $_SESSION['admin_login'] = true;
    $_SESSION['id_admin'] = (int)$admin['id_admin'];
    $_SESSION['username_admin'] = $admin['username'];
    header('Location: admin.php');
    exit;
}

header('Location: login.php?gagal=1');
exit;
