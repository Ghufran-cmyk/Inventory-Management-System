# Multivision Gear - Product Manager

Aplikasi web berbasis PHP dan MySQL untuk mengelola inventaris katalog peralatan fotografi, videografi, dan studio. Dibuat untuk memenuhi Tugas Akhir (Mini Project) Pemrograman Web Pertemuan 3.

## Fitur Utama
- **CRUD (Create, Read, Update, Delete)**: Pengelolaan data peralatan secara penuh.
- **Upload Gambar**: Fitur unggah foto produk ke direktori lokal (`/public/uploads/`).
- **Pencarian & Filter Kategori**: Pencarian teks dinamis dan filter berdasarkan 4 kategori spesifik menggunakan metode GET.
- **Keamanan & Validasi (Server-Side)**: 
  - Menerapkan perlindungan SQL Injection (PDO Prepared Statements) & XSS (htmlspecialchars).
  - Keamanan hapus data menggunakan POST & Token CSRF.
  - Validasi input ketat (Nama minimal 3 karakter, harga > 0, stok tidak boleh negatif).
  - Pola Post-Redirect-Get (PRG) untuk mencegah duplikasi data saat *refresh*.
- **UI/UX Modern**: Desain responsif menggunakan CSS Flexbox, Box Model, dan komponen visual yang menarik (Card, Dropdown, Notifikasi Dinamis).

## Teknologi yang Digunakan
- **Backend**: PHP 8+
- **Database**: MySQL / MariaDB (via PDO)
- **Frontend**: HTML5, CSS3 (tanpa framework)

## Cara Menjalankan Aplikasi (Instalasi)
1. komputer sudah terinstal web server lokal (seperti **XAMPP** atau **Laragon**).
2. Pindahkan seluruh folder proyek ini ke dalam direktori *document root* web server (contoh: `C:\xampp\htdocs\product-manager`).
3. Buka **phpMyAdmin** (biasanya di `http://localhost/phpmyadmin`).
4. Buat database baru dengan nama `store_db`.
5. *Import* file database yang sudah disediakan di dalam folder `database/store_db.sql`.
6. Buka *browser* dan akses aplikasi melalui URL:  
   `http://localhost/product-manager/public/index.php`

## Struktur Folder
- `config/db.php` - Pengaturan koneksi database.
- `public/` - Halaman antarmuka pengguna (index, create, edit, delete).
- `public/assets/` - File styling CSS dan aset statis (logo).
- `public/uploads/` - Tempat penyimpanan *file* gambar yang diunggah.
- `database/store_db.sql` - Skema tabel database untuk di-*import*.

---
**Dibuat oleh:**
- **Nama:** [Ghufran Fadlurrahman]
- **NIM:** [250180106]