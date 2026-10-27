<?php
session_start();
if (!empty($_SESSION['admin_login'])) {
    header('Location: admin.php');
    exit;
}
$pesan = '';
if (isset($_GET['gagal'])) $pesan = 'Username atau password salah.';
if (isset($_GET['keluar'])) $pesan = 'Anda berhasil logout.';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login Admin | Street N.I</title>
<style>
*{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;padding:20px;background:linear-gradient(135deg,#24160f,#5a3828);font-family:Arial,sans-serif;color:#33251e}
.login-card{width:100%;max-width:410px;background:#fffaf4;padding:34px;border-radius:18px;box-shadow:0 18px 55px #0005}
.brand{text-align:center;font-size:13px;letter-spacing:3px;color:#936747;font-weight:bold}.brand-logo{width:140px;height:100px;max-width:100%;object-fit:contain;display:block;margin:8px auto 10px}
h1{text-align:center;font-size:25px;margin:8px 0}.sub{text-align:center;color:#796b61;margin:0 0 24px;font-size:14px}
label{display:block;font-size:14px;font-weight:bold;margin:14px 0 7px}
input{width:100%;padding:13px 14px;border:1px solid #d9c9bb;border-radius:9px;font-size:15px;background:white}
input:focus{outline:2px solid #b78a65;border-color:transparent}
button{width:100%;margin-top:22px;padding:13px;border:0;border-radius:9px;background:#583824;color:white;font-weight:bold;font-size:15px;cursor:pointer}
button:hover{background:#704a31}.msg{background:#ffebe7;color:#8a2c20;padding:11px;border-radius:8px;font-size:13px;margin-bottom:12px}.ok{background:#e7f5e9;color:#246332}
.back{text-align:center;margin-top:20px;font-size:13px}.back a{color:#704a31}
</style>
</head>
<body>
<div class="login-card">
  <div class="brand">STREET N.I</div>
  <img class="brand-logo" src="../assets/images/logo-street-ni.jpeg" alt="Logo Street N.I">
  <h1>Login Admin</h1><p class="sub">Masuk untuk mengelola pesanan kedai</p>
  <?php if ($pesan): ?><div class="msg <?= isset($_GET['keluar'])?'ok':'' ?>"><?= htmlspecialchars($pesan,ENT_QUOTES,'UTF-8') ?></div><?php endif; ?>
  <form action="proses_login.php" method="post">
    <label for="username">Username</label><input id="username" name="username" type="text" maxlength="50" required autocomplete="username">
    <label for="password">Password</label><input id="password" name="password" type="password" required autocomplete="current-password">
    <button type="submit">Masuk ke Dashboard</button>
  </form>
  <div class="back"><a href="../index.php">← Kembali ke website</a></div>
</div>
</body>
</html>