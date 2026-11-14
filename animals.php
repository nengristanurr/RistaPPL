<?php
/**
 * PawMate - Katalog Hewan Adopsi (PHP & MySQL)
 */
require_once __DIR__ . '/config/auth.php';

$db = getDB();
$currentUser = getCurrentUser();

// Ambil parameter filter dari GET
$q = trim($_GET['q'] ?? '');
$species = trim($_GET['species'] ?? 'all');
$gender = trim($_GET['gender'] ?? 'all');
$age = trim($_GET['age'] ?? 'all');
$status = trim($_GET['status'] ?? 'all');
$location = trim($_GET['location'] ?? 'all');

// Susun Query SQL Dinamis
$sql = "SELECT * FROM pets WHERE 1=1";
$params = [];

if (!empty($q)) {
    $sql .= " AND (name LIKE ? OR breed LIKE ? OR description LIKE ? OR location LIKE ?)";
    $searchTerm = "%$q%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if ($species !== 'all' && !empty($species)) {
    $sql .= " AND species = ?";
    $params[] = $species;
}

if ($gender !== 'all' && !empty($gender)) {
    $sql .= " AND gender = ?";
    $params[] = $gender;
}

if ($status !== 'all' && !empty($status)) {
    $sql .= " AND status = ?";
    $params[] = $status;
}

if ($location !== 'all' && !empty($location)) {
    $sql .= " AND location = ?";
    $params[] = $location;
}

if ($age !== 'all' && !empty($age)) {
    if ($age === 'kitten-puppy') {
        $sql .= " AND (age LIKE '%Bulan%' OR age LIKE '0.%')";
    } elseif ($age === 'adult') {
        $sql .= " AND (age LIKE '1%' OR age LIKE '2%' OR age LIKE '3%') AND age NOT LIKE '%Bulan%'";
    } elseif ($age === 'senior') {
        $sql .= " AND (age LIKE '4%' OR age LIKE '5%' OR age LIKE '6%' OR age LIKE '7%' OR age LIKE '8%')";
    }
}

$sql .= " ORDER BY (status = 'Tersedia') DESC, created_at DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$pets = $stmt->fetchAll();

// Ambil daftar favorit pengguna jika sudah login
$userFavorites = [];
if ($currentUser) {
    $stmtFav = $db->prepare("SELECT pet_id FROM favorites WHERE user_id = ?");
    $stmtFav->execute([$currentUser['id']]);
    $userFavorites = $stmtFav->fetchAll(PDO::FETCH_COLUMN);
}

