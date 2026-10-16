# Multivision Gear – Inventory Management System
**Oleh:** Ghufran Fadlurrahman (@gfranfdlur_)

---

## 1. Deskripsi Proyek
**Multivision Gear** adalah aplikasi manajemen inventaris berbasis web yang dikembangkan untuk mengelola persediaan peralatan fotografi dan multimedia secara komprehensif. Aplikasi ini dirancang dengan antarmuka *Modern Minimalist Dashboard* (Monochrome) dan mengedepankan integritas relasional, kepatuhan aturan bisnis (*business rules*), pencatatan audit mutasi (*audit trail*), keamanan tingkat tinggi (*prepared statements, validation, output escaping*), serta otorisasi berbasis peran (Role-Based Access Control / RBAC).

---

## 2. Desain Database & Relasi (ERD)
Aplikasi menerapkan 3 jenis relasi database relasional sesuai standar materi Pertemuan 4:

1. **One-to-One (1:1):**
   - `users` ↔ `user_profiles`
   - Kunci relasi: `user_profiles.user_id` bertipe UNIQUE dan merujuk ke `users.id` dengan opsi `ON DELETE CASCADE`.
2. **One-to-Many (1:N):**
   - `categories` ↔ `products` (Satu kategori memiliki banyak produk). Foreign key `products.category_id` dengan `ON DELETE RESTRICT`.
   - `users` ↔ `stock_movements` (Satu user mencatat banyak transaksi mutasi).
   - `products` ↔ `stock_movements` (Satu produk memiliki banyak riwayat mutasi masuk/keluar).
3. **Many-to-Many (M:N):**
   - `products` ↔ `suppliers`
   - Melalui Junction Table `product_suppliers` dengan Composite Primary Key (`product_id, supplier_id`) serta menyimpan atribut relasi `supplier_price` (harga beli dari masing-masing supplier).

                          +-------------------+
                          |   user_profiles   |
                          +-------------------+
                                    ^ (1:1)
                                    |
    +------------------+    +-------------------+    +---------------------+
    |    categories    |    |       users       |    |      suppliers      |
    +------------------+    +-------------------+    +---------------------+
            | (1:N)                 | (1:N)                    |
            v                       v                          | (M:N via
    +------------------+    +-------------------+              |  product_suppliers)
    |     products     |--->|  stock_movements  |<-------------+
    +------------------+    +-------------------+
            ^
            | (M:N)
    +-----------------------+
    |   product_suppliers   |
    +-----------------------+

---

## 3. Aturan Bisnis & Logika Transaksi (Business Logic)
Sesuai materi Slide 25, 26, 27, dan 33:

- **SKU Unik & Validasi Server:** Setiap SKU diverifikasi unik di database sebelum disimpan. Harga jual dan stok minimum tidak boleh bernilai negatif.
- **Barang Masuk (Stock IN):** Menggunakan Atomic Database Transaction (`beginTransaction`, `commit`, `rollBack`). Menambah stok fisik dan mencatat entri histori ke `stock_movements` secara bersamaan.
- **Barang Keluar (Stock OUT):**
  - Menggunakan `SELECT stock FROM products WHERE id = :id FOR UPDATE` (Pessimistic Locking).
  - Validasi ketat `quantity <= stock`. Mencegah terjadinya stok negatif.
  - Mengurangi stok dan mencatat riwayat transaksi.
- **Integritas Referensial (RESTRICT on Delete):**
  - Produk atau Kategori yang sudah memiliki histori transaksi mutasi tidak dapat dihapus sembarangan demi mempertahankan integritas audit trail laporan keuangan/gudang.
- **Otorisasi Ketat (RBAC):**
  - **Admin:** Memiliki akses penuh ke Master Produk, Kategori, Supplier, Transaksi Mutasi, dan Manajemen User. (Indikator warna: *Emerald Green*).
  - **Karyawan:** Hanya dapat melihat katalog produk dan mencatat transaksi Barang Masuk / Barang Keluar. Akses ke menu Master Tambah/Edit/Hapus Produk dan Data Supplier otomatis dihilangkan dari antarmuka dan diblokir dengan status HTTP 403 Forbidden di sisi server.

---

## 4. Fitur & Query Agregasi
- **Dashboard Real-Time (Slide 29):**
  - Widget Total Produk: `COUNT(products.id)`
  - Widget Nilai Inventory: `SUM(stock * price)`
  - Widget Produk Stok Rendah: `stock <= minimum_stock` (Dilengkapi peringatan stok kritis).
- **Listing Produk Tingkat Lanjut (Slide 13, 16, 17):**
  - Pencarian dengan klausa `LIKE` (nama produk dan SKU).
  - Filter kategori dinamis (Pill Navigation).
  - Sorting dengan Allowlist validation (mencegah SQL Injection pada `ORDER BY`).
  - Pagination dinamis yang mempertahankan seluruh parameter pencarian/filter di URL (`OFFSET` & `LIMIT`).

---

## 5. Keamanan Aplikasi (Security Best Practices)
1. **SQL Injection Defense:** Seluruh query dinamis menggunakan PDO Prepared Statements dengan parameter binding (`:param`). Opsi `PDO::ATTR_EMULATE_PREPARES => false` diaktifkan.
2. **XSS Protection:** Seluruh data keluaran HTML/Teks yang diinputkan pengguna melalui fungsi escaping `htmlspecialchars($val, ENT_QUOTES, 'UTF-8')`.
3. **Session Security:** Menerapkan `session_start()` yang ketat dengan penanganan sesi aman dan proteksi CSRF (*Cross-Site Request Forgery*) menggunakan token heksadesimal (`bin2hex(random_bytes(32))`).

---

## 6. Panduan Menjalankan Aplikasi

**Persyaratan:**
- Web Server XAMPP (Apache & MySQL/MariaDB) aktif.
- PHP versi 8.0 ke atas.

**Langkah Menjalankan:**
1. Ekstrak folder `product-manager` ke dalam direktori `C:\xampp\htdocs\`.
2. Buka phpMyAdmin di browser (http://localhost/phpmyadmin), lalu buat database baru bernama `store_db`.
3. Lakukan *Import* pada file `schema.sql` (atau `store_db.sql`) yang telah disediakan ke dalam database tersebut.
4. Buka URL utama aplikasi: `http://localhost/product-manager/public/` atau `http://localhost/product-manager/public/login.php`

**Akun Uji Coba (Demo Credentials):**
| Role | Username | Password | Hak Akses |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin` | `admin123` | Akses penuh (CRUD, Supplier, User) |
| **Karyawan/Staff** | `karyawan` | `staff123` | Read-only katalog & mutasi barang |
