<?php
/**
 * PawMate - Beranda Utama (PHP & MySQL)
 */
require_once __DIR__ . '/config/auth.php';

$db = getDB();
$currentUser = getCurrentUser();

// Ambil 4 hewan pilihan yang berstatus Tersedia
$stmtPets = $db->query("SELECT * FROM pets WHERE status = 'Tersedia' ORDER BY created_at DESC LIMIT 4");
$featuredPets = $stmtPets->fetchAll();

// Jika kurang dari 4, ambil semua hewan apapun statusnya
if (count($featuredPets) < 4) {
    $stmtAll = $db->query("SELECT * FROM pets ORDER BY created_at DESC LIMIT 4");
    $featuredPets = $stmtAll->fetchAll();
}

// Ambil ID favorit user jika sedang login
$userFavorites = [];
if ($currentUser) {
    $stmtFav = $db->prepare("SELECT pet_id FROM favorites WHERE user_id = ?");
    $stmtFav->execute([$currentUser['id']]);
    $userFavorites = $stmtFav->fetchAll(PDO::FETCH_COLUMN);
}

// Hitung statistik live dari database
$availableCount = $db->query("SELECT COUNT(*) FROM pets WHERE status = 'Tersedia'")->fetchColumn();
$approvedAppsCount = $db->query("SELECT COUNT(*) FROM applications WHERE status = 'Disetujui'")->fetchColumn();
$totalAdopted = 120 + (int)$approvedAppsCount;

