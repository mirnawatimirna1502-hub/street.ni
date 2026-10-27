<?php
require __DIR__ . '/cek_login.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['csrf_admin_hapus'])) {
    $_SESSION['csrf_admin_hapus'] = bin2hex(random_bytes(32));
}

$pesan = '';
$jenisPesan = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hapus_pesanan'])) {
    $idHapus = filter_input(INPUT_POST, 'id_pesanan', FILTER_VALIDATE_INT);
    $token = $_POST['csrf_token'] ?? '';

    if (!is_string($token) || !hash_equals($_SESSION['csrf_admin_hapus'], $token)) {
        $pesan = 'Permintaan tidak valid. Silakan muat ulang halaman.';
        $jenisPesan = 'error';
    } elseif (!$idHapus || $idHapus < 1) {
        $pesan = 'ID pesanan tidak valid.';
        $jenisPesan = 'error';
    } else {
        $stmt = mysqli_prepare($koneksi, "SELECT status_pesanan FROM pesanan WHERE id_pesanan = ? LIMIT 1");
        if (!$stmt) {
            $pesan = 'Pesanan tidak dapat diperiksa.';
            $jenisPesan = 'error';
        } else {
            mysqli_stmt_bind_param($stmt, 'i', $idHapus);
            mysqli_stmt_execute($stmt);
            $resultStatus = mysqli_stmt_get_result($stmt);
            $dataStatus = mysqli_fetch_assoc($resultStatus);
            mysqli_stmt_close($stmt);

            if (!$dataStatus) {
                $pesan = 'Pesanan tidak ditemukan.';
                $jenisPesan = 'error';
            } elseif (strtolower(trim($dataStatus['status_pesanan'])) !== 'selesai') {
                $pesan = 'Hanya pesanan berstatus Selesai yang dapat dihapus.';
                $jenisPesan = 'error';
            } else {
                mysqli_begin_transaction($koneksi);
                try {
                    $stmtDetail = mysqli_prepare($koneksi, "DELETE FROM detail_pesanan WHERE id_pesanan = ?");
                    if (!$stmtDetail) {
                        throw new Exception();
                    }
                    mysqli_stmt_bind_param($stmtDetail, 'i', $idHapus);
                    if (!mysqli_stmt_execute($stmtDetail)) {
                        mysqli_stmt_close($stmtDetail);
                        throw new Exception();
                    }
                    mysqli_stmt_close($stmtDetail);

                    $stmtHapus = mysqli_prepare(
                        $koneksi,
                        "DELETE FROM pesanan WHERE id_pesanan = ? AND status_pesanan = 'Selesai'"
                    );
                    if (!$stmtHapus) {
                        throw new Exception();
                    }
                    mysqli_stmt_bind_param($stmtHapus, 'i', $idHapus);
                    if (!mysqli_stmt_execute($stmtHapus)) {
                        mysqli_stmt_close($stmtHapus);
                        throw new Exception();
                    }
                    $terhapus = mysqli_stmt_affected_rows($stmtHapus);
                    mysqli_stmt_close($stmtHapus);

                    if ($terhapus !== 1) {
                        throw new Exception();
                    }

                    mysqli_commit($koneksi);
                    $pesan = 'Riwayat pesanan #' . $idHapus . ' berhasil dihapus.';
                    $jenisPesan = 'success';
                } catch (Throwable $e) {
                    mysqli_rollback($koneksi);
                    $pesan = 'Gagal menghapus pesanan. Periksa tabel detail_pesanan dan relasi database.';
                    $jenisPesan = 'error';
                }
            }
        }
    }
}

$query = mysqli_query(
    $koneksi,
    "SELECT id_pesanan, nama_pemesan, no_hp, tanggal_pesanan, total_harga, status_pesanan
     FROM pesanan ORDER BY tanggal_pesanan DESC"
);

if (!$query) {
    exit('Data pesanan gagal dimuat. Periksa koneksi dan struktur tabel database.');
}

$daftarPesanan = [];
$totalSemua = 0;
$totalSelesai = 0;
$totalProses = 0;
$totalMenunggu = 0;
$totalOmzetSelesai = 0;

while ($p = mysqli_fetch_assoc($query)) {
    $daftarPesanan[] = $p;
    $totalSemua++;
    $status = strtolower(trim($p['status_pesanan'] ?? ''));

    if ($status === 'selesai') {
        $totalSelesai++;
        $totalOmzetSelesai += (float)($p['total_harga'] ?? 0);
    } elseif ($status === 'sedang dibuat') {
        $totalProses++;
    } else {
        $totalMenunggu++;
    }
}

