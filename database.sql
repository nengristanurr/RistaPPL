-- ===================================================
-- PawMate - Database Schema & Seed Data
-- Platform Adopsi Hewan Online
-- Host: localhost (XAMPP MySQL)
-- Database: pawmate_db
-- ===================================================

CREATE DATABASE IF NOT EXISTS `pawmate_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `pawmate_db`;

-- --------------------------------------------------------
-- Struktur Tabel `users`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `favorites`;
DROP TABLE IF EXISTS `applications`;
DROP TABLE IF EXISTS `pets`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` VARCHAR(50) NOT NULL PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `phone` VARCHAR(30) DEFAULT '',
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'user') NOT NULL DEFAULT 'user',
  `address` TEXT NULL,
  `occupation` VARCHAR(100) DEFAULT '',
  `registered_at` DATE NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data Demo Users (Password: admin, password)
INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password`, `role`, `address`, `occupation`, `registered_at`) VALUES
('usr-admin', 'Admin PawMate', 'admin@pawmate.id', '081299887766', '$2y$10$vI8aWBnW3fID.ZQ4/zo1G.q1q3o1b98Z4tZ7uQWlGjKx5Oq7z5FfK', 'admin', 'Jl. Melati Indah No. 12, Jakarta', 'Shelter Manager PawMate', '2026-08-01'),
('usr-demo', 'Rista Purnamasari', 'rista@example.com', '081234567890', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'Jl. Cempaka Putih No. 45, Jakarta Pusat', 'Creative Designer & Dog Lover', '2026-09-10');

-- --------------------------------------------------------
-- Struktur Tabel `pets`
-- --------------------------------------------------------
CREATE TABLE `pets` (
  `id` VARCHAR(50) NOT NULL PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `species` ENUM('Anjing', 'Kucing', 'Lainnya') NOT NULL DEFAULT 'Anjing',
  `breed` VARCHAR(100) DEFAULT '',
  `age` VARCHAR(50) DEFAULT '',
  `gender` ENUM('Jantan', 'Betina') NOT NULL DEFAULT 'Jantan',
  `size` VARCHAR(50) DEFAULT 'Sedang',
  `location` VARCHAR(100) NOT NULL DEFAULT 'Jakarta',
  `status` ENUM('Tersedia', 'Dalam Proses', 'Sudah Diadopsi') NOT NULL DEFAULT 'Tersedia',
  `image` TEXT NOT NULL,
  `gallery` TEXT NULL,
  `description` TEXT NULL,
  `temperament` TEXT NULL,
  `health` TEXT NULL,
  `vaccinated` VARCHAR(100) DEFAULT '',
  `care_needs` TEXT NULL,
  `created_at` DATE NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data Demo Pets
INSERT INTO `pets` (`id`, `name`, `species`, `breed`, `age`, `gender`, `size`, `location`, `status`, `image`, `gallery`, `description`, `temperament`, `health`, `vaccinated`, `care_needs`, `created_at`) VALUES
('pet-1', 'Milo', 'Anjing', 'Golden Retriever Mix', '1.5 Tahun', 'Jantan', 'Sedang', 'Jakarta Selatan', 'Tersedia', 'https://images.unsplash.com/photo-1552053831-71594a27632d?auto=format&fit=crop&w=800&q=80', '[\"https://images.unsplash.com/photo-1552053831-71594a27632d?auto=format&fit=crop&w=800&q=80\",\"https://images.unsplash.com/photo-1537151625747-768eb6cf92b2?auto=format&fit=crop&w=800&q=80\",\"https://images.unsplash.com/photo-1583511655857-d19b40a7a54e?auto=format&fit=crop&w=800&q=80\"]', 'Milo adalah anjing berenergi positif yang sangat menyukai bermain bola dan ramah kepada anak-anak. Milo diselamatkan dari shelter lingkungan dan siap menjadi sahabat setia keluarga Anda.', '[\"Ramah\",\"Aktif\",\"Suka Bermain\",\"Penyayang\"]', 'Sehat prima, sudah disteril, bebas kutu dan jamur.', 'Lengkap (Rabies, DHPP)', 'Membutuhkan jalan pagi minimal 30 menit dan ruang gerak yang cukup.', '2026-09-15'),
('pet-2', 'Luna', 'Kucing', 'British Shorthair Mix', '8 Bulan', 'Betina', 'Kecil', 'Bandung', 'Tersedia', 'https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?auto=format&fit=crop&w=800&q=80', '[\"https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?auto=format&fit=crop&w=800&q=80\",\"https://images.unsplash.com/photo-1573865526739-10659fec78a5?auto=format&fit=crop&w=800&q=80\",\"https://images.unsplash.com/photo-1495360010541-f48722b34f7d?auto=format&fit=crop&w=800&q=80\"]', 'Luna adalah kucing manis bermata bulat yang suka mendengkur ketika dielus di bawah dagu. Sangat tenang dan cocok untuk yang tinggal di apartemen atau rumah minimalis.', '[\"Manja\",\"Tenang\",\"Pintar\",\"Indoor Cat\"]', 'Sangat sehat, rutin potong kuku, telinga bersih.', 'Lengkap (Tricat, Rabies)', 'Penyisiran bulu halus 2x seminggu dan litter box yang selalu bersih.', '2026-09-20'),
('pet-3', 'Coco', 'Anjing', 'Corgi Mix', '2 Tahun', 'Jantan', 'Sedang', 'Tangerang Selatan', 'Tersedia', 'https://images.unsplash.com/photo-1612536057832-2ff7ead58194?auto=format&fit=crop&w=800&q=80', '[\"https://images.unsplash.com/photo-1612536057832-2ff7ead58194?auto=format&fit=crop&w=800&q=80\",\"https://images.unsplash.com/photo-1589941013453-ec89f33b5e95?auto=format&fit=crop&w=800&q=80\"]', 'Coco berkepribadian ceria dan menggemaskan dengan langkah kakinya yang lincah. Sudah terlatih buang air di pee pad dan mengerti perintah dasar seperti duduk dan salaman.', '[\"Cerdas\",\"Lincah\",\"Setia\",\"Mudah Dilatih\"]', 'Sehat, sudah vaksinasi tahunan & obat cacing rutin.', 'Lengkap', 'Pola makan teratur untuk menjaga berat badan ideal.', '2026-09-22'),
('pet-4', 'Bella', 'Kucing', 'Persian Calico', '1 Tahun', 'Betina', 'Kecil', 'Jakarta Barat', 'Tersedia', 'https://images.unsplash.com/photo-1533738363-b7f9aef128ce?auto=format&fit=crop&w=800&q=80', '[\"https://images.unsplash.com/photo-1533738363-b7f9aef128ce?auto=format&fit=crop&w=800&q=80\",\"https://images.unsplash.com/photo-1561948955-570b270e7c36?auto=format&fit=crop&w=800&q=80\"]', 'Bella adalah kucing berbulu lebat tiga warna yang cantik jelita. Memiliki pembawaan anggun dan senang bersantai di dekat jendela menikmati sinar matahari sore.', '[\"Anggun\",\"Penyabar\",\"Pemalu Awalnya\",\"Lembut\"]', 'Sudah steril, gigi bersih, mata sehat terawat.', 'Lengkap (Tricat & Rabies)', 'Penyisiran bulu setiap hari dan ruangan sejuk (ber-AC lebih disukai).', '2026-09-25'),
('pet-5', 'Mochi', 'Kucing', 'Domestic Short Hair', '5 Bulan', 'Jantan', 'Kecil', 'Depok', 'Tersedia', 'https://images.unsplash.com/photo-1574158622682-e40e69881006?auto=format&fit=crop&w=800&q=80', '[\"https://images.unsplash.com/photo-1574158622682-e40e69881006?auto=format&fit=crop&w=800&q=80\",\"https://images.unsplash.com/photo-1543852786-1cf6624b9987?auto=format&fit=crop&w=800&q=80\"]', 'Kitten mungil putih dengan motif belang abu yang super aktif! Suka mengejar tali dan bola kerincing. Sangat sosial dan mudah akrab dengan siapa saja.', '[\"Super Aktif\",\"Pemberani\",\"Fasih Mengeong\",\"Sosial\"]', 'Sehat, sudah obat cacing, bebas kutu.', 'Vaksin Tahap 1 Selesai', 'Makanan kitten bernutrisi tinggi dan mainan interaktif.', '2026-09-28'),
('pet-6', 'Oreo', 'Anjing', 'Border Collie Mix', '3 Tahun', 'Jantan', 'Sedang', 'Bekasi', 'Tersedia', 'https://images.unsplash.com/photo-1561037404-61cd46aa615b?auto=format&fit=crop&w=800&q=80', '[\"https://images.unsplash.com/photo-1561037404-61cd46aa615b?auto=format&fit=crop&w=800&q=80\",\"https://images.unsplash.com/photo-1517849845537-4d257902454a?auto=format&fit=crop&w=800&q=80\"]', 'Oreo adalah teman petualang sejati. Memiliki kecerdasan tinggi, sangat cepat belajar trik baru, dan sangat protektif serta menyayangi pemiliknya.', '[\"Cerdas Luar Biasa\",\"Setia\",\"Fokus\",\"Protektif\"]', 'Prima & atletis, sudah vaksin lengkap dan steril.', 'Lengkap', 'Memerlukan latihan stimulasi mental dan olahraga harian.', '2026-10-01'),
('pet-7', 'Cleo', 'Kucing', 'Siamese Mix', '1.2 Tahun', 'Betina', 'Kecil', 'Bogor', 'Tersedia', 'https://images.unsplash.com/photo-1513360309081-36f20ca480d0?auto=format&fit=crop&w=800&q=80', '[\"https://images.unsplash.com/photo-1513360309081-36f20ca480d0?auto=format&fit=crop&w=800&q=80\",\"https://images.unsplash.com/photo-1526336024174-e58f5cdd8e13?auto=format&fit=crop&w=800&q=80\"]', 'Cleo bermata biru kristal menawan dengan suara vokal yang lembut menyapa saat pemilik pulang ke rumah. Sangat suka tidur melingkar di atas selimut hangat.', '[\"Komunikatif\",\"Hangat\",\"Penyayang\",\"Manja\"]', 'Sehat & terawat, sudah steril.', 'Lengkap', 'Tempat tidur yang hangat dan perhatian penuh kasih sayang.', '2026-10-03'),
('pet-8', 'Rocky', 'Anjing', 'Beagle', '2.5 Tahun', 'Jantan', 'Sedang', 'Jakarta Utara', 'Tersedia', 'https://images.unsplash.com/photo-1505628346881-b72b27e84530?auto=format&fit=crop&w=800&q=80', '[\"https://images.unsplash.com/photo-1505628346881-b72b27e84530?auto=format&fit=crop&w=800&q=80\",\"https://images.unsplash.com/photo-1544568100-847a948585b9?auto=format&fit=crop&w=800&q=80\"]', 'Rocky adalah anjing lucu bertelinga panjang yang selalu ceria mengendus jejak petualangan baru. Sangat bersahabat dengan anak-anak maupun anjing lain.', '[\"Ceria\",\"Eksploratif\",\"Ramah\",\"Suka Mencium Aroma\"]', 'Sangat sehat, rutin kontrol ke dokter hewan.', 'Lengkap (DHPPi, Rabies)', 'Halaman berpagar aman dan jalan santai harian.', '2026-10-05');

-- --------------------------------------------------------
-- Struktur Tabel `applications`
-- --------------------------------------------------------
CREATE TABLE `applications` (
  `id` VARCHAR(50) NOT NULL PRIMARY KEY,
  `user_id` VARCHAR(50) NOT NULL,
  `pet_id` VARCHAR(50) NOT NULL,
  `user_name` VARCHAR(100) NOT NULL,
  `user_email` VARCHAR(100) NOT NULL,
  `user_phone` VARCHAR(30) NOT NULL,
  `pet_name` VARCHAR(100) NOT NULL,
  `pet_species` VARCHAR(50) DEFAULT '',
  `pet_image` TEXT NULL,
  `address` TEXT NOT NULL,
  `occupation` VARCHAR(100) DEFAULT '',
  `housing_type` VARCHAR(100) DEFAULT '',
  `pet_experience` TEXT NULL,
  `adoption_reason` TEXT NULL,
  `commitment_agreed` TINYINT(1) DEFAULT 1,
  `status` ENUM('Menunggu', 'Dalam Proses', 'Disetujui', 'Ditolak') NOT NULL DEFAULT 'Menunggu',
  `admin_notes` TEXT NULL,
  `applied_at` DATETIME NOT NULL,
  INDEX (`user_id`),
  INDEX (`pet_id`),
  CONSTRAINT `fk_app_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_app_pet` FOREIGN KEY (`pet_id`) REFERENCES `pets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data Demo Applications
