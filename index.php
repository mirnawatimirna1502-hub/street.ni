<?php
require __DIR__ . '/includes/cek_login_pelanggan.php';
require __DIR__ . '/config/koneksi.php';
require __DIR__ . '/includes/token_pesanan.php';

// Tetap menggunakan struktur tabel produk yang sudah ada.
$query = mysqli_query(
    $koneksi,
       "SELECT id_produk, nama_produk, harga, foto
     FROM produk
     ORDER BY nama_produk ASC"
);

function rupiah($angka) {
    return 'Rp ' . number_format((float)$angka, 0, ',', '.');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#2b1b14">
    <title>Street N.I Coffee & Tea</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/includes/header.php'; ?>

    <main>
        <section class="hero" id="beranda">
            <div class="hero-content">
                <p class="eyebrow">COFFEE • TEA • MOCKTAIL</p>
                <h1>Selamat Datang di<br><span>Kedai Street N.I</span></h1>
                <p>Nikmati berbagai pilihan kopi dan minuman favorit dengan cita rasa terbaik.</p>
                <div class="hero-actions">
                    <a class="button button-light" href="#menu">Lihat Menu</a>
                    <a class="button button-outline" href="#pemesanan">Pesan Sekarang</a>
                </div>
            </div>
            <div class="hero-art">
                <img class="hero-shop-photo" src="assets/images/logo-street-ni.jpeg" alt="Foto asli kedai Street N.I">
                <span class="hero-note">Good Coffee<br>Better Days</span>
            </div>
        </section>

        <section class="section favorite-section" id="profil">
            <div class="section-heading">
                <div><p class="eyebrow">CERITA KAMI</p><h2>Selamat Datang di Street N.I</h2></div>
                <a class="text-link" href="#lokasi">Kenali kami <span>→</span></a>
            </div>
            <div class="about-panel">
                <div class="about-icon">N.I</div>
                <div>
                    <h3>Kopi, cerita, dan suasana yang hangat.</h3>
                    <p>Street N.I Lahir dari persahabatan. N.I adalah N itu Nusul, dan I adalah Ichal. Dua orang yang memulai dari jalanan dengan alat sederhana, tapi satu tujuan : membuat kopi yang jujur rasanya.</p>
                </div>
            </div>
        </section>

        <section class="section menu-section" id="menu">
            <div class="section-heading">
                <div><p class="eyebrow">PILIHAN UNTUKMU</p><h2>Menu Kami</h2><p class="section-subtitle">Pilih minuman favoritmu dari menu Street N.I.</p></div>
                <a class="text-link" href="#pemesanan">Pesan menu <span>→</span></a>
            </div>

            <div class="category-pills" aria-label="Kategori menu">
                <button type="button" class="category-pill selected" data-filter="all">Semua</button>
                <button type="button" class="category-pill" data-filter="coffee">Coffee</button>
                <button type="button" class="category-pill" data-filter="ice">Ice Coffee</button>
                <button type="button" class="category-pill" data-filter="tea">Non Coffee / Tea</button>
                <button type="button" class="category-pill" data-filter="mocktail">Mocktail</button>
            </div>

            <?php if ($query && mysqli_num_rows($query) > 0) { ?>
            <form action="proses_pesanan.php" method="POST" id="orderForm">
                <input type="hidden" name="token_pesanan" value="<?= htmlspecialchars($_SESSION['token_pesanan'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                <div class="menu-grid">
                    <?php while ($produk = mysqli_fetch_assoc($query)) {
                        $nama = (string)$produk['nama_produk'];
                        $kategori = 'coffee';
                        $namaLower = strtolower($nama);
                        if (preg_match('/matcha|lemon|thai tea|chocolate|coklat|milk tea|teh/', $namaLower)) {
                            $kategori = 'tea';
                        } elseif (preg_match('/mangga|strawberry|stroberry|berry|wilberry|mocktail/', $namaLower)) {
                            $kategori = 'mocktail';
                        } elseif (preg_match('/americano|aren|hazelnut|butterscotch|pistachio|pisachio|caramel|macchiato|kopi susu/', $namaLower)) {
                            $kategori = 'ice';
                        }
                    ?>
                    <article class="product-card" data-category="<?= htmlspecialchars($kategori, ENT_QUOTES, 'UTF-8') ?>">
                       <?php
							$foto = trim((string)($produk['foto'] ?? ''));
						$fotoPath = 'assets/images/menu/' . basename($foto);
						if ($foto !== '' && is_file(__DIR__ . '/' . $fotoPath)) { ?>
						<div class="product-photo"><img src="<?= htmlspecialchars($fotoPath, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($nama, ENT_QUOTES, 'UTF-8') ?>" loading="lazy"></div>
						<?php } else { ?>
						<div class="product-art" aria-hidden="true"><span>☕</span><small>STREET N.I</small></div>
						<?php } ?>
                        <div class="product-info">
                            <h3><?= htmlspecialchars($nama, ENT_QUOTES, 'UTF-8') ?></h3>
                            <p class="product-price"><?= rupiah($produk['harga']) ?></p>
                            <label class="quantity-control">
                                <span>Jumlah</span>
                                <input type="number" name="qty[<?= (int)$produk['id_produk'] ?>]" min="0" max="100" value="0" inputmode="numeric" aria-label="Jumlah <?= htmlspecialchars($nama, ENT_QUOTES, 'UTF-8') ?>">
                            </label>
                        </div>
                    </article>
                    <?php } ?>
                </div>

                <section class="order-panel" id="pemesanan">
                    <div class="order-heading">
                        <p class="eyebrow">PESANANMU</p>
                        <h2>Pemesanan Online</h2>
                        <p>Pilih menu di atas, lalu isi data berikut untuk mengirim pesanan.</p>
                    </div>
                    <div class="order-layout">
                        <div class="order-summary">
                            <h3>Informasi Pemesanan</h3>
                            <p>Pastikan jumlah minuman sudah sesuai sebelum mengirim pesanan.</p>
                            <a class="text-link" href="riwayat_pesanan.php">Lihat Riwayat Pesanan →</a>
                        </div>
                        <div class="order-fields">
                            <label for="nama">Nama lengkap <span>*</span></label>
                            <input type="text" id="nama" name="nama_pemesan" maxlength="100" placeholder="Masukkan nama lengkap" required>
                            <label for="hp">Nomor HP <span>*</span></label>
                            <input type="tel" id="hp" name="no_hp" maxlength="20" placeholder="Contoh: 081234567890" required>
                            <label for="catatan">Catatan pesanan</label>
                            <textarea id="catatan" name="catatan" rows="3" placeholder="Contoh: gula sedikit, es sedikit"></textarea>
                            <button class="button button-dark submit-button" type="submit">Kirim Pesanan <span>→</span></button>
                            <p class="form-note">* Isi nama dan nomor HP. Pilih minimal satu produk.</p>
                        </div>
                    </div>
                </section>
            </form>
            <?php } else { ?>
                <div class="empty-menu">Menu belum tersedia. Silakan isi tabel produk terlebih dahulu.</div>
            <?php } ?>
        </section>

        <section class="location-section" id="lokasi">
            <div>
                <p class="eyebrow">KUNJUNGI KAMI</p>
                <h2>Temukan Street N.I</h2>
                <p>Lapangan Pancasila, Palopo</p>
                <p><strong>Jam operasional</strong><br>16.00–23.59 WITA</p>
            </div>
            <div class="contact-card">
                <h3>Lokasi &amp; Kontak</h3>
                <p><span>⌖</span> Lapangan Pancasila, Palopo</p>
                <p><span>◷</span> Setiap hari, 16.00–23.59 WITA</p>
                <p><span>☎</span> 0812-4454-1335</p>
                <p><span>◎</span> @street_n.i</p>
                <div style="margin-top:18px; width:100%; overflow:hidden; border-radius:10px; border:1px solid #e8ded2;">
                    <iframe
                        src="https://maps.google.com/maps?q=-3.009652,120.201485&z=17&output=embed"
                        width="100%"
                        height="280"
                        style="display:block; border:0;"
                        loading="lazy"
                        allowfullscreen
                        title="Peta lokasi Street N.I">
                    </iframe>
                </div>
                <p style="margin-top:10px;"><a href="https://www.google.com/maps?q=-3.009652,120.201485" target="_blank" rel="noopener" style="color:#795238; font-weight:bold; text-decoration:none;">Buka lokasi di Google Maps →</a></p>
            </div>
        </section>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>

 <script src="assets/js/script.js"></script>
</body>
</html>