$activePage = 'home';
$pageTitle = "Platform Adopsi Hewan Modern & Terpercaya";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

  <main>
    <!-- Hero Section -->
    <section class="hero-section">
      <div class="hero-shape-1"></div>
      <div class="hero-shape-2"></div>
      <div class="container hero-grid">
        <div class="hero-content">
          <div class="hero-badge-pill">
            <i class="fa-solid fa-heart" style="color:var(--primary)"></i> Platform Adopsi #1 Terverifikasi (PHP & MySQL)
          </div>
          <h1 class="hero-title">
            Temukan Teman Hidup Baru di <span class="highlight">PawMate</span>
          </h1>
          <p class="hero-desc">
            Beri kesempatan kedua bagi anabul terlantar untuk menemukan rumah yang hangat dan penuh cinta. Setiap pelukan mengubah hidup mereka selamanya.
          </p>

          <div class="hero-buttons">
            <a href="animals.php" class="btn btn-primary btn-lg">
              <i class="fa-solid fa-magnifying-glass"></i> Temukan Sahabat
            </a>
            <a href="#steps" class="btn btn-secondary btn-lg">
              <i class="fa-solid fa-circle-play"></i> Cara Adopsi
            </a>
          </div>

          <div class="hero-stats-mini">
            <div class="stat-item">
              <h3 id="stat-adopted-count"><?= $totalAdopted ?>+</h3>
              <p>Hewan Teradopsi</p>
            </div>
            <div class="stat-item">
              <h3 id="stat-available-count"><?= $availableCount ?>+</h3>
              <p>Siap Diadopsi</p>
            </div>
            <div class="stat-item">
              <h3>100%</h3>
              <p>Cinta & Vaksinasi</p>
            </div>
          </div>
        </div>

        <div class="hero-visual">
          <div class="hero-visual-card">
            <img src="https://images.unsplash.com/photo-1548199973-03cce0bbc87b?auto=format&fit=crop&w=1000&q=80" alt="Anjing dan Kucing Sahabat Bahagia PawMate">
            <div class="floating-paw-bubble">
              <i class="fa-solid fa-shield-cat"></i>
              <div>
                <strong>Kesehatan Terjamin</strong>
                <span>Vaksinasi & Steril Lengkap</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Quick Search Bar Section -->
    <section class="container" style="margin-top: -3.5rem; position: relative; z-index: 10;">
      <div class="search-box-card">
        <form method="GET" action="animals.php" class="search-filter-grid">
          <div class="filter-group">
            <label for="hero-search-input">Cari Nama / Ras</label>
            <div class="input-with-icon">
              <i class="fa-solid fa-magnifying-glass"></i>
              <input type="text" name="q" id="hero-search-input" placeholder="Cth: Milo, Luna, Corgi...">
            </div>
          </div>

          <div class="filter-group">
            <label for="hero-species-select">Jenis Hewan</label>
            <select name="species" id="hero-species-select">
              <option value="all">Semua Jenis</option>
              <option value="Anjing">Anjing 🐶</option>
              <option value="Kucing">Kucing 🐱</option>
            </select>
          </div>

          <div class="filter-group">
            <label for="hero-gender-select">Jenis Kelamin</label>
            <select name="gender" id="hero-gender-select">
              <option value="all">Semua Kelamin</option>
              <option value="Jantan">Jantan</option>
              <option value="Betina">Betina</option>
            </select>
          </div>

          <div class="filter-group">
            <label for="hero-location-select">Lokasi Shelter</label>
            <select name="location" id="hero-location-select">
              <option value="all">Semua Kota</option>
              <option value="Jakarta Selatan">Jakarta Selatan</option>
              <option value="Jakarta Barat">Jakarta Barat</option>
              <option value="Jakarta Utara">Jakarta Utara</option>
              <option value="Bandung">Bandung</option>
              <option value="Bogor">Bogor</option>
              <option value="Depok">Depok</option>
              <option value="Tangerang Selatan">Tangerang Selatan</option>
              <option value="Bekasi">Bekasi</option>
            </select>
          </div>

          <div class="filter-group" style="align-self: flex-end;">
            <button type="submit" class="btn btn-primary" style="height: 48px; width: 100%;">
              <i class="fa-solid fa-filter"></i> Cari Anabul
            </button>
          </div>
        </form>
      </div>
    </section>

    <!-- Kenapa Memilih PawMate -->
    <section class="section" id="about">
      <div class="container">
        <div class="section-header">
          <span class="badge badge-tag"><i class="fa-solid fa-sparkles"></i> Keunggulan Kami</span>
          <h2>Mengapa Mengadopsi Lewat PawMate?</h2>
          <p>Kami memastikan setiap hewan adopsi sehat, bahagia, dan siap menyambut keluarga barunya dengan standar terbaik.</p>
        </div>

        <div class="grid grid-3">
          <div class="feature-card">
            <div class="feature-icon">
              <i class="fa-solid fa-stethoscope"></i>
            </div>
            <h3 class="feature-title">Pemeriksaan Medis Menyeluruh</h3>
            <p class="feature-desc">Setiap anabul telah menjalani vaksinasi wajib, sterilisasi, pemberian obat cacing berkala, serta pemeriksaan fisik dokter hewan.</p>
          </div>

          <div class="feature-card">
            <div class="feature-icon">
              <i class="fa-solid fa-clipboard-check"></i>
            </div>
            <h3 class="feature-title">Proses Transparan & Mudah</h3>
            <p class="feature-desc">Pelacakan status berkas adopsi secara real-time langsung melalui dashboard pemohon dengan bimbingan tim shelter berpengalaman.</p>
          </div>

          <div class="feature-card">
            <div class="feature-icon">
              <i class="fa-solid fa-hand-holding-heart"></i>
            </div>
            <h3 class="feature-title">Bebas Biaya Komersial</h3>
            <p class="feature-desc">PawMate adalah inisiatif nirlaba. Kami berfokus murni pada kesejahteraan hewan dan keselamatan mereka seumur hidup.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Hewan Pilihan Terbaru (Dynamic from MySQL) -->
    <section class="section" style="background: #FFF9FB; border-top: 1px solid #FFE7EF; border-bottom: 1px solid #FFE7EF;">
      <div class="container">
        <div class="section-header">
          <span class="badge badge-tag"><i class="fa-solid fa-paw"></i> Rekomendasi Hari Ini</span>
          <h2>Anabul Menunggu Rumah Hangat</h2>
          <p>Mereka memiliki tatapan mata penuh harapan. Kenali karakter unik dan kisah perjalanan mereka.</p>
        </div>

        <div class="pet-grid" id="recommended-pets-container">
          <?php foreach ($featuredPets as $pet): 
            $isFav = in_array($pet['id'], $userFavorites);
            $isMale = $pet['gender'] === 'Jantan';
            $statusClass = 'badge-approved';
            if ($pet['status'] === 'Dalam Proses') $statusClass = 'badge-process';
            if ($pet['status'] === 'Sudah Diadopsi') $statusClass = 'badge-rejected';
          ?>
            <div class="pet-card" data-pet-id="<?= htmlspecialchars($pet['id']) ?>">
              <div class="pet-card-image-wrap">
                <span class="pet-status-pill badge <?= $statusClass ?>">
                  <i class="fa-solid fa-circle" style="font-size:0.5rem"></i> <?= htmlspecialchars($pet['status']) ?>
                </span>
                <button class="pet-favorite-btn <?= $isFav ? 'active' : '' ?>" data-favorite-pet="<?= htmlspecialchars($pet['id']) ?>" onclick="handleToggleFavorite('<?= htmlspecialchars($pet['id']) ?>', event)" title="Simpan ke favorit">
                  <i class="<?= $isFav ? 'fa-solid' : 'fa-regular' ?> fa-heart"></i>
                </button>
                <img src="<?= htmlspecialchars($pet['image']) ?>" alt="<?= htmlspecialchars($pet['name']) ?>" loading="lazy" onerror="this.src='https://images.unsplash.com/photo-1543466835-00a7907e9de1?auto=format&fit=crop&w=800&q=80'">
              </div>
              <div class="pet-card-body">
                <div class="pet-card-meta">
                  <h3 class="pet-name"><?= htmlspecialchars($pet['name']) ?></h3>
                  <span class="pet-gender-badge <?= $isMale ? 'gender-male' : 'gender-female' ?>">
                    <i class="fa-solid <?= $isMale ? 'fa-mars' : 'fa-venus' ?>"></i> <?= htmlspecialchars($pet['gender']) ?>
                  </span>
                </div>
                <span class="pet-breed-tag"><?= htmlspecialchars($pet['breed'] ?: $pet['species']) ?> • <?= htmlspecialchars($pet['age']) ?></span>
                
                <div class="pet-tags-row">
                  <span class="pet-tag"><i class="fa-solid fa-shield-cat"></i> <?= htmlspecialchars($pet['vaccinated'] ?: 'Vaksin Rutin') ?></span>
                  <span class="pet-tag"><i class="fa-solid fa-shapes"></i> <?= htmlspecialchars($pet['species']) ?></span>
                </div>

                <div class="pet-card-footer">
                  <span class="pet-location">
                    <i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($pet['location']) ?>
                  </span>
                  <a href="animal-detail.php?id=<?= urlencode($pet['id']) ?>" class="btn btn-secondary btn-sm">
                    Lihat Detail <i class="fa-solid fa-arrow-right" style="font-size:0.75rem"></i>
                  </a>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <div style="text-align: center; margin-top: 3rem;">
          <a href="animals.php" class="btn btn-primary btn-lg">
            <i class="fa-solid fa-grid-2"></i> Lihat Semua Hewan di Katalog
          </a>
        </div>
      </div>
    </section>

    <!-- Alur Adopsi (4 Langkah) -->
    <section class="section" id="steps">
      <div class="container">
        <div class="section-header">
          <span class="badge badge-tag"><i class="fa-solid fa-route"></i> Alur Transparan</span>
          <h2>4 Langkah Mudah Membawa Pulang Sahabat Baru</h2>
          <p>Proses selektif namun penuh kehangatan demi memastikan kecocokan antara anabul dan keluarga barunya.</p>
        </div>

        <div class="grid grid-4">
          <div class="step-card">
            <div class="step-number">1</div>
            <h3 class="step-title">Pilih Sahabat Anda</h3>
            <p class="step-desc">Jelajahi profil hewan, sesuaikan dengan hunian, aktivitas, dan karakter impian Anda.</p>
          </div>

          <div class="step-card">
            <div class="step-number">2</div>
            <h3 class="step-title">Isi Formulir Online</h3>
            <p class="step-desc">Lengkapi formulir pengajuan adopsi yang transparan melalui platform PawMate.</p>
          </div>

          <div class="step-card">
            <div class="step-number">3</div>
            <h3 class="step-title">Wawancara & Kunjungan</h3>
            <p class="step-desc">Tim shelter akan menghubungi via WhatsApp untuk diskusi singkat dan sesi tatap muka.</p>
          </div>

          <div class="step-card">
            <div class="step-number">4</div>
            <h3 class="step-title">Selamat Datang di Rumah!</h3>
            <p class="step-desc">Penyerahan dokumen adopsi, panduan adaptasi awal, dan sambut hari-hari penuh keceriaan.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Call to Action Banner -->
    <section class="container" style="margin-bottom: 5rem;">
      <div style="background: linear-gradient(135deg, var(--primary) 0%, #FF8FAB 100%); border-radius: var(--radius-xl); padding: 3.5rem 2.5rem; text-align: center; color: white; box-shadow: var(--shadow-lg);">
        <h2 style="font-size: 2.2rem; margin-bottom: 1rem; color: white;">Siap Menjadi Pahlawan Bagi Mereka?</h2>
        <p style="font-size: 1.1rem; max-width: 600px; margin: 0 auto 2rem; opacity: 0.95;">
          Satu keputusan kecil dari Anda adalah keajaiban seumur hidup bagi seekor anabul terlantar. Temukan sahabat sejatimu hari ini.
        </p>
        <a href="animals.php" class="btn btn-secondary btn-lg" style="background: white; color: var(--primary); font-weight: 800;">
          <i class="fa-solid fa-heart"></i> Mulai Adopsi Sekarang
        </a>
      </div>
    </section>
  </main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
