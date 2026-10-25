<?php
session_start(); require_once __DIR__ . '/config/koneksi.php'; require_once __DIR__ . '/includes/auth_pelanggan_helper.php';
if (!empty($_SESSION['pelanggan_id'])) { header('Location: index.php'); exit; }
$error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
 if (!auth_check_csrf($_POST['csrf_token']??null)) $error='Sesi formulir kedaluwarsa. Muat ulang halaman.';
 else {
  $nama=trim($_POST['nama']??''); $hp=preg_replace('/[\s()-]/','',trim($_POST['no_hp']??'')); $password=$_POST['password']??'';
  if ($nama==='' || strlen($nama)>100 || !preg_match('/^\+?[0-9]{8,16}$/',$hp) || strlen($password)<8) $error='Isi nama, nomor HP yang valid, dan kata sandi minimal 8 karakter.';
  else {
   $stmt=mysqli_prepare($koneksi,'SELECT id_pelanggan FROM pelanggan WHERE no_hp=? LIMIT 1'); mysqli_stmt_bind_param($stmt,'s',$hp); mysqli_stmt_execute($stmt); $res=mysqli_stmt_get_result($stmt); $exists=mysqli_fetch_assoc($res); mysqli_stmt_close($stmt);
   if ($exists) $error='Nomor HP sudah terdaftar. Silakan login.';
   else { $hash=password_hash($password,PASSWORD_DEFAULT); $stmt=mysqli_prepare($koneksi,'INSERT INTO pelanggan (nama,no_hp,password_hash) VALUES (?,?,?)'); mysqli_stmt_bind_param($stmt,'sss',$nama,$hp,$hash); $ok=mysqli_stmt_execute($stmt); $id=mysqli_insert_id($koneksi); mysqli_stmt_close($stmt);
    if ($ok) { session_regenerate_id(true); $_SESSION['pelanggan_id']=$id; $_SESSION['pelanggan_nama']=$nama; $_SESSION['pelanggan_hp']=$hp; auth_redirect_after_login(); } else $error='Pendaftaran gagal. Coba lagi.';
   }
  }
 }
}
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Daftar Pelanggan | Street N.I</title><style>*{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;padding:20px;background:linear-gradient(135deg,#2b1b14,#795238);font-family:Arial,sans-serif;color:#302017}.card{width:100%;max-width:430px;background:#fffaf4;border-radius:18px;padding:30px;box-shadow:0 15px 45px #0003}.brand{text-align:center;font-weight:800;letter-spacing:3px;color:#795238}h1{text-align:center}.sub{text-align:center;color:#78675d;font-size:14px}label{display:block;margin:14px 0 6px;font-size:13px;font-weight:bold}input{width:100%;padding:12px;border:1px solid #d9c9bb;border-radius:8px}button{width:100%;margin-top:20px;padding:13px;border:0;border-radius:8px;background:#583824;color:white;font-weight:bold;cursor:pointer}.msg{background:#ffebe7;color:#8a2c20;padding:11px;border-radius:8px;font-size:13px}.foot{text-align:center;font-size:13px;margin-top:20px}.foot a{color:#704a31}</style></head><body><main class="card"><div class="brand">STREET N.I</div><h1>Daftar Pelanggan</h1><p class="sub">Buat akun untuk mulai memesan.</p><?php if($error): ?><p class="msg"><?= auth_h($error) ?></p><?php endif; ?><form method="post"><input type="hidden" name="csrf_token" value="<?= auth_h(auth_csrf()) ?>"><label for="nama">Nama lengkap</label><input id="nama" name="nama" maxlength="100" required value="<?= auth_h($_POST['nama']??'') ?>"><label for="hp">Nomor HP</label><input id="hp" name="no_hp" type="tel" maxlength="20" placeholder="081234567890" required value="<?= auth_h($_POST['no_hp']??'') ?>"><label for="password">Kata sandi (minimal 8 karakter)</label><input id="password" name="password" type="password" minlength="8" required autocomplete="new-password"><button type="submit">Buat Akun</button></form><p class="foot">Sudah punya akun? <a href="login_pelanggan.php">Login</a></p></main></body></html>
