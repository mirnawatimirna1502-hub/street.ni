<?php
session_start(); require_once __DIR__ . '/config/koneksi.php'; require_once __DIR__ . '/config/config_google.php'; require_once __DIR__ . '/includes/auth_pelanggan_helper.php';
if ($_SERVER['REQUEST_METHOD']!=='POST' || !auth_check_csrf($_POST['csrf_token']??null)) { header('Location: login_pelanggan.php?error=google'); exit; }
if (GOOGLE_CLIENT_ID === 'GANTI_DENGAN_GOOGLE_CLIENT_ID.apps.googleusercontent.com') { header('Location: login_pelanggan.php?error=config'); exit; }
$credential=$_POST['credential']??'';
if (!is_string($credential) || $credential==='' || strlen($credential)>10000 || !function_exists('curl_init')) { header('Location: login_pelanggan.php?error=google'); exit; }
// Validasi token di sisi server. Untuk produksi, pertimbangkan library resmi Google API Client.
$ch=curl_init('https://oauth2.googleapis.com/tokeninfo?id_token='.rawurlencode($credential));
curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_CONNECTTIMEOUT=>5,CURLOPT_TIMEOUT=>10,CURLOPT_SSL_VERIFYPEER=>true]);
$body=curl_exec($ch); $status=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE); curl_close($ch);
$data=is_string($body)?json_decode($body,true):null;
if ($status!==200 || !is_array($data) || !hash_equals(GOOGLE_CLIENT_ID,(string)($data['aud']??'')) || (string)($data['email_verified']??'')!=='true' || empty($data['sub']) || empty($data['email'])) { header('Location: login_pelanggan.php?error=google'); exit; }
$googleId=(string)$data['sub']; $email=strtolower(trim((string)$data['email'])); $nama=trim((string)($data['name']??$data['given_name']??'Pelanggan Google'));
// Prioritaskan pengaitan lewat google_sub. Email unik mencegah akun ganda untuk email yang sama.
$stmt=mysqli_prepare($koneksi,'SELECT id_pelanggan,nama,no_hp FROM pelanggan WHERE google_sub=? OR email=? LIMIT 1');
if (!$stmt) { http_response_code(500); exit('Tabel pelanggan belum siap. Jalankan database_login_pelanggan.sql.'); }
mysqli_stmt_bind_param($stmt,'ss',$googleId,$email); mysqli_stmt_execute($stmt); $res=mysqli_stmt_get_result($stmt); $user=mysqli_fetch_assoc($res); mysqli_stmt_close($stmt);
if ($user) {
 $stmt=mysqli_prepare($koneksi, "UPDATE pelanggan SET google_sub=?, email=COALESCE(NULLIF(email,''),?) WHERE id_pelanggan=?"); mysqli_stmt_bind_param($stmt,'ssi',$googleId,$email,$user['id_pelanggan']); mysqli_stmt_execute($stmt); mysqli_stmt_close($stmt);
} else {
 $stmt=mysqli_prepare($koneksi,'INSERT INTO pelanggan (nama,email,google_sub) VALUES (?,?,?)'); mysqli_stmt_bind_param($stmt,'sss',$nama,$email,$googleId); $ok=mysqli_stmt_execute($stmt); $id=mysqli_insert_id($koneksi); mysqli_stmt_close($stmt);
 if (!$ok) { header('Location: login_pelanggan.php?error=google'); exit; }
 $user=['id_pelanggan'=>$id,'nama'=>$nama,'no_hp'=>''];
}
session_regenerate_id(true); $_SESSION['pelanggan_id']=(int)$user['id_pelanggan']; $_SESSION['pelanggan_nama']=$user['nama'] ?: $nama; $_SESSION['pelanggan_hp']=$user['no_hp'] ?? ''; $_SESSION['pelanggan_email']=$email; auth_redirect_after_login();
?>
