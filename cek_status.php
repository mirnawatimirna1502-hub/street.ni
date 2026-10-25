<?php
require __DIR__ . '/config/koneksi.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    http_response_code(400);
    echo json_encode(['error' => 'ID pesanan tidak valid']);
    exit;
}

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT status_pesanan
     FROM pesanan
     WHERE id_pesanan = ?"
);

mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $status);

if (mysqli_stmt_fetch($stmt)) {
    echo json_encode(['status' => $status]);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Pesanan tidak ditemukan']);
}

mysqli_stmt_close($stmt);
mysqli_close($koneksi);
?>
