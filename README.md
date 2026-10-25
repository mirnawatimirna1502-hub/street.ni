# Street N.I Coffee & Tea

Website pemesanan minuman online untuk kedai **Street N.I Coffee & Tea**.

## Mitra
Street N.I Coffee & Tea, Lapangan Pancasila, Palopo (Instagram: @street_n.i)

## Tech Stack
- PHP native (MySQLi, prepared statement)
- MySQL / MariaDB
- HTML, CSS, JavaScript
- XAMPP (Apache + MySQL) untuk lingkungan lokal
- Login pelanggan: nomor HP + kata sandi, dan Google Sign-In

## Struktur Folder
```
street_ni/
├── index.php, login_pelanggan.php, daftar_pelanggan.php, ...   halaman pelanggan
├── admin/        halaman admin (dashboard, login, ubah status)
├── assets/
│   ├── css/      style.css
│   ├── js/       script.js
│   ├── images/   logo dan foto kedai; menu/ berisi foto minuman
│   └── fonts/
├── config/       koneksi.php, config_google.php
├── includes/     header.php, navbar.php, footer.php, helper login pelanggan
├── database/     db_street_ni.sql, migrasi_login_pelanggan.sql
└── README.md
```

## Cara Menjalankan di Lokal (XAMPP)
1. Install XAMPP, lalu jalankan **Apache** dan **MySQL** di XAMPP Control Panel.
2. Salin folder `street_ni` ke `C:\xampp\htdocs\`.
3. Buka `http://localhost/phpmyadmin`, pilih **Import**, lalu impor `database/db_street_ni.sql`.
4. Sesuaikan `config/koneksi.php` bila user atau password MySQL berbeda.
5. Buka website: `http://localhost/street_ni/` (daftar akun pelanggan terlebih dahulu).
6. Admin: `http://localhost/street_ni/admin/login.php`.

## Login Google (opsional)
Buat OAuth Client ID (Web application) di Google Cloud Console dengan origin
`http://localhost`, lalu isi `GOOGLE_CLIENT_ID` di `config/config_google.php`.
Ekstensi PHP cURL harus aktif.

## Foto Menu
Simpan foto di `assets/images/menu/`, lalu isi kolom `foto` pada tabel `produk`
dengan nama file (contoh: `americano.jpeg`).
