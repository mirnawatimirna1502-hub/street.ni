<?php
session_start();
require_once __DIR__ . '/config/koneksi.php';

function e($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function rupiah($value) {
    return 'Rp ' . number_format((float)$value, 0, ',', '.');
}

// Ambil ID pesanan dari URL, contoh: konfirmasi.php?id=7
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    http_response_code(400);
    exit('Nomor pesanan tidak valid. Silakan periksa kembali pesananmu.');
}

// Deteksi nama kolom agar lebih sesuai dengan struktur tabel database.
$kolom = [];
$hasilKolom = $koneksi->query("SHOW COLUMNS FROM pesanan");

if ($hasilKolom) {
    while ($baris = $hasilKolom->fetch_assoc()) {
        $kolom[] = $baris['Field'];
    }
}

function pilihKolom($daftar, $tersedia) {
    foreach ($daftar as $nama) {
        if (in_array($nama, $tersedia, true)) {
            return $nama;
        }
    }
    return null;
}

$kolomId = pilihKolom(
    ['id_pesanan', 'id'],
    $kolom
);

$kolomNama = pilihKolom(
    ['nama_pemesan', 'nama', 'nama_pelanggan'],
    $kolom
);

$kolomStatus = pilihKolom(
    ['status_pesanan', 'status'],
    $kolom
);

$kolomTotal = pilihKolom(
    ['total_harga', 'total', 'grand_total'],
    $kolom
);

if (!$kolomId || !$kolomStatus) {
    http_response_code(500);
    exit('Kolom ID atau status pesanan tidak ditemukan. Periksa struktur tabel pesanan.');
}

// Fungsi mengambil data pesanan terbaru.
function ambilPesanan($koneksi, $id, $kolomId) {
    $sql = "SELECT * FROM pesanan WHERE `$kolomId` = ? LIMIT 1";
    $stmt = $koneksi->prepare($sql);

    if (!$stmt) {
        return null;
    }

    $stmt->bind_param('i', $id);
    $stmt->execute();

    $hasil = $stmt->get_result();
    $data = $hasil->fetch_assoc();

    $stmt->close();

    return $data ?: null;
}

$pesanan = ambilPesanan($koneksi, $id, $kolomId);

if (!$pesanan) {
    http_response_code(404);
    exit('Pesanan tidak ditemukan.');
}

// Endpoint untuk pembaruan status otomatis melalui JavaScript.
if (isset($_GET['ajax']) && $_GET['ajax'] === 'status') {
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate');

    $dataTerbaru = ambilPesanan($koneksi, $id, $kolomId);

    if (!$dataTerbaru) {
        http_response_code(404);
        echo json_encode(['error' => 'Pesanan tidak ditemukan']);
        exit;
    }

    echo json_encode([
        'status' => (string)($dataTerbaru[$kolomStatus] ?? 'Menunggu')
    ]);
    exit;
}

$nama = $kolomNama
    ? ($pesanan[$kolomNama] ?? 'Pelanggan')
    : 'Pelanggan';

$status = $pesanan[$kolomStatus] ?? 'Menunggu';

