<?php
session_start();
require __DIR__ . '/includes/cek_login_pelanggan.php';
require __DIR__ . '/config/koneksi.php';

// Pastikan formulir dikirim melalui POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Periksa token formulir.
$token = $_POST['token_pesanan'] ?? '';
$tokenSesi = $_SESSION['token_pesanan'] ?? '';

if (
    !is_string($token) ||
    !is_string($tokenSesi) ||
    $tokenSesi === '' ||
    !hash_equals($tokenSesi, $token)
) {
    http_response_code(409);
    exit(
        'Formulir sudah digunakan atau kedaluwarsa. ' .
        '<a href="index.php">Kembali ke menu</a>'
    );
}

// Ambil data pelanggan.
$nama = trim($_POST['nama_pemesan'] ?? '');
$hp = trim($_POST['no_hp'] ?? '');
$catatan = trim($_POST['catatan'] ?? '');
$qty = $_POST['qty'] ?? [];

if (
    $nama === '' ||
    $hp === '' ||
    strlen($nama) > 100 ||
    strlen($hp) > 20 ||
    !is_array($qty)
) {
    exit('Data pelanggan tidak valid. Silakan kembali ke menu.');
}

// Validasi produk dan hitung total dari database.
$itemPesanan = [];
$total = 0;

$stmtProduk = mysqli_prepare(
    $koneksi,
    "SELECT harga FROM produk WHERE id_produk = ?"
);

foreach ($qty as $id => $jumlah) {
    $id = filter_var($id, FILTER_VALIDATE_INT);
    $jumlah = filter_var($jumlah, FILTER_VALIDATE_INT);

    if (
        !$id ||
        $jumlah === false ||
        $jumlah < 0 ||
        $jumlah > 100
    ) {
        mysqli_stmt_close($stmtProduk);
        exit('Produk atau jumlah tidak valid.');
    }

    if ($jumlah === 0) {
        continue;
    }

    mysqli_stmt_bind_param($stmtProduk, 'i', $id);
    mysqli_stmt_execute($stmtProduk);
    mysqli_stmt_bind_result($stmtProduk, $hargaDB);

    if (!mysqli_stmt_fetch($stmtProduk)) {
        mysqli_stmt_free_result($stmtProduk);
        mysqli_stmt_close($stmtProduk);
        exit('Produk tidak ditemukan.');
    }

    mysqli_stmt_free_result($stmtProduk);

    $harga = (float) $hargaDB;
    $subtotal = $harga * $jumlah;
    $total += $subtotal;

    $itemPesanan[] = [
        'id_produk' => $id,
        'jumlah' => $jumlah,
        'harga' => $harga,
        'subtotal' => $subtotal
    ];
}

mysqli_stmt_close($stmtProduk);

if (empty($itemPesanan)) {
    exit('Pilih minimal satu minuman.');
}

// Mulai transaksi penyimpanan.
mysqli_begin_transaction($koneksi);

try {
    // Simpan pesanan utama.
    $stmtPesanan = mysqli_prepare(
        $koneksi,
        "INSERT INTO pesanan
        (id_pelanggan, nama_pemesan, no_hp, catatan, total_harga, status_pesanan)
        VALUES (?, ?, ?, ?, ?, 'Menunggu')"
    );

    mysqli_stmt_bind_param(
        $stmtPesanan,
        'isssd',
        $_SESSION['pelanggan_id'],
        $nama,
        $hp,
        $catatan,
        $total
    );

    mysqli_stmt_execute($stmtPesanan);
    $idPesanan = mysqli_insert_id($koneksi);
    mysqli_stmt_close($stmtPesanan);

    // Simpan rincian setiap produk.
    $stmtDetail = mysqli_prepare(
        $koneksi,
        "INSERT INTO detail_pesanan
        (id_pesanan, id_produk, jumlah, harga_satuan, subtotal)
        VALUES (?, ?, ?, ?, ?)"
    );

    foreach ($itemPesanan as $item) {
        mysqli_stmt_bind_param(
            $stmtDetail,
            'iiidd',
            $idPesanan,
            $item['id_produk'],
            $item['jumlah'],
            $item['harga'],
            $item['subtotal']
        );

        mysqli_stmt_execute($stmtDetail);
    }

    mysqli_stmt_close($stmtDetail);

    // Simpan semua data secara bersamaan.
    mysqli_commit($koneksi);

    // Catat riwayat pesanan di sesi pelanggan.
    if (!isset($_SESSION['riwayat_pesanan'])) {
        $_SESSION['riwayat_pesanan'] = [];
    }

    $_SESSION['riwayat_pesanan'][] = $idPesanan;

   // Buat token baru agar pelanggan bisa memesan kembali.
	$_SESSION['token_pesanan'] = bin2hex(random_bytes(32));

    mysqli_close($koneksi);

    // Alihkan ke halaman konfirmasi.
    header('Location: konfirmasi.php?id=' . $idPesanan);
    exit;

} catch (Throwable $e) {
    mysqli_rollback($koneksi);
    error_log($e->getMessage());

    http_response_code(500);
    exit(
        'Pesanan gagal disimpan. ' .
        '<a href="index.php">Kembali ke menu</a>'
    );
}
?>