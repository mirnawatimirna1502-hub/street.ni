<?php
require __DIR__ . '/cek_login.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin.php');
    exit;
}

$id = filter_var(
    $_POST['id_pesanan'] ?? '',
    FILTER_VALIDATE_INT
);

$status = $_POST['status'] ?? '';

$statusValid = [
    'Menunggu',
    'Sedang Dibuat',
    'Selesai'
];

if (!$id || !in_array($status, $statusValid, true)) {
    exit('Data status tidak valid.');
}

$stmt = mysqli_prepare(
    $koneksi,
    "UPDATE pesanan
     SET status_pesanan = ?
     WHERE id_pesanan = ?"
);

mysqli_stmt_bind_param($stmt, 'si', $status, $id);
mysqli_stmt_execute($stmt);

mysqli_stmt_close($stmt);
mysqli_close($koneksi);

header('Location: admin.php?berhasil=1');
exit;
?>
