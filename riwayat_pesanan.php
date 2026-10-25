<?php
require_once __DIR__ . '/config/koneksi.php';

function h($nilai) {
    return htmlspecialchars((string)$nilai, ENT_QUOTES, 'UTF-8');
}

$nomor_hp = trim($_GET['no_hp'] ?? '');
$pesanan = [];
$error = '';

if ($nomor_hp !== '') {
    $stmt = $koneksi->prepare(
        "SELECT * FROM pesanan WHERE no_hp = ? ORDER BY id_pesanan DESC"
    );

    if ($stmt) {
        $stmt->bind_param("s", $nomor_hp);
        $stmt->execute();

        $hasil = $stmt->get_result();

        while ($baris = $hasil->fetch_assoc()) {
            $pesanan[] = $baris;
        }

        $stmt->close();
    } else {
        $error = 'Riwayat gagal dimuat. Periksa struktur tabel pesanan.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Riwayat Pesanan - Street N.I</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #faf6f0;
            color: #352218;
            margin: 0;
            padding: 30px 15px;
        }

        .container {
            max-width: 850px;
            margin: 30px auto;
            background: white;
            padding: 30px;
            border: 1px solid #eadfd2;
            border-radius: 12px;
        }

        h1 {
            font-family: Georgia, serif;
            font-size: 38px;
        }

        input {
            padding: 12px;
            border: 1px solid #e5d5c5;
            border-radius: 6px;
            width: 60%;
            max-width: 300px;
        }

        button {
            padding: 12px 18px;
            background: #302017;
            color: white;
            border: 0;
            border-radius: 6px;
            cursor: pointer;
        }

        .pesanan {
            border: 1px solid #eadfd2;
            padding: 18px;
            border-radius: 8px;
            margin-top: 18px;
            overflow-wrap: anywhere;
        }

        .status {
            color: #9a633c;
            font-weight: bold;
        }

        a {
            color: #805637;
            text-decoration: none;
        }

        @media (max-width: 500px) {
            input {
                width: 90%;
                max-width: none;
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <p>STREET N.I · COFFEE &amp; TEA</p>

    <h1>Riwayat Pesanan</h1>

    <p>
        Masukkan nomor HP yang digunakan saat memesan
        untuk melihat riwayat pesananmu.
    </p>

    <form method="get" action="riwayat_pesanan.php">
        <input
            type="tel"
            name="no_hp"
            placeholder="Masukkan nomor HP"
            value="<?= h($nomor_hp) ?>"
            required
        >
        <button type="submit">Cari Pesanan</button>
    </form>

    <?php if ($error !== ''): ?>
        <p><?= h($error) ?></p>

    <?php elseif ($nomor_hp !== '' && empty($pesanan)): ?>
        <p>Pesanan tidak ditemukan. Periksa kembali nomor HP kamu.</p>

    <?php else: ?>
        <?php foreach ($pesanan as $item): ?>
            <div class="pesanan">
                <h3>
                    Pesanan #<?= h($item['id_pesanan'] ?? '-') ?>
                </h3>

                <p>
                    Nama:
                    <?= h($item['nama_pemesan'] ?? '-') ?>
                </p>

                <p>
                    Status:
                    <span class="status">
                        <?= h($item['status'] ?? $item['status_pesanan'] ?? 'Menunggu') ?>
                    </span>
                </p>

                <?php if (isset($item['total_harga'])): ?>
                    <p>
                        Total: Rp
                        <?= number_format(
                            (float)$item['total_harga'],
                            0, ',', '.'
                        ) ?>
                    </p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <p style="margin-top:25px">
        <a href="index.php#menu">← Kembali ke Menu</a>
    </p>
</div>

</body>
</html>
