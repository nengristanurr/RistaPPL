<?php
/**
 * PawMate - Database Connection & Multi-Environment Manager
 * Mendukung:
 * 1. MySQL Lokal XAMPP (Default)
 * 2. Remote Cloud MySQL (TiDB, Aiven, Railway, PlanetScale via Environment Variables)
 * 3. Serverless Vercel Preview (Fallback Otomatis ke SQLite jika belum ada cloud MySQL)
 */

function getDBConfig() {
    return [
        'host' => getenv('DB_HOST') ?: (getenv('MYSQLHOST') ?: 'localhost'),
        'port' => getenv('DB_PORT') ?: (getenv('MYSQLPORT') ?: 3306),
        'name' => getenv('DB_NAME') ?: (getenv('MYSQLDATABASE') ?: 'pawmate_db'),
        'user' => getenv('DB_USER') ?: (getenv('MYSQLUSER') ?: 'root'),
        'pass' => getenv('DB_PASS') ?: (getenv('MYSQLPASSWORD') ?: ''),
    ];
}

/**
 * Mendapatkan instance koneksi PDO
 */
function getDB() {
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    $config = getDBConfig();
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    $isVercel = (bool)getenv('VERCEL');

    // 1. Coba koneksi ke MySQL terlebih dahulu
    try {
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['name']};charset=utf8mb4";
        $pdo = new PDO($dsn, $config['user'], $config['pass'], $options);
        ensureTablesExist($pdo, 'mysql');
        return $pdo;
    } catch (PDOException $e) {
        // Jika database belum ada di MySQL lokal, coba buat database otomatis
        if (!$isVercel && ($e->getCode() == 1049 || strpos($e->getMessage(), 'Unknown database') !== false)) {
            try {
                $rootDsn = "mysql:host={$config['host']};port={$config['port']};charset=utf8mb4";
                $rootPdo = new PDO($rootDsn, $config['user'], $config['pass'], $options);
                $rootPdo->exec("CREATE DATABASE IF NOT EXISTS `{$config['name']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                
                $pdo = new PDO($dsn, $config['user'], $config['pass'], $options);
                ensureTablesExist($pdo, 'mysql');
                return $pdo;
            } catch (PDOException $innerEx) {
                // Biarkan lanjut ke pengecekan Vercel / Error display
            }
        }

        // 2. Jika di Vercel serverless dan koneksi MySQL gagal (belum pasang cloud DB), gunakan SQLite di /tmp
        if ($isVercel && extension_loaded('pdo_sqlite')) {
            try {
                $sqlitePath = sys_get_temp_dir() . '/pawmate_db.sqlite';
                $pdo = new PDO("sqlite:" . $sqlitePath, null, null, $options);
                ensureTablesExist($pdo, 'sqlite');
                return $pdo;
            } catch (Exception $sqEx) {
                // Lanjut ke error display
            }
        }

        // 3. Tampilkan pesan error ramah
        die("<div style='font-family:sans-serif; padding:2rem; background:#fee2e2; color:#991b1b; border-radius:8px; max-width:650px; margin:3rem auto; line-height:1.6;'>
            <h3 style='margin-top:0;'>Gagal Menghubungkan ke Database</h3>
            <p><strong>Lokal XAMPP:</strong> Pastikan modul <strong>MySQL</strong> di XAMPP Control Panel sudah dalam status <strong>Running</strong>.</p>
            <p><strong>Vercel / Cloud:</strong> Atur Environment Variables di Vercel Dashboard (<code>DB_HOST</code>, <code>DB_USER</code>, <code>DB_PASS</code>, <code>DB_NAME</code>, <code>DB_PORT</code>) untuk menghubungkan ke cloud MySQL (misal: TiDB Serverless, Aiven, atau Railway).</p>
            <p style='font-size:0.85rem; color:#b91c1c;'><strong>Detail Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
        </div>");
    }

    return $pdo;
}

/**
 * Memastikan tabel skema dan seed data sudah ada
 */
function ensureTablesExist($pdo, $driver = 'mysql') {
    if ($driver === 'sqlite') {
        // Skema SQLite untuk Vercel Serverless
        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id TEXT PRIMARY KEY,
            name TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            phone TEXT DEFAULT '',
            password TEXT NOT NULL,
            role TEXT NOT NULL DEFAULT 'user',
            address TEXT,
            occupation TEXT DEFAULT '',
            registered_at TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS pets (
            id TEXT PRIMARY KEY,
            name TEXT NOT NULL,
            species TEXT NOT NULL DEFAULT 'Anjing',
            breed TEXT DEFAULT '',
            age TEXT DEFAULT '',
            gender TEXT NOT NULL DEFAULT 'Jantan',
            size TEXT DEFAULT 'Sedang',
            location TEXT NOT NULL DEFAULT 'Jakarta',
            status TEXT NOT NULL DEFAULT 'Tersedia',
            image TEXT NOT NULL,
            gallery TEXT,
            description TEXT,
            temperament TEXT,
            health TEXT,
            vaccinated TEXT DEFAULT '',
            care_needs TEXT,
            created_at TEXT
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS applications (
            id TEXT PRIMARY KEY,
            user_id TEXT NOT NULL,
            pet_id TEXT NOT NULL,
            user_name TEXT NOT NULL,
            user_email TEXT NOT NULL,
            user_phone TEXT NOT NULL,
            pet_name TEXT NOT NULL,
            pet_species TEXT DEFAULT '',
            pet_image TEXT,
            address TEXT NOT NULL,
            occupation TEXT DEFAULT '',
            housing_type TEXT DEFAULT '',
            pet_experience TEXT,
            adoption_reason TEXT,
            commitment_agreed INTEGER DEFAULT 1,
            status TEXT NOT NULL DEFAULT 'Menunggu',
            admin_notes TEXT,
            applied_at TEXT NOT NULL
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS favorites (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id TEXT NOT NULL,
            pet_id TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(user_id, pet_id)
        )");
    } else {
        // Skema MySQL Standar (XAMPP / Remote MySQL)
        $pdo->exec("CREATE TABLE IF NOT EXISTS `users` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $pdo->exec("CREATE TABLE IF NOT EXISTS `pets` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $pdo->exec("CREATE TABLE IF NOT EXISTS `applications` (
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
            INDEX (`pet_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $pdo->exec("CREATE TABLE IF NOT EXISTS `favorites` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `user_id` VARCHAR(50) NOT NULL,
            `pet_id` VARCHAR(50) NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY `unique_user_pet` (`user_id`, `pet_id`),
            INDEX (`user_id`),
            INDEX (`pet_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    // Cek apakah data awal perlu dimasukkan
    $stmt = $pdo->query("SELECT COUNT(*) FROM pets");
    $petCount = $stmt->fetchColumn();

    if ($petCount == 0) {
        seedInitialData($pdo, $driver);
    }
}

/**
 * Seed data awal
 */
function seedInitialData($pdo, $driver = 'mysql') {
    // 1. Seed Users
    $users = [
        [
            'id' => 'usr-admin',
            'name' => 'Admin PawMate',
            'email' => 'admin@pawmate.id',
            'phone' => '081299887766',
            'password' => password_hash('admin', PASSWORD_DEFAULT),
            'role' => 'admin',
            'address' => 'Jl. Melati Indah No. 12, Jakarta',
            'occupation' => 'Shelter Manager PawMate',
            'registered_at' => '2026-08-01'
        ],
        [
            'id' => 'usr-demo',
            'name' => 'Rista Purnamasari',
            'email' => 'rista@example.com',
            'phone' => '081234567890',
            'password' => password_hash('password', PASSWORD_DEFAULT),
            'role' => 'user',
            'address' => 'Jl. Cempaka Putih No. 45, Jakarta Pusat',
            'occupation' => 'Creative Designer & Dog Lover',
            'registered_at' => '2026-09-10'
        ]
    ];

    $userSql = ($driver === 'sqlite')
        ? "INSERT OR IGNORE INTO users (id, name, email, phone, password, role, address, occupation, registered_at) VALUES (:id, :name, :email, :phone, :password, :role, :address, :occupation, :registered_at)"
        : "INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password`, `role`, `address`, `occupation`, `registered_at`) VALUES (:id, :name, :email, :phone, :password, :role, :address, :occupation, :registered_at) ON DUPLICATE KEY UPDATE `name` = VALUES(`name`)";
    
    $stmtUser = $pdo->prepare($userSql);
    foreach ($users as $u) {
        $stmtUser->execute($u);
    }

    // 2. Seed Pets
    $pets = [
        [
            'id' => 'pet-1',
            'name' => 'Milo',
            'species' => 'Anjing',
            'breed' => 'Golden Retriever Mix',
            'age' => '1.5 Tahun',
            'gender' => 'Jantan',
            'size' => 'Sedang',
            'location' => 'Jakarta Selatan',
            'status' => 'Tersedia',
            'image' => 'https://images.unsplash.com/photo-1552053831-71594a27632d?auto=format&fit=crop&w=800&q=80',
            'gallery' => json_encode([
                'https://images.unsplash.com/photo-1552053831-71594a27632d?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1537151625747-768eb6cf92b2?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1583511655857-d19b40a7a54e?auto=format&fit=crop&w=800&q=80'
            ]),
            'description' => 'Milo adalah anjing berenergi positif yang sangat menyukai bermain bola dan ramah kepada anak-anak. Milo diselamatkan dari shelter lingkungan dan siap menjadi sahabat setia keluarga Anda.',
            'temperament' => json_encode(['Ramah', 'Aktif', 'Suka Bermain', 'Penyayang']),
            'health' => 'Sehat prima, sudah disteril, bebas kutu dan jamur.',
            'vaccinated' => 'Lengkap (Rabies, DHPP)',
            'care_needs' => 'Membutuhkan jalan pagi minimal 30 menit dan ruang gerak yang cukup.',
            'created_at' => '2026-09-15'
        ],
        [
            'id' => 'pet-2',
            'name' => 'Luna',
            'species' => 'Kucing',
            'breed' => 'British Shorthair Mix',
            'age' => '8 Bulan',
            'gender' => 'Betina',
            'size' => 'Kecil',
            'location' => 'Bandung',
            'status' => 'Tersedia',
            'image' => 'https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?auto=format&fit=crop&w=800&q=80',
            'gallery' => json_encode([
                'https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1573865526739-10659fec78a5?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1495360010541-f48722b34f7d?auto=format&fit=crop&w=800&q=80'
            ]),
            'description' => 'Luna adalah kucing manis bermata bulat yang suka mendengkur ketika dielus di bawah dagu. Sangat tenang dan cocok untuk yang tinggal di apartemen atau rumah minimalis.',
            'temperament' => json_encode(['Manja', 'Tenang', 'Pintar', 'Indoor Cat']),
            'health' => 'Sangat sehat, rutin potong kuku, telinga bersih.',
            'vaccinated' => 'Lengkap (Tricat, Rabies)',
            'care_needs' => 'Penyisiran bulu halus 2x seminggu dan litter box yang selalu bersih.',
            'created_at' => '2026-09-20'
        ],
        [
            'id' => 'pet-3',
            'name' => 'Coco',
            'species' => 'Anjing',
            'breed' => 'Corgi Mix',
            'age' => '2 Tahun',
            'gender' => 'Jantan',
            'size' => 'Sedang',
            'location' => 'Tangerang Selatan',
            'status' => 'Tersedia',
            'image' => 'https://images.unsplash.com/photo-1612536057832-2ff7ead58194?auto=format&fit=crop&w=800&q=80',
            'gallery' => json_encode([
                'https://images.unsplash.com/photo-1612536057832-2ff7ead58194?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1589941013453-ec89f33b5e95?auto=format&fit=crop&w=800&q=80'
            ]),
            'description' => 'Coco berkepribadian ceria dan menggemaskan dengan langkah kakinya yang lincah. Sudah terlatih buang air di pee pad dan mengerti perintah dasar seperti duduk dan salaman.',
            'temperament' => json_encode(['Cerdas', 'Lincah', 'Setia', 'Mudah Dilatih']),
            'health' => 'Sehat, sudah vaksinasi tahunan & obat cacing rutin.',
            'vaccinated' => 'Lengkap',
            'care_needs' => 'Pola makan teratur untuk menjaga berat badan ideal.',
            'created_at' => '2026-09-22'
        ],
        [
            'id' => 'pet-4',
            'name' => 'Bella',
            'species' => 'Kucing',
            'breed' => 'Persian Calico',
            'age' => '1 Tahun',
            'gender' => 'Betina',
            'size' => 'Kecil',
            'location' => 'Jakarta Barat',
            'status' => 'Tersedia',
            'image' => 'https://images.unsplash.com/photo-1533738363-b7f9aef128ce?auto=format&fit=crop&w=800&q=80',
            'gallery' => json_encode([
                'https://images.unsplash.com/photo-1533738363-b7f9aef128ce?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1561948955-570b270e7c36?auto=format&fit=crop&w=800&q=80'
            ]),
            'description' => 'Bella adalah kucing berbulu lebat tiga warna yang cantik jelita. Memiliki pembawaan anggun dan senang bersantai di dekat jendela menikmati sinar matahari sore.',
            'temperament' => json_encode(['Anggun', 'Penyabar', 'Pemalu Awalnya', 'Lembut']),
            'health' => 'Sudah steril, gigi bersih, mata sehat terawat.',
            'vaccinated' => 'Lengkap (Tricat & Rabies)',
            'care_needs' => 'Penyisiran bulu setiap hari dan ruangan sejuk (ber-AC lebih disukai).',
            'created_at' => '2026-09-25'
        ],
        [
            'id' => 'pet-5',
            'name' => 'Mochi',
            'species' => 'Kucing',
            'breed' => 'Domestic Short Hair',
            'age' => '5 Bulan',
            'gender' => 'Jantan',
            'size' => 'Kecil',
            'location' => 'Depok',
            'status' => 'Tersedia',
            'image' => 'https://images.unsplash.com/photo-1574158622682-e40e69881006?auto=format&fit=crop&w=800&q=80',
            'gallery' => json_encode([
                'https://images.unsplash.com/photo-1574158622682-e40e69881006?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1543852786-1cf6624b9987?auto=format&fit=crop&w=800&q=80'
            ]),
            'description' => 'Kitten mungil putih dengan motif belang abu yang super aktif! Suka mengejar tali dan bola kerincing. Sangat sosial dan mudah akrab dengan siapa saja.',
            'temperament' => json_encode(['Super Aktif', 'Pemberani', 'Fasih Mengeong', 'Sosial']),
            'health' => 'Sehat, sudah obat cacing, bebas kutu.',
            'vaccinated' => 'Vaksin Tahap 1 Selesai',
            'care_needs' => 'Makanan kitten bernutrisi tinggi dan mainan interaktif.',
            'created_at' => '2026-09-28'
        ],
        [
            'id' => 'pet-6',
            'name' => 'Oreo',
            'species' => 'Anjing',
            'breed' => 'Border Collie Mix',
            'age' => '3 Tahun',
            'gender' => 'Jantan',
            'size' => 'Sedang',
            'location' => 'Bekasi',
            'status' => 'Tersedia',
            'image' => 'https://images.unsplash.com/photo-1561037404-61cd46aa615b?auto=format&fit=crop&w=800&q=80',
            'gallery' => json_encode([
                'https://images.unsplash.com/photo-1561037404-61cd46aa615b?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1517849845537-4d257902454a?auto=format&fit=crop&w=800&q=80'
            ]),
            'description' => 'Oreo adalah teman petualang sejati. Memiliki kecerdasan tinggi, sangat cepat belajar trik baru, dan sangat protektif serta menyayangi pemiliknya.',
            'temperament' => json_encode(['Cerdas Luar Biasa', 'Setia', 'Fokus', 'Protektif']),
            'health' => 'Prima & atletis, sudah vaksin lengkap dan steril.',
            'vaccinated' => 'Lengkap',
            'care_needs' => 'Memerlukan latihan stimulasi mental dan olahraga harian.',
            'created_at' => '2026-10-01'
        ],
        [
            'id' => 'pet-7',
            'name' => 'Cleo',
            'species' => 'Kucing',
            'breed' => 'Siamese Mix',
            'age' => '1.2 Tahun',
            'gender' => 'Betina',
            'size' => 'Kecil',
            'location' => 'Bogor',
            'status' => 'Tersedia',
            'image' => 'https://images.unsplash.com/photo-1513360309081-36f20ca480d0?auto=format&fit=crop&w=800&q=80',
            'gallery' => json_encode([
                'https://images.unsplash.com/photo-1513360309081-36f20ca480d0?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1526336024174-e58f5cdd8e13?auto=format&fit=crop&w=800&q=80'
            ]),
            'description' => 'Cleo bermata biru kristal menawan dengan suara vokal yang lembut menyapa saat pemilik pulang ke rumah. Sangat suka tidur melingkar di atas selimut hangat.',
            'temperament' => json_encode(['Komunikatif', 'Hangat', 'Penyayang', 'Manja']),
            'health' => 'Sehat & terawat, sudah steril.',
            'vaccinated' => 'Lengkap',
            'care_needs' => 'Tempat tidur yang hangat dan perhatian penuh kasih sayang.',
            'created_at' => '2026-10-03'
        ],
        [
            'id' => 'pet-8',
            'name' => 'Rocky',
            'species' => 'Anjing',
            'breed' => 'Beagle',
            'age' => '2.5 Tahun',
            'gender' => 'Jantan',
            'size' => 'Sedang',
            'location' => 'Jakarta Utara',
            'status' => 'Tersedia',
            'image' => 'https://images.unsplash.com/photo-1505628346881-b72b27e84530?auto=format&fit=crop&w=800&q=80',
            'gallery' => json_encode([
                'https://images.unsplash.com/photo-1505628346881-b72b27e84530?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1544568100-847a948585b9?auto=format&fit=crop&w=800&q=80'
            ]),
            'description' => 'Rocky adalah anjing lucu bertelinga panjang yang selalu ceria mengendus jejak petualangan baru. Sangat bersahabat dengan anak-anak maupun anjing lain.',
            'temperament' => json_encode(['Ceria', 'Eksploratif', 'Ramah', 'Suka Mencium Aroma']),
            'health' => 'Sangat sehat, rutin kontrol ke dokter hewan.',
            'vaccinated' => 'Lengkap (DHPPi, Rabies)',
            'care_needs' => 'Halaman berpagar aman dan jalan santai harian.',
            'created_at' => '2026-10-05'
        ]
    ];

    $petSql = ($driver === 'sqlite')
        ? "INSERT OR IGNORE INTO pets (id, name, species, breed, age, gender, size, location, status, image, gallery, description, temperament, health, vaccinated, care_needs, created_at) VALUES (:id, :name, :species, :breed, :age, :gender, :size, :location, :status, :image, :gallery, :description, :temperament, :health, :vaccinated, :care_needs, :created_at)"
        : "INSERT INTO `pets` (`id`, `name`, `species`, `breed`, `age`, `gender`, `size`, `location`, `status`, `image`, `gallery`, `description`, `temperament`, `health`, `vaccinated`, `care_needs`, `created_at`) VALUES (:id, :name, :species, :breed, :age, :gender, :size, :location, :status, :image, :gallery, :description, :temperament, :health, :vaccinated, :care_needs, :created_at) ON DUPLICATE KEY UPDATE `name` = VALUES(`name`)";

    $stmtPet = $pdo->prepare($petSql);
    foreach ($pets as $p) {
        $stmtPet->execute($p);
    }

    // 3. Seed Application
    $applications = [
        [
            'id' => 'app-1001',
            'user_id' => 'usr-demo',
            'pet_id' => 'pet-1',
            'user_name' => 'Rista Purnamasari',
            'user_email' => 'rista@example.com',
            'user_phone' => '081234567890',
            'pet_name' => 'Milo',
            'pet_species' => 'Anjing',
            'pet_image' => 'https://images.unsplash.com/photo-1552053831-71594a27632d?auto=format&fit=crop&w=800&q=80',
            'address' => 'Jl. Cempaka Putih No. 45, Jakarta Pusat',
            'occupation' => 'Creative Designer',
            'housing_type' => 'Rumah dengan Halaman Berpagar',
            'pet_experience' => 'Pernah memelihara anjing Golden selama 7 tahun.',
            'adoption_reason' => 'Mencari sahabat baru setelah anjing sebelumnya meninggal karena usia tua. Keluarga kami sangat menyukai anjing aktif.',
            'commitment_agreed' => 1,
            'status' => 'Menunggu',
            'admin_notes' => 'Sedang menunggu jadwal wawancara virtual dengan pemohon.',
            'applied_at' => '2026-10-10 14:30:00'
        ]
    ];

    $appSql = ($driver === 'sqlite')
        ? "INSERT OR IGNORE INTO applications (id, user_id, pet_id, user_name, user_email, user_phone, pet_name, pet_species, pet_image, address, occupation, housing_type, pet_experience, adoption_reason, commitment_agreed, status, admin_notes, applied_at) VALUES (:id, :user_id, :pet_id, :user_name, :user_email, :user_phone, :pet_name, :pet_species, :pet_image, :address, :occupation, :housing_type, :pet_experience, :adoption_reason, :commitment_agreed, :status, :admin_notes, :applied_at)"
        : "INSERT INTO `applications` (`id`, `user_id`, `pet_id`, `user_name`, `user_email`, `user_phone`, `pet_name`, `pet_species`, `pet_image`, `address`, `occupation`, `housing_type`, `pet_experience`, `adoption_reason`, `commitment_agreed`, `status`, `admin_notes`, `applied_at`) VALUES (:id, :user_id, :pet_id, :user_name, :user_email, :user_phone, :pet_name, :pet_species, :pet_image, :address, :occupation, :housing_type, :pet_experience, :adoption_reason, :commitment_agreed, :status, :admin_notes, :applied_at) ON DUPLICATE KEY UPDATE `status` = VALUES(`status`)";

    $stmtApp = $pdo->prepare($appSql);
    foreach ($applications as $a) {
        $stmtApp->execute($a);
    }

    // 4. Seed Favorites
    $favorites = [
        ['user_id' => 'usr-demo', 'pet_id' => 'pet-2'],
        ['user_id' => 'usr-demo', 'pet_id' => 'pet-4']
    ];

    $stmtFav = $pdo->prepare("INSERT OR IGNORE INTO favorites (user_id, pet_id) VALUES (:user_id, :pet_id)");
    if ($driver !== 'sqlite') {
        $stmtFav = $pdo->prepare("INSERT IGNORE INTO `favorites` (`user_id`, `pet_id`) VALUES (:user_id, :pet_id)");
    }
    foreach ($favorites as $f) {
        $stmtFav->execute($f);
    }
}
