<?php
/**
 * PawMate - Manajemen Data Hewan Shelter (PHP & MySQL XAMPP)
 */
require_once __DIR__ . '/config/auth.php';

if (!isAdmin()) {
    $db = getDB();
    $stmtAdmin = $db->query("SELECT * FROM users WHERE role = 'admin' LIMIT 1");
    $demoAdmin = $stmtAdmin->fetch();
    if ($demoAdmin) loginUser($demoAdmin);
    else { header('Location: login.php'); exit; }
}

$db = getDB();

// Handle Delete Pet
if (isset($_GET['delete'])) {
    $delId = $_GET['delete'];
    $stmtDel = $db->prepare("DELETE FROM pets WHERE id = ?");
    $stmtDel->execute([$delId]);
    setFlash('info', 'Hewan Dihapus', "Data hewan ($delId) berhasil dihapus dari database MySQL.");
    header('Location: admin-animals.php');
    exit;
}

// Handle Add / Edit Pet POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_pet') {
    $petId = trim($_POST['pet_id'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $species = trim($_POST['species'] ?? 'Anjing');
    $breed = trim($_POST['breed'] ?? 'Mix');
    $age = trim($_POST['age'] ?? '1 Tahun');
    $gender = trim($_POST['gender'] ?? 'Jantan');
    $size = trim($_POST['size'] ?? 'Sedang');
    $status = trim($_POST['status'] ?? 'Tersedia');
    $location = trim($_POST['location'] ?? 'Jakarta');
    $image = trim($_POST['image'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $health = trim($_POST['health'] ?? 'Sehat terawat');
    $vaccinated = trim($_POST['vaccinated'] ?? 'Lengkap');
    $careNeeds = trim($_POST['care_needs'] ?? 'Kasih sayang harian');

    if (empty($image)) {
        $image = 'https://images.unsplash.com/photo-1543466835-00a7907e9de1?auto=format&fit=crop&w=800&q=80';
    }

    $galleryJson = json_encode([$image]);
    $temperamentJson = json_encode(['Ramah', 'Lincah', 'Penyayang']);

    if (empty($petId)) {
        // Tambah Hewan Baru (INSERT)
        $newId = 'pet-' . time();
        $today = date('Y-m-d');
        $stmtInsert = $db->prepare("INSERT INTO pets 
            (id, name, species, breed, age, gender, size, location, status, image, gallery, description, temperament, health, vaccinated, care_needs, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmtInsert->execute([
            $newId, $name, $species, $breed, $age, $gender, $size, $location, $status,
            $image, $galleryJson, $description, $temperamentJson, $health, $vaccinated, $careNeeds, $today
        ]);
        setFlash('success', 'Hewan Ditambahkan', "Data anabul $name berhasil disimpan ke database MySQL.");
    } else {
        // Edit Hewan (UPDATE)
        $stmtUpdate = $db->prepare("UPDATE pets SET 
            name = ?, species = ?, breed = ?, age = ?, gender = ?, size = ?, location = ?, status = ?,
            image = ?, gallery = ?, description = ?, health = ?, vaccinated = ?, care_needs = ?
            WHERE id = ?");
        $stmtUpdate->execute([
            $name, $species, $breed, $age, $gender, $size, $location, $status,
            $image, $galleryJson, $description, $health, $vaccinated, $careNeeds, $petId
        ]);
        setFlash('success', 'Data Diperbarui', "Informasi $name berhasil diperbarui di database.");
    }

    header('Location: admin-animals.php');
    exit;
}

// Filter & Pencarian
$q = trim($_GET['q'] ?? '');
$filterSpecies = trim($_GET['species'] ?? 'all');
$filterStatus = trim($_GET['status'] ?? 'all');

$sql = "SELECT * FROM pets WHERE 1=1";
$params = [];

if (!empty($q)) {
    $sql .= " AND (name LIKE ? OR breed LIKE ? OR id LIKE ? OR location LIKE ?)";
    $term = "%$q%";
    $params = array_merge($params, [$term, $term, $term, $term]);
}

if ($filterSpecies !== 'all' && !empty($filterSpecies)) {
    $sql .= " AND species = ?";
    $params[] = $filterSpecies;
}

if ($filterStatus !== 'all' && !empty($filterStatus)) {
    $sql .= " AND status = ?";
    $params[] = $filterStatus;
}

$sql .= " ORDER BY created_at DESC, id DESC";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$pets = $stmt->fetchAll();

$pageTitle = "Manajemen Data Hewan";
$includeAdminCss = true;
$activeAdminNav = 'animals';
require_once __DIR__ . '/includes/header.php';
?>

  <div class="admin-layout">
    <?php require_once __DIR__ . '/includes/admin-sidebar.php'; ?>

    <main class="admin-main">
      <header class="admin-topbar">
        <div class="admin-topbar-left">
          <button class="admin-menu-toggle" id="admin-sidebar-toggle">
            <i class="fa-solid fa-bars"></i>
          </button>
          <div>
            <h1 class="admin-page-title">Manajemen Data Hewan</h1>
          </div>
        </div>

        <div class="admin-topbar-right">
          <button class="btn btn-primary" onclick="openAddPetModal()">
            <i class="fa-solid fa-plus"></i> Tambah Hewan Baru
          </button>
        </div>
      </header>

      <div class="admin-content-body">
        <!-- Control Bar: Search & Filter -->
        <div class="admin-panel" style="margin-bottom: 1.5rem; padding: 1.25rem 1.5rem;">
          <form method="GET" action="admin-animals.php" style="display:flex; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap;">
            <div style="display:flex; align-items:center; gap:0.75rem; flex-grow:1; max-width:400px;">
              <div class="input-with-icon" style="width:100%;">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" name="q" placeholder="Cari nama, ras, atau ID..." value="<?= htmlspecialchars($q) ?>">
              </div>
            </div>

            <div style="display:flex; align-items:center; gap:0.75rem; flex-wrap:wrap;">
              <select name="species" class="form-control" style="width:auto; padding:0.6rem 2rem 0.6rem 1rem;" onchange="this.form.submit()">
                <option value="all" <?= $filterSpecies === 'all' ? 'selected' : '' ?>>Semua Jenis</option>
                <option value="Anjing" <?= $filterSpecies === 'Anjing' ? 'selected' : '' ?>>Anjing</option>
                <option value="Kucing" <?= $filterSpecies === 'Kucing' ? 'selected' : '' ?>>Kucing</option>
              </select>

              <select name="status" class="form-control" style="width:auto; padding:0.6rem 2rem 0.6rem 1rem;" onchange="this.form.submit()">
                <option value="all" <?= $filterStatus === 'all' ? 'selected' : '' ?>>Semua Status</option>
                <option value="Tersedia" <?= $filterStatus === 'Tersedia' ? 'selected' : '' ?>>Tersedia</option>
                <option value="Dalam Proses" <?= $filterStatus === 'Dalam Proses' ? 'selected' : '' ?>>Dalam Proses</option>
                <option value="Sudah Diadopsi" <?= $filterStatus === 'Sudah Diadopsi' ? 'selected' : '' ?>>Sudah Diadopsi</option>
              </select>

              <button type="submit" class="btn btn-secondary btn-sm" title="Terapkan">
                <i class="fa-solid fa-filter"></i>
              </button>
              <a href="admin-animals.php" class="btn btn-secondary btn-sm" title="Reset">
                <i class="fa-solid fa-rotate-left"></i>
              </a>
            </div>
          </form>
        </div>

        <!-- Animals Table Panel -->
        <div class="admin-panel">
          <div class="admin-panel-header">
            <div>
              <h3 class="admin-panel-title">Daftar Hewan Shelter (MySQL)</h3>
              <p class="admin-panel-sub">Total <?= count($pets) ?> data anabul tercatat dalam database</p>
            </div>
          </div>

          <div class="admin-table-wrap">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>Hewan</th>
                  <th>ID</th>
                  <th>Spesies & Ras</th>
                  <th>Gender</th>
                  <th>Usia</th>
                  <th>Lokasi</th>
                  <th>Status</th>
                  <th style="text-align:right;">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($pets)): ?>
                  <tr>
                    <td colspan="8" style="text-align:center; padding:3rem; color:var(--text-muted);">
                      <i class="fa-solid fa-dog" style="font-size:2rem; margin-bottom:0.5rem; display:block;"></i>
                      Tidak ada hewan yang sesuai dengan filter atau kata kunci.
                    </td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($pets as $pet): 
                    $isMale = $pet['gender'] === 'Jantan';
                    $statusBadge = 'badge-approved';
                    if ($pet['status'] === 'Dalam Proses') $statusBadge = 'badge-process';
                    if ($pet['status'] === 'Sudah Diadopsi') $statusBadge = 'badge-rejected';
                  ?>
                    <tr>
                      <td>
                        <div style="display:flex; align-items:center; gap:0.75rem;">
                          <img src="<?= htmlspecialchars($pet['image']) ?>" alt="<?= htmlspecialchars($pet['name']) ?>" class="admin-pet-thumb">
                          <div>
                            <div style="font-weight:700; font-size:1rem;"><?= htmlspecialchars($pet['name']) ?></div>
                            <small style="color:var(--text-muted);"><?= htmlspecialchars($pet['size'] ?: 'Sedang') ?></small>
                          </div>
                        </div>
                      </td>
                      <td><code><?= htmlspecialchars($pet['id']) ?></code></td>
                      <td>
                        <span><?= htmlspecialchars($pet['species']) ?></span><br>
                        <small style="color:var(--text-muted);"><?= htmlspecialchars($pet['breed'] ?: 'Mix') ?></small>
                      </td>
                      <td>
                        <span class="pet-gender-badge <?= $isMale ? 'gender-male' : 'gender-female' ?>">
                          <i class="fa-solid <?= $isMale ? 'fa-mars' : 'fa-venus' ?>"></i> <?= htmlspecialchars($pet['gender']) ?>
                        </span>
                      </td>
                      <td><?= htmlspecialchars($pet['age']) ?></td>
                      <td><?= htmlspecialchars($pet['location']) ?></td>
                      <td><span class="badge <?= $statusBadge ?>"><?= htmlspecialchars($pet['status']) ?></span></td>
                      <td style="text-align:right;">
                        <div class="table-actions-inline">
                          <button class="btn-table-action" onclick='openEditPetModal(<?= json_encode($pet) ?>)' title="Edit Data">
                            <i class="fa-solid fa-pen-to-square"></i>
                          </button>
                          <a href="animal-detail.php?id=<?= urlencode($pet['id']) ?>" target="_blank" class="btn-table-action" title="Pratinjau Web">
                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                          </a>
                          <a href="admin-animals.php?delete=<?= urlencode($pet['id']) ?>" onclick="return confirm('Hapus hewan <?= htmlspecialchars($pet['name']) ?> dari database MySQL?')" class="btn-table-action delete" title="Hapus Hewan">
                            <i class="fa-solid fa-trash-can"></i>
                          </a>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </main>
  </div>

  <!-- CRUD Modal Form -->
  <div class="modal-backdrop" id="pet-form-modal">
    <div class="modal-card" style="max-width: 650px;">
      <div class="modal-header">
        <h3 class="modal-title" id="pet-modal-title">
          <i class="fa-solid fa-plus" style="color:var(--primary)"></i> Tambah Hewan Baru
        </h3>
        <button class="modal-close" onclick="closePetFormModal()"><i class="fa-solid fa-xmark"></i></button>
      </div>

      <form method="POST" action="admin-animals.php" id="pet-crud-form">
        <input type="hidden" name="action" value="save_pet">
        <input type="hidden" name="pet_id" id="pet-form-id" value="">

        <div class="modal-body">
          <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
            <div class="form-group">
              <label class="form-label" for="pet-input-name">Nama Anabul <span class="required">*</span></label>
              <input type="text" id="pet-input-name" name="name" class="form-control" placeholder="Cth: Milo" required>
            </div>
            <div class="form-group">
              <label class="form-label" for="pet-input-species">Jenis Spesies <span class="required">*</span></label>
              <select id="pet-input-species" name="species" class="form-control" required>
                <option value="Anjing">Anjing</option>
                <option value="Kucing">Kucing</option>
              </select>
            </div>
          </div>

          <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
            <div class="form-group">
              <label class="form-label" for="pet-input-breed">Ras / Keturunan</label>
              <input type="text" id="pet-input-breed" name="breed" class="form-control" placeholder="Cth: Golden Retriever Mix">
            </div>
            <div class="form-group">
              <label class="form-label" for="pet-input-age">Usia <span class="required">*</span></label>
              <input type="text" id="pet-input-age" name="age" class="form-control" placeholder="Cth: 1.5 Tahun / 6 Bulan" required>
            </div>
          </div>

          <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:1rem;">
            <div class="form-group">
              <label class="form-label" for="pet-input-gender">Kelamin <span class="required">*</span></label>
              <select id="pet-input-gender" name="gender" class="form-control" required>
                <option value="Jantan">Jantan</option>
                <option value="Betina">Betina</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label" for="pet-input-size">Ukuran Tubuh</label>
              <select id="pet-input-size" name="size" class="form-control">
                <option value="Kecil">Kecil</option>
                <option value="Sedang">Sedang</option>
                <option value="Besar">Besar</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label" for="pet-input-status">Status Adopsi <span class="required">*</span></label>
              <select id="pet-input-status" name="status" class="form-control" required>
                <option value="Tersedia">Tersedia</option>
                <option value="Dalam Proses">Dalam Proses</option>
                <option value="Sudah Diadopsi">Sudah Diadopsi</option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label" for="pet-input-location">Lokasi Shelter / Domisili <span class="required">*</span></label>
            <input type="text" id="pet-input-location" name="location" class="form-control" placeholder="Cth: Jakarta Selatan" required>
          </div>

          <div class="form-group">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.4rem;">
              <label class="form-label" for="pet-input-image" style="margin:0;">URL Foto Anabul <span class="required">*</span></label>
              <button type="button" class="btn btn-secondary btn-sm" onclick="pickPresetPhoto()" style="padding:0.2rem 0.6rem; font-size:0.75rem;">
                <i class="fa-solid fa-wand-magic-sparkles"></i> Preset Foto Acak
              </button>
            </div>
            <input type="url" id="pet-input-image" name="image" class="form-control" placeholder="https://..." oninput="previewPetImage(this.value)" required>
            <div style="margin-top:0.75rem; text-align:center;">
              <img id="img-preview" src="" alt="Pratinjau Foto" style="max-height:140px; border-radius:12px; display:none; margin:0 auto; box-shadow:var(--shadow-sm); border:2px solid var(--border-light);">
            </div>
          </div>

          <div class="form-group">
            <label class="form-label" for="pet-input-desc">Cerita & Deskripsi Karakter</label>
            <textarea id="pet-input-desc" name="description" class="form-control" rows="3" placeholder="Ceritakan kepribadian anabul, kebiasaan lucu, atau latar belakang penyelamatan..."></textarea>
          </div>

          <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
            <div class="form-group">
              <label class="form-label" for="pet-input-health">Status Kesehatan</label>
              <input type="text" id="pet-input-health" name="health" class="form-control" placeholder="Sehat prima, steril...">
            </div>
            <div class="form-group">
              <label class="form-label" for="pet-input-vaccinated">Riwayat Vaksin</label>
              <input type="text" id="pet-input-vaccinated" name="vaccinated" class="form-control" placeholder="Lengkap (Rabies, DHPP)">
            </div>
          </div>

          <div class="form-group">
            <label class="form-label" for="pet-input-care">Kebutuhan Perawatan Khusus</label>
            <input type="text" id="pet-input-care" name="care_needs" class="form-control" placeholder="Cth: Jalan pagi 30 menit, ruangan ber-AC...">
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" onclick="closePetFormModal()">Batal</button>
          <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Simpan ke Database</button>
        </div>
      </form>
    </div>
  </div>

  <div id="toast-container"></div>

  <script>
    const PRESET_PHOTOS = [
      'https://images.unsplash.com/photo-1543466835-00a7907e9de1?auto=format&fit=crop&w=800&q=80',
      'https://images.unsplash.com/photo-1537151625747-768eb6cf92b2?auto=format&fit=crop&w=800&q=80',
      'https://images.unsplash.com/photo-1583511655857-d19b40a7a54e?auto=format&fit=crop&w=800&q=80',
      'https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?auto=format&fit=crop&w=800&q=80',
      'https://images.unsplash.com/photo-1573865526739-10659fec78a5?auto=format&fit=crop&w=800&q=80',
      'https://images.unsplash.com/photo-1548767797-d8c844163c4c?auto=format&fit=crop&w=800&q=80',
      'https://images.unsplash.com/photo-1533738363-b7f9aef128ce?auto=format&fit=crop&w=800&q=80',
      'https://images.unsplash.com/photo-1561948955-570b270e7c36?auto=format&fit=crop&w=800&q=80'
    ];

    function previewPetImage(url) {
      const img = document.getElementById('img-preview');
      if (url && (url.startsWith('http') || url.startsWith('data:'))) {
        img.src = url;
        img.style.display = 'block';
      } else {
        img.style.display = 'none';
      }
    }

    function pickPresetPhoto() {
      const rand = PRESET_PHOTOS[Math.floor(Math.random() * PRESET_PHOTOS.length)];
      document.getElementById('pet-input-image').value = rand;
      previewPetImage(rand);
    }

    function openAddPetModal() {
      document.getElementById('pet-crud-form').reset();
      document.getElementById('pet-form-id').value = '';
      document.getElementById('pet-modal-title').innerHTML = '<i class="fa-solid fa-plus" style="color:var(--primary)"></i> Tambah Hewan Baru';
      pickPresetPhoto();
      document.getElementById('pet-form-modal').classList.add('active');
    }

    function openEditPetModal(pet) {
      document.getElementById('pet-form-id').value = pet.id;
      document.getElementById('pet-input-name').value = pet.name;
      document.getElementById('pet-input-species').value = pet.species;
      document.getElementById('pet-input-breed').value = pet.breed || '';
      document.getElementById('pet-input-age').value = pet.age || '';
      document.getElementById('pet-input-gender').value = pet.gender;
      document.getElementById('pet-input-size').value = pet.size || 'Sedang';
      document.getElementById('pet-input-status').value = pet.status;
      document.getElementById('pet-input-location').value = pet.location || '';
      document.getElementById('pet-input-image').value = pet.image;
      document.getElementById('pet-input-desc').value = pet.description || '';
      document.getElementById('pet-input-health').value = pet.health || '';
      document.getElementById('pet-input-vaccinated').value = pet.vaccinated || '';
      document.getElementById('pet-input-care').value = pet.care_needs || '';

      previewPetImage(pet.image);
      document.getElementById('pet-modal-title').innerHTML = `<i class="fa-solid fa-pen-to-square" style="color:var(--primary)"></i> Edit Data ${pet.name}`;
      document.getElementById('pet-form-modal').classList.add('active');
    }

    function closePetFormModal() {
      document.getElementById('pet-form-modal').classList.remove('active');
    }

    // Auto open add modal if action=add in url
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('action') === 'add') {
      openAddPetModal();
    }

    // Sidebar toggle
    const toggleBtn = document.getElementById('admin-sidebar-toggle');
    const sidebar = document.querySelector('.admin-sidebar');
    if (toggleBtn && sidebar) {
      toggleBtn.addEventListener('click', () => {
        sidebar.classList.toggle('open');
      });
    }

    <?php $flash = getFlash(); if ($flash): ?>
    document.addEventListener('DOMContentLoaded', () => {
      showToast(<?= json_encode($flash['title']) ?>, <?= json_encode($flash['text']) ?>, <?= json_encode($flash['type']) ?>);
    });
    <?php endif; ?>

    function showToast(title, message = '', type = 'info') {
      let container = document.getElementById('toast-container');
      if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        document.body.appendChild(container);
      }
      const toast = document.createElement('div');
      toast.className = `toast ${type}`;
      toast.innerHTML = `
        <div class="toast-icon"><i class="fa-solid fa-circle-check"></i></div>
        <div class="toast-content"><div class="toast-title">${title}</div>${message ? `<div class="toast-desc">${message}</div>` : ''}</div>
        <button class="toast-close" onclick="this.parentElement.remove()"><i class="fa-solid fa-xmark"></i></button>
      `;
      container.appendChild(toast);
      setTimeout(() => { toast.remove(); }, 3500);
    }
  </script>
</body>
</html>