INSERT INTO `applications` (`id`, `user_id`, `pet_id`, `user_name`, `user_email`, `user_phone`, `pet_name`, `pet_species`, `pet_image`, `address`, `occupation`, `housing_type`, `pet_experience`, `adoption_reason`, `commitment_agreed`, `status`, `admin_notes`, `applied_at`) VALUES
('app-1001', 'usr-demo', 'pet-1', 'Rista Purnamasari', 'rista@example.com', '081234567890', 'Milo', 'Anjing', 'https://images.unsplash.com/photo-1552053831-71594a27632d?auto=format&fit=crop&w=800&q=80', 'Jl. Cempaka Putih No. 45, Jakarta Pusat', 'Creative Designer', 'Rumah dengan Halaman Berpagar', 'Pernah memelihara anjing Golden selama 7 tahun.', 'Mencari sahabat baru setelah anjing sebelumnya meninggal karena usia tua. Keluarga kami sangat menyukai anjing aktif.', 1, 'Menunggu', 'Sedang menunggu jadwal wawancara virtual dengan pemohon.', '2026-10-10 14:30:00');

-- --------------------------------------------------------
-- Struktur Tabel `favorites`
-- --------------------------------------------------------
CREATE TABLE `favorites` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` VARCHAR(50) NOT NULL,
  `pet_id` VARCHAR(50) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_user_pet` (`user_id`, `pet_id`),
  INDEX (`user_id`),
  INDEX (`pet_id`),
  CONSTRAINT `fk_fav_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_fav_pet` FOREIGN KEY (`pet_id`) REFERENCES `pets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data Demo Favorites
INSERT INTO `favorites` (`user_id`, `pet_id`) VALUES
('usr-demo', 'pet-2'),
('usr-demo', 'pet-4');