$total = $kolomTotal
    ? ($pesanan[$kolomTotal] ?? 0)
    : null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Konfirmasi Pesanan | Street N.I</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: #faf6f0;
            color: #302017;
        }

        .konfirmasi-container {
            min-height: 100vh;
            padding: 55px 18px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }

        .konfirmasi-card {
            width: 100%;
            max-width: 650px;
            padding: 38px;
            background: #fff;
            border: 1px solid #eadfd2;
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(58, 36, 23, .07);
        }

        .brand {
            margin-bottom: 35px;
            font-size: 17px;
            font-weight: 800;
            letter-spacing: 2px;
        }

        .brand small {
            display: block;
            margin-top: 5px;
            font-size: 10px;
            letter-spacing: 3px;
            color: #8b7565;
        }

        .ikon-sukses {
            width: 66px;
            height: 66px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 22px;
            border-radius: 50%;
            background: #f2e8dc;
            color: #795238;
            font-size: 36px;
        }

        .label-kecil {
            color: #a76f45;
            font-size: 12px;
            font-weight: bold;
            letter-spacing: 3px;
        }

        h1 {
            margin: 12px 0 16px;
            font-family: Georgia, serif;
            font-size: clamp(29px, 5vw, 40px);
            line-height: 1.2;
        }

        .deskripsi {
            color: #78675d;
            line-height: 1.8;
        }

        .detail-pesanan {
            margin-top: 28px;
            padding: 22px;
            background: #f7f1e9;
            border-left: 4px solid #a76f45;
            border-radius: 9px;
        }

        .baris {
            margin: 8px 0;
            line-height: 1.7;
            overflow-wrap: anywhere;
        }

        .status-pesanan {
            display: inline-block;
            padding: 9px 15px;
            border-radius: 30px;
            background: #fff0cf;
            color: #85551e;
            font-size: 14px;
            font-weight: bold;
        }

        .pesan-status {
            margin-top: 20px;
            padding: 14px 16px;
            border-radius: 8px;
            background: #faf6f0;
            color: #78675d;
            font-size: 14px;
            line-height: 1.7;
        }

        .tombol-kembali {
            display: inline-block;
            margin-top: 24px;
            padding: 14px 22px;
            border-radius: 7px;
            background: #302017;
            color: white;
            text-decoration: none;
            font-weight: bold;
            transition: background .2s;
        }

        .tombol-kembali:hover {
            background: #795238;
        }

        @media (max-width: 600px) {
            .konfirmasi-container {
                padding: 25px 13px;
            }

            .konfirmasi-card {
                padding: 24px 20px;
            }
        }
    </style>
</head>

<body>
    <main class="konfirmasi-container">
        <section class="konfirmasi-card">

            <div class="brand">
                STREET N.I
                <small>COFFEE &amp; TEA</small>
            </div>

            <div class="ikon-sukses">✓</div>

            <div class="label-kecil">PESANANMU</div>

            <h1>Pesanan Berhasil Dikirim!</h1>

            <p class="deskripsi">
                Terima kasih, <?= e($nama) ?>.
                Pesananmu sudah tersimpan. Silakan pantau status
                pesanan melalui halaman ini.
            </p>

            <div class="detail-pesanan">
                <p class="baris">
                    Nomor pesanan:
                    <strong>#<?= e($id) ?></strong>
                </p>

                <?php if ($total !== null): ?>
                    <p class="baris">
                        Total pembayaran:
                        <strong><?= e(rupiah($total)) ?></strong>
                    </p>
                <?php endif; ?>

                <p class="baris">
                    Status pesanan:
                </p>

                <span class="status-pesanan" id="statusPesanan">
                    <?= e($status) ?>
                </span>
            </div>

            <p class="pesan-status" id="pesanStatus" aria-live="polite">
                Status akan diperiksa otomatis setiap 3 detik.
            </p>

            <a href="index.php#menu" class="tombol-kembali">
                ← Kembali ke Menu
            </a>

        </section>
    </main>

    <script>
        const statusEl = document.getElementById('statusPesanan');
        const pesanEl = document.getElementById('pesanStatus');

        async function perbaruiStatus() {
            try {
                const url = 'konfirmasi.php?id=<?= (int)$id ?>&ajax=status&_='
                    + Date.now();

                const response = await fetch(url, {
                    cache: 'no-store'
                });

                if (!response.ok) {
                    throw new Error('Status belum dapat dimuat.');
                }

                const data = await response.json();

                if (data.status) {
                    statusEl.textContent = data.status;
                    pesanEl.textContent =
                        'Status pesanan diperbarui otomatis. Terakhir diperiksa: '
                        + new Date().toLocaleTimeString('id-ID');
                }
            } catch (error) {
                pesanEl.textContent =
                    'Status belum dapat diperbarui. Sedang mencoba kembali.';
            }
        }

        // Periksa status setiap 3 detik tanpa me-refresh halaman.
        perbaruiStatus();
        setInterval(perbaruiStatus, 3000);
    </script>
</body>
</html>