function e($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Dashboard Admin | Street N.I</title>
<style>
:root {
    --coffee:#302017;
    --coffee-2:#4a3022;
    --brown:#8b6244;
    --cream:#f7f2eb;
    --paper:#fffdfa;
    --line:#eadfd2;
    --muted:#88786c;
    --green:#28764b;
    --orange:#9a651b;
    --red:#a83232;
}
*{box-sizing:border-box}
body{margin:0;background:var(--cream);color:#302820;font-family:Arial,Helvetica,sans-serif}
a{color:inherit;text-decoration:none}
.layout{min-height:100vh;display:flex}
.sidebar{width:245px;flex-shrink:0;background:var(--coffee);color:#fff;padding:28px 18px;display:flex;flex-direction:column}
.brand{padding:4px 12px 30px;border-bottom:1px solid rgba(255,255,255,.14)}
.brand strong{font-size:21px;letter-spacing:2px;display:block}
.brand span{font-size:10px;letter-spacing:3px;color:#d8c1ac;display:block;margin-top:6px}
.side-label{padding:26px 12px 10px;color:#c8b5a5;font-size:10px;font-weight:bold;letter-spacing:2px}
.nav-link{display:block;padding:13px 14px;margin:4px 0;border-radius:9px;color:#eee1d5;font-size:14px}
.nav-link.active,.nav-link:hover{background:#59402f;color:#fff}
.sidebar-note{margin-top:auto;padding:15px 12px;color:#d1bba7;font-size:12px;line-height:1.7}
.main{flex:1;min-width:0;padding:30px clamp(16px,3vw,42px) 45px}
.topline{display:flex;justify-content:space-between;align-items:center;gap:15px;margin-bottom:28px}
.topline p{margin:5px 0 0;color:var(--muted);font-size:13px}
.topline h1{font-family:Georgia,serif;font-size:clamp(25px,3vw,34px);margin:0;color:var(--coffee)}
.admin-pill{background:#fff;border:1px solid var(--line);border-radius:999px;padding:10px 14px;font-size:12px;font-weight:bold;color:var(--coffee);white-space:nowrap}
.welcome{background:linear-gradient(115deg,#3a2519,#745037);color:#fff;border-radius:16px;padding:26px 28px;margin-bottom:22px;position:relative;overflow:hidden}
.welcome:after{content:"";position:absolute;width:190px;height:190px;border:1px solid rgba(255,255,255,.12);border-radius:50%;right:-45px;top:-70px;box-shadow:0 0 0 22px rgba(255,255,255,.03),0 0 0 44px rgba(255,255,255,.025)}
.welcome small{color:#e0c7af;letter-spacing:2px;font-size:10px;font-weight:bold}
.welcome h2{font-family:Georgia,serif;font-size:26px;margin:9px 0}
.welcome p{margin:0;color:#f0e3d7;font-size:13px;line-height:1.6;max-width:620px}
.stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:15px;margin-bottom:26px}
.stat{background:var(--paper);border:1px solid var(--line);border-radius:13px;padding:20px;min-width:0}
.stat-top{display:flex;align-items:center;justify-content:space-between;gap:8px;color:var(--muted);font-size:12px}
.stat-icon{width:34px;height:34px;border-radius:10px;display:flex;align-items:center;justify-content:center;background:#f0e5d9;color:var(--coffee);font-size:16px}
.stat strong{display:block;font-family:Georgia,serif;font-size:28px;margin-top:14px;color:var(--coffee);overflow-wrap:anywhere}
.stat small{display:block;margin-top:6px;color:var(--muted);font-size:11px}
.section-head{display:flex;justify-content:space-between;align-items:end;gap:15px;margin:0 0 15px}
.section-head h2{font-size:19px;margin:0;color:var(--coffee)}
.section-head p{font-size:12px;color:var(--muted);margin:6px 0 0}
.table-card{background:var(--paper);border:1px solid var(--line);border-radius:14px;overflow:hidden}
.table-scroll{overflow-x:auto}
table{width:100%;border-collapse:collapse;min-width:940px}
th{background:#f5eee6;text-align:left;color:#725741;font-size:11px;text-transform:uppercase;letter-spacing:.6px;padding:15px 16px;white-space:nowrap}
td{padding:15px 16px;border-top:1px solid #f0e8df;font-size:13px;vertical-align:middle}
tbody tr:hover{background:#fffcf8}
.order-id{font-weight:bold;color:var(--coffee)}
.customer-name{font-weight:bold;margin-bottom:4px}
.subtext{font-size:11px;color:var(--muted)}
.money{font-weight:bold;white-space:nowrap}
.badge{display:inline-block;padding:7px 10px;border-radius:99px;font-size:11px;font-weight:bold;white-space:nowrap}
.badge-menunggu{background:#fff0d5;color:#8a5a12}
.badge-proses{background:#e8efff;color:#315b9b}
.badge-selesai{background:#e4f4e9;color:#28764b}
.status-form{display:flex;gap:7px;align-items:center}
.status-form select{max-width:145px;padding:9px 8px;border:1px solid var(--line);border-radius:7px;background:white;color:#493629;font-size:12px}
button{font-family:inherit}
.btn{border:0;border-radius:7px;padding:10px 12px;font-weight:bold;font-size:12px;cursor:pointer}
.btn-save{background:var(--coffee);color:white}
.btn-save:hover{background:var(--brown)}
.btn-delete{background:#fff0ed;color:var(--red);border:1px solid #f2d1ca}
.btn-delete:hover{background:#fbe1dc}
.empty{text-align:center;padding:45px 20px;color:var(--muted)}
.message{padding:13px 16px;border-radius:9px;margin:0 0 18px;font-size:13px;line-height:1.5}
.message.success{background:#e7f5e9;color:#205d2b}
.message.error{background:#fff0ed;color:#8a2d1c}
.table-footer{padding:14px 17px;display:flex;justify-content:space-between;gap:10px;color:var(--muted);font-size:11px;border-top:1px solid var(--line)}
@media(max-width:1100px){.stats{grid-template-columns:repeat(2,minmax(0,1fr))}.sidebar{width:210px}}
@media(max-width:720px){.layout{display:block}.sidebar{width:100%;padding:17px 16px}.brand{padding:0 4px 15px}.side-label{padding:15px 4px 7px}.nav-wrap{display:flex;gap:6px;overflow-x:auto}.nav-link{white-space:nowrap;padding:10px 12px}.sidebar-note{display:none}.main{padding:22px 14px 30px}.topline{align-items:flex-start}.admin-pill{font-size:11px}.welcome{padding:22px}.welcome h2{font-size:23px}.stats{gap:10px}.stat{padding:15px}.stat strong{font-size:24px}.section-head{align-items:flex-start;flex-direction:column}}
@media(max-width:400px){.stats{grid-template-columns:1fr 1fr}.stat strong{font-size:21px}.topline h1{font-size:24px}}

.topline {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 20px;
}

.admin-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 10px;
    flex-shrink: 0;
}

.logout-btn {
    display: inline-block;
    background: #302017;
    color: white;
    padding: 10px 16px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: bold;
    text-decoration: none;
    white-space: nowrap;
}

.logout-btn:hover {
    background: #563a29;
}

@media (max-width: 700px) {
    .topline {
        flex-wrap: wrap;
        align-items: flex-start;
    }

    .page-heading {
        min-width: 0;
        flex: 1 1 220px;
    }

    .admin-actions {
        margin-left: auto;
        flex-wrap: wrap;
    }

    .status-form {
        flex-wrap: wrap;
        min-width: 230px;
    }

    .status-form select {
        flex: 1 1 130px;
        max-width: 100%;
    }

    .table-footer {
        flex-wrap: wrap;
    }
}

@media (max-width: 420px) {
    .admin-actions {
        width: 100%;
        justify-content: flex-start;
        margin-left: 0;
    }

    .welcome {
        padding: 18px;
    }

    .welcome h2 {
        font-size: 21px;
    }
}

</style>
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div class="brand">
            <strong>STREET N.I</strong>
            <span>COFFEE &amp; TEA · ADMIN</span>
        </div>
        <div class="side-label">MENU ADMIN</div>
        <nav class="nav-wrap">
            <a class="nav-link active" href="admin.php">▦ &nbsp; Dashboard &amp; Pesanan</a>
            <a class="nav-link" href="../index.php" target="_blank" rel="noopener">↗ &nbsp; Lihat Website</a>
        </nav>
        <div class="sidebar-note">
            Kelola pesanan pelanggan Street N.I dengan mudah.<br>
            <span>Panel administrasi</span>
        </div>
    </aside>

    <main class="main">
        <div class="topline">
            <div class="page-heading">
                <h1>Dashboard Admin</h1>
                <p>Ringkasan dan pengelolaan pesanan Street N.I.</p>
            </div>
            <div class="admin-actions">
                <div class="admin-pill">☕ Panel Admin</div>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>

        <section class="welcome">
            <small>STREET N.I · ADMINISTRASI</small>
            <h2>Selamat datang kembali!</h2>
            <p>Pantau pesanan masuk, perbarui status pembuatan minuman, dan kelola riwayat pesanan yang sudah selesai dari satu halaman.</p>
        </section>

        <?php if (isset($_GET['berhasil'])): ?>
            <div class="message success">Status pesanan berhasil diperbarui.</div>
        <?php endif; ?>
        <?php if ($pesan !== ''): ?>
            <div class="message <?= e($jenisPesan) ?>"><?= e($pesan) ?></div>
        <?php endif; ?>

        <section class="stats">
            <div class="stat">
                <div class="stat-top"><span>Total Pesanan</span><span class="stat-icon">▤</span></div>
                <strong><?= $totalSemua ?></strong>
                <small>Semua pesanan tercatat</small>
            </div>
            <div class="stat">
                <div class="stat-top"><span>Menunggu</span><span class="stat-icon">◷</span></div>
                <strong><?= $totalMenunggu ?></strong>
                <small>Perlu diproses</small>
            </div>
            <div class="stat">
                <div class="stat-top"><span>Sedang Dibuat</span><span class="stat-icon">☕</span></div>
                <strong><?= $totalProses ?></strong>
                <small>Dalam proses pembuatan</small>
            </div>
            <div class="stat">
                <div class="stat-top"><span>Selesai</span><span class="stat-icon">✓</span></div>
                <strong><?= $totalSelesai ?></strong>
                <small>Omzet pesanan selesai: <?= 'Rp ' . number_format($totalOmzetSelesai, 0, ',', '.') ?></small>
            </div>
        </section>

        <section>
            <div class="section-head">
                <div>
                    <h2>Daftar Pesanan</h2>
                    <p>Ubah status pesanan atau hapus riwayat yang sudah selesai.</p>
                </div>
            </div>

            <div class="table-card">
                <div class="table-scroll">
                    <table>
                        <thead>
                            <tr>
                                <th>No. Pesanan</th>
                                <th>Pelanggan</th>
                                <th>Tanggal</th>
                                <th>Total</th>
                                <th>Status Saat Ini</th>
                                <th>Ubah Status</th>
                                <th>Riwayat</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (count($daftarPesanan) === 0): ?>
                            <tr><td colspan="7" class="empty">Belum ada pesanan yang tercatat.</td></tr>
                        <?php else: ?>
                            <?php foreach ($daftarPesanan as $p): ?>
                                <?php
                                $status = $p['status_pesanan'] ?? 'Menunggu';
                                $statusLower = strtolower(trim($status));
                                $badgeClass = $statusLower === 'selesai'
                                    ? 'badge-selesai'
                                    : ($statusLower === 'sedang dibuat' ? 'badge-proses' : 'badge-menunggu');
                                ?>
                                <tr>
                                    <td><span class="order-id">#<?= (int)$p['id_pesanan'] ?></span></td>
                                    <td>
                                        <div class="customer-name"><?= e($p['nama_pemesan']) ?></div>
                                        <div class="subtext"><?= e($p['no_hp']) ?></div>
                                    </td>
                                    <td><?= e($p['tanggal_pesanan']) ?></td>
                                    <td class="money">Rp <?= number_format((float)$p['total_harga'], 0, ',', '.') ?></td>
                                    <td><span class="badge <?= $badgeClass ?>"><?= e($status) ?></span></td>
                                    <td>
                                        <form class="status-form" action="ubah_status.php" method="POST">
                                            <input type="hidden" name="id_pesanan" value="<?= (int)$p['id_pesanan'] ?>">
                                            <select name="status" required aria-label="Pilih status pesanan">
                                                <option value="Menunggu" <?= $status === 'Menunggu' ? 'selected' : '' ?>>Menunggu</option>
                                                <option value="Sedang Dibuat" <?= $status === 'Sedang Dibuat' ? 'selected' : '' ?>>Sedang Dibuat</option>
                                                <option value="Selesai" <?= $status === 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                                            </select>
                                            <button class="btn btn-save" type="submit">Simpan</button>
                                        </form>
                                    </td>
                                    <td>
                                        <?php if ($statusLower === 'selesai'): ?>
                                            <form method="POST" action="admin.php"
                                                onsubmit="return confirm('Yakin ingin menghapus riwayat pesanan #<?= (int)$p['id_pesanan'] ?>? Tindakan ini tidak dapat dibatalkan.');">
                                                <input type="hidden" name="id_pesanan" value="<?= (int)$p['id_pesanan'] ?>">
                                                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_admin_hapus']) ?>">
                                                <button class="btn btn-delete" type="submit" name="hapus_pesanan" value="1">Hapus Riwayat</button>
                                            </form>
                                        <?php else: ?>
                                            <span class="subtext">Tersedia saat selesai</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="table-footer">
                    <span><?= $totalSemua ?> pesanan</span>
                    <span>Street N.I · Admin</span>
                </div>
            </div>
        </section>
    </main>
</div>

<script>
(function () {
    // Perbarui dashboard setiap 5 detik
    // hanya jika tidak sedang mengirim formulir.
    setInterval(function () {
        if (document.visibilityState === 'visible') {
            window.location.reload();
        }
    }, 5000);
})();
</script>

</body>
</html>
