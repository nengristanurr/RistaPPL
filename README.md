# 🐾 PawMate - Platform Adopsi Hewan Online (PHP & MySQL XAMPP)

**PawMate** adalah platform modern yang dirancang untuk mempermudah dan mendigitalisasi proses adopsi hewan (anjing & kucing) secara online. Dibangun menggunakan bahasa pemrograman **PHP Native** dan database relasional **MySQL** via **XAMPP**, dengan antarmuka yang bersih, manis, ramah pengguna, dan responsif berkonsep UI/UX terinspirasi dari Pinterest (tema pastel pink, putih, dan abu-abu).

---

## 🎨 Konsep Desain & Palet Warna
- **Primary Color:** Pastel Pink (`#F36B91`)
- **Secondary Color:** Soft Pink (`#FFE7EF`)
- **Backgrounds:** Pure White (`#FFFFFF`) & Off-White (`#FFF9FB`)
- **Typography:** Dark Navy (`#252B3A`) dengan font Google Fonts *Poppins* & *Nunito*
- **Badge Status:**
  - 🟢 **Disetujui:** Hijau (`#065F46` / `#D1FAE5`)
  - 🟡 **Menunggu:** Kuning / Amber (`#92400E` / `#FEF3C7`)
  - 🔵 **Dalam Proses:** Biru (`#1E40AF` / `#DBEAFE`)
  - 🔴 **Ditolak:** Merah (`#991B1B` / `#FEE2E2`)
- **Aksen Elemen:** Sudut membulat (*smooth rounded cards* `20px`), bayangan lembut (*soft multi-layered shadows*), tombol pil (*pill buttons*), serta micro-animation ikon paw dan heart.

---

## 🚀 Fitur Utama Sistem (PHP & MySQL)

### 1. Area Pengguna (User / Publik)
- **Beranda (`index.php`)**:
  - Rekomendasi hewan adopsi secara dinamis dari database MySQL (`pets`).
  - Quick Search bar yang langsung memfilter katalog.
  - Statistik dinamis jumlah hewan siap adopsi dan teradopsi.
- **Katalog Hewan (`animals.php`)**:
  - Pencarian live berdasarkan nama, ras, atau karakter.
  - Multi-filter database: Jenis hewan (Anjing/Kucing), Kelompok Usia, Jenis Kelamin, dan Status Ketersediaan.
  - Quick Category Tags untuk seleksi cepat.
  - Tombol simpan ke Favorit (wishlist) interaktif yang tersimpan ke tabel MySQL `favorites`.
- **Detail Hewan (`animal-detail.php?id=...`)**:
  - Menampilkan data lengkap anabul dari MySQL: Galeri foto switcher, spesifikasi usia, ras, kelamin, ukuran, lokasi, kepribadian, status kesehatan, vaksin, dan kebutuhan perawatan.
  - Tombol **"Ajukan Adopsi"** (otomatis dinonaktifkan jika status bukan "Tersedia").
- **Formulir Pengajuan Adopsi (`adoption-form.php?id=...`)**:
  - Ringkasan anabul yang dipilih di sidebar.
  - Auto-fill data pemohon jika pengguna sudah login.
  - Validasi lengkap dan pencegahan pengajuan ganda (*duplicate application prevention*).
  - Menyimpan berkas pengajuan ke tabel MySQL `applications`.
- **Autentikasi Pengguna (`login.php` & `register.php`)**:
  - Sistem sesi akun PHP (`session_start()`).
  - Registrasi akun baru dengan hash password aman (`password_hash`).
  - Fitur **Akses Cepat Demo** (1-klik login sebagai Anggota atau Admin) untuk memudahkan penguji/dosen.
- **Dashboard Pengguna (`user-dashboard.php`)**:
  - Ringkasan profil dan modal perbarui data diri (disimpan ke tabel `users`).
  - Riwayat pengajuan adopsi dengan badge status real-time (*Menunggu*, *Dalam Proses*, *Disetujui*, *Ditolak*).
  - Catatan evaluasi dari pihak admin shelter.
  - Tab koleksi hewan favorit (*Wishlist*).

### 2. Area Pengurus (Admin Shelter)
- **Dashboard Admin (`admin-dashboard.php`)**:
  - 6 Kartu statistik real-time yang **dihitung langsung dari query MySQL**:
    1. Total Hewan
    2. Hewan Tersedia
    3. Total Pengajuan Masuk
    4. Pengajuan Menunggu Verifikasi
    5. Pengajuan Disetujui
    6. Pengajuan Ditolak
  - Tabel 5 pengajuan adopsi terbaru.
  - Tabel 5 hewan di shelter terbaru.
  - Tombol **Reset Data Demo** untuk mengembalikan database ke seed awal.
- **Manajemen Data Hewan (`admin-animals.php`)**:
  - CRUD lengkap data hewan ke tabel MySQL `pets`: Tambah hewan baru, edit data hewan, hapus data hewan.
  - Pemilih preset foto berkualitas tinggi atau input URL kustom.