$activePage = 'animals';
$pageTitle = "Katalog Hewan Adopsi";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

  <main class="container" style="padding-top: 2.5rem; padding-bottom: 5rem;">
    <!-- Page Header -->
    <div style="text-align: center; max-width: 650px; margin: 0 auto 2.5rem;">
      <span class="badge badge-tag" style="margin-bottom: 0.75rem; font-size: 0.85rem;">
        <i class="fa-solid fa-paw"></i> Temukan Sahabat Setia (MySQL)
      </span>
      <h1 style="font-size: 2.6rem; margin-bottom: 0.6rem;">Katalog Hewan Adopsi</h1>
      <p style="color: var(--text-muted); font-size: 1.05rem;">
        Pilih anabul yang cocok dengan gaya hidup dan hatimu. Data terhubung langsung dengan database shelter di XAMPP.
      </p>
    </div>

    <!-- Filter & Search Controls Bar -->
    <div class="search-box-card" style="margin-bottom: 2.5rem;">
      <form method="GET" action="animals.php" class="search-filter-grid" style="grid-template-columns: 2fr 1fr 1fr 1fr 1.2fr auto;">
        
        <!-- Search Input -->
        <div class="filter-group">
          <label for="filter-search">Cari Nama / Karakter</label>
          <div class="input-with-icon">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" name="q" id="filter-search" placeholder="Cari Milo, Luna, Corgi..." value="<?= htmlspecialchars($q) ?>">
          </div>
        </div>

        <!-- Species Filter -->
        <div class="filter-group">
          <label for="filter-species">Jenis Hewan</label>
          <select name="species" id="filter-species">
            <option value="all" <?= $species === 'all' ? 'selected' : '' ?>>Semua Jenis</option>
            <option value="Anjing" <?= $species === 'Anjing' ? 'selected' : '' ?>>Anjing 🐶</option>
            <option value="Kucing" <?= $species === 'Kucing' ? 'selected' : '' ?>>Kucing 🐱</option>
          </select>
        </div>

        <!-- Gender Filter -->
        <div class="filter-group">
          <label for="filter-gender">Jenis Kelamin</label>
          <select name="gender" id="filter-gender">
            <option value="all" <?= $gender === 'all' ? 'selected' : '' ?>>Semua Kelamin</option>
            <option value="Jantan" <?= $gender === 'Jantan' ? 'selected' : '' ?>>Jantan</option>
            <option value="Betina" <?= $gender === 'Betina' ? 'selected' : '' ?>>Betina</option>
          </select>
        </div>

        <!-- Age Filter -->
        <div class="filter-group">
          <label for="filter-age">Kelompok Usia</label>
          <select name="age" id="filter-age">
            <option value="all" <?= $age === 'all' ? 'selected' : '' ?>>Semua Usia</option>
            <option value="kitten-puppy" <?= $age === 'kitten-puppy' ? 'selected' : '' ?>>Anakan (&lt; 1 Tahun)</option>
            <option value="adult" <?= $age === 'adult' ? 'selected' : '' ?>>Dewasa (1 - 3 Tahun)</option>
            <option value="senior" <?= $age === 'senior' ? 'selected' : '' ?>>Senior (&gt; 3 Tahun)</option>
          </select>
        </div>

        <!-- Availability Status Filter -->
        <div class="filter-group">
          <label for="filter-status">Status Ketersediaan</label>
          <select name="status" id="filter-status">
            <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>Semua Status</option>
            <option value="Tersedia" <?= $status === 'Tersedia' ? 'selected' : '' ?>>Tersedia</option>
            <option value="Dalam Proses" <?= $status === 'Dalam Proses' ? 'selected' : '' ?>>Dalam Proses</option>
            <option value="Sudah Diadopsi" <?= $status === 'Sudah Diadopsi' ? 'selected' : '' ?>>Sudah Diadopsi</option>
          </select>
        </div>

        <!-- Submit & Reset Action -->
        <div class="filter-group" style="align-self: flex-end; display: flex; gap: 0.5rem;">
          <button type="submit" class="btn btn-primary" title="Terapkan Filter" style="height: 48px; padding: 0 1.25rem;">
            <i class="fa-solid fa-filter"></i>
          </button>
          <a href="animals.php" class="btn btn-secondary" title="Reset Filter" style="height: 48px; display: inline-flex; align-items: center; justify-content: center; padding: 0 1.1rem;">
            <i class="fa-solid fa-rotate-left"></i>
          </a>
        </div>
      </form>

      <!-- Quick Category Filter Chips -->
      <div style="display:flex; align-items:center; gap:0.6rem; margin-top:1.25rem; flex-wrap:wrap; border-top:1px solid #FFE7EF; padding-top:1rem;">
        <span style="font-size:0.85rem; font-weight:700; color:var(--text-muted);">Pilihan Cepat:</span>
        <a href="animals.php" class="btn btn-sm <?= ($species === 'all' && $status === 'all' && empty($q)) ? 'btn-primary' : 'btn-secondary' ?>" style="border-radius:var(--radius-pill);">
          Semua (<?= count($pets) ?>)
        </a>
        <a href="animals.php?species=Anjing" class="btn btn-sm <?= $species === 'Anjing' ? 'btn-primary' : 'btn-secondary' ?>" style="border-radius:var(--radius-pill);">
          🐶 Anjing
        </a>
        <a href="animals.php?species=Kucing" class="btn btn-sm <?= $species === 'Kucing' ? 'btn-primary' : 'btn-secondary' ?>" style="border-radius:var(--radius-pill);">
          🐱 Kucing
        </a>
        <a href="animals.php?status=Tersedia" class="btn btn-sm <?= $status === 'Tersedia' ? 'btn-primary' : 'btn-secondary' ?>" style="border-radius:var(--radius-pill);">
          ✨ Siap Adopsi
        </a>
      </div>
    </div>

    <!-- Results Header -->
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
      <div style="font-size: 1rem; color: var(--text-muted);">
        Menampilkan <strong style="color: var(--text-main);" id="results-count"><?= count($pets) ?></strong> anabul yang siap bertemu sahabat baru
      </div>
    </div>

    <!-- Pinterest Pet Grid -->
    <?php if (empty($pets)): ?>
      <div id="empty-state" style="text-align: center; padding: 4rem 1.5rem;">
        <div style="width: 100px; height: 100px; background: #FFE8F0; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; font-size: 2.5rem; color: var(--primary);">
          <i class="fa-solid fa-magnifying-glass"></i>
        </div>
        <h3 style="font-size: 1.4rem; margin-bottom: 0.5rem;">Tidak Ada Hewan yang Cocok</h3>
        <p style="color: var(--text-muted); max-width: 450px; margin: 0 auto 1.5rem;">
          Kami tidak menemukan anabul dengan filter yang Anda masukkan. Coba atur ulang pencarian atau lihat seluruh katalog.
        </p>
        <a href="animals.php" class="btn btn-secondary">
          <i class="fa-solid fa-rotate-left"></i> Reset Pencarian
        </a>
      </div>
    <?php else: ?>
      <div class="pet-grid" id="catalog-pet-grid">
        <?php foreach ($pets as $pet): 
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
    <?php endif; ?>
  </main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