- **Manajemen Pengajuan Adopsi (`admin-applications.php`)**:
  - Daftar seluruh berkas pemohon.
  - Modal review mendalam (data diri pemohon, kontak WhatsApp, tipe tempat tinggal, alasan adopsi).
  - Pembaruan status berkas menjadi *Dalam Proses*, *Disetujui*, atau *Ditolak* serta input catatan evaluasi shelter.
  - **Sinkronisasi Otomatis:** Saat pengajuan disetujui, status anabul di tabel `pets` otomatis diperbarui menjadi **"Sudah Diadopsi"**.
- **Manajemen Data Pengguna (`admin-users.php`)**:
  - Daftar seluruh pengguna terdaftar di MySQL, nomor WhatsApp, peran, tanggal daftar, dan jumlah berkas pengajuan adopsi (`COUNT(applications)`).

---

## 📁 Struktur File & Direktori

```
ppl rista/
│
├── config/
│   ├── database.php            # Koneksi PDO MySQL & Fitur Auto-Setup Database
│   └── auth.php                # Session Handler, Role Guard & Utilitas Auth
│
├── includes/
│   ├── header.php              # Header HTML & Load Stylesheet
│   ├── navbar.php              # Navbar Publik Dinamis Sesuai Sesi Akun
│   ├── footer.php              # Footer Publik & Sistem Notifikasi Toast
│   └── admin-sidebar.php       # Sidebar Admin dengan Real-time Badge Counts
│
├── api/
│   └── toggle-favorite.php     # Endpoint AJAX untuk Wishlist ke MySQL
│
├── database.sql                # File Skema & Data Demo SQL untuk phpMyAdmin
│
├── index.php                   # Beranda Utama (PHP & MySQL)
├── animals.php                 # Katalog Hewan dengan Filter Database
├── animal-detail.php           # Detail Anabul berdasarkan ?id=...
├── adoption-form.php           # Formulir Pengajuan Adopsi
├── login.php                   # Halaman Masuk Akun & Demo 1-Click
├── register.php                # Halaman Pendaftaran Akun Baru
├── logout.php                  # Logout & Pembersihan Sesi
├── user-dashboard.php          # Dashboard Anggota / Pemohon
│
├── admin-dashboard.php         # Dashboard Statistik Pengurus Shelter
├── admin-animals.php           # Manajemen CRUD Hewan Shelter
├── admin-applications.php      # Evaluasi & Approval Pengajuan Adopsi
├── admin-users.php             # Data Seluruh Pengguna & Riwayatnya
│
├── assets/
│   ├── css/
│   │   ├── style.css           # Styling Utama Pengguna & Komponen Global
│   │   └── admin.css           # Styling Khusus Dashboard & Sidebar Admin
│   └── js/
│       ├── main.js             # Utilitas Global
│       └── auth.js             # Helper Tampilan Input
│
├── _html_backup/               # Backup File HTML Prototipe Awal
└── README.md                   # Dokumentasi Proyek
```

---

## 💻 Cara Menjalankan Website di XAMPP

1. **Pastikan Folder Berada di `htdocs`:**
   - Folder proyek ini sudah berada di:  
     `C:\xampp\htdocs\ppl rista`

2. **Aktifkan Modul di XAMPP Control Panel:**
   - Buka aplikasi **XAMPP Control Panel**.
   - Klik tombol **Start** pada modul **Apache**.
   - Klik tombol **Start** pada modul **MySQL**.
   - Pastikan kedua modul berstatus hijau (*Running*).

3. **Inisialisasi Database (Otomatis & Siap Pakai):**
   - Sistem dilengkapi **Auto-Setup**: Saat pertama kali halaman dibuka di browser, sistem akan secara otomatis membuat database `pawmate_db`, membuat semua tabel, dan mengisinya dengan data demo awal.
   - *(Opsional)* Jika ingin mengimpor secara manual:
     1. Buka browser ke `http://localhost/phpmyadmin`.
     2. Buat database baru bernama `pawmate_db`.
     3. Pilih menu **Import** dan pilih file `database.sql` yang berada di folder proyek.

4. **Buka Website di Browser:**
   - Halaman Utama (Publik):  
     👉 **[http://localhost/ppl%20rista/](http://localhost/ppl%20rista/)**
   - Halaman Portal Admin:  
     👉 **[http://localhost/ppl%20rista/admin-dashboard.php](http://localhost/ppl%20rista/admin-dashboard.php)**
   - Halaman Masuk:  
     👉 **[http://localhost/ppl%20rista/login.php](http://localhost/ppl%20rista/login.php)**

---

## 🔑 Akun Demo Tersedia (Akses Cepat)

Untuk mempermudah pengujian dan demonstrasi di depan dosen/penguji:

| Peran (Role) | Email | Password | Keterangan |
| :--- | :--- | :--- | :--- |
| **Admin Shelter** | `admin@pawmate.id` | `admin` | Akses penuh ke seluruh halaman manajemen shelter |
| **Pengguna / Adopter** | `rista@example.com` | `password` | Akun anggota dengan riwayat pengajuan demo |

> *Tips: Pada halaman `login.php`, Anda cukup mengklik tombol **"Akun Pengguna"** atau **"Akun Admin"** untuk masuk secara instan tanpa perlu mengetik.*
