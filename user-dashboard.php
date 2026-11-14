<?php
/**
 * PawMate - Dashboard Pengguna (PHP & MySQL)
 */
require_once __DIR__ . '/config/auth.php';
requireLogin('login.php');

$db = getDB();
$user = getCurrentUser();

// Handle Update Profile POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $occupation = trim($_POST['occupation'] ?? '');

    if (!empty($name)) {
        $stmtUpdate = $db->prepare("UPDATE users SET name = ?, phone = ?, address = ?, occupation = ? WHERE id = ?");
        $stmtUpdate->execute([$name, $phone, $address, $occupation, $user['id']]);

        // Refresh session
        $stmtUser = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmtUser->execute([$user['id']]);
        $refreshed = $stmtUser->fetch();
        loginUser($refreshed);
        $user = getCurrentUser();

        setFlash('success', 'Profil Diperbarui', 'Data akun Anda berhasil diperbarui di database.');
        header('Location: user-dashboard.php');
        exit;
    }
}

// Handle Cancel Application
if (isset($_GET['cancel_app'])) {
    $cancelId = $_GET['cancel_app'];
    $stmtCancel = $db->prepare("DELETE FROM applications WHERE id = ? AND user_id = ? AND status = 'Menunggu'");
    $stmtCancel->execute([$cancelId, $user['id']]);
    setFlash('info', 'Pengajuan Dibatalkan', 'Berkas pengajuan berhasil dibatalkan.');
    header('Location: user-dashboard.php');
    exit;
}

// Ambil Riwayat Pengajuan Adopsi Pengguna
$stmtApps = $db->prepare("SELECT a.*, p.name as current_pet_name, p.status as pet_current_status, p.breed as pet_breed 
    FROM applications a 
    LEFT JOIN pets p ON a.pet_id = p.id 
    WHERE a.user_id = ? 
    ORDER BY a.applied_at DESC");
$stmtApps->execute([$user['id']]);
$userApps = $stmtApps->fetchAll();

// Ambil Koleksi Hewan Favorit
$stmtFavs = $db->prepare("SELECT p.* FROM favorites f 
    JOIN pets p ON f.pet_id = p.id 
    WHERE f.user_id = ? 
    ORDER BY f.created_at DESC");
$stmtFavs->execute([$user['id']]);
$favPets = $stmtFavs->fetchAll();

$pageTitle = "Dashboard Pengguna";
$activePage = 'user-dashboard';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

  <main class="container" style="padding-top: 2rem; padding-bottom: 5rem;">
    <!-- Dashboard Header -->
    <div style="margin-bottom: 2rem;">
      <h1 style="font-size: 2.2rem; margin-bottom: 0.35rem;">Dashboard Anggota</h1>
      <p style="color: var(--text-muted); font-size: 1rem;">
        Pantau status verifikasi berkas adopsi dan kelola anabul favorit Anda. Data tersinkronisasi di XAMPP MySQL.
      </p>
    </div>

    <div style="display: grid; grid-template-columns: 300px 1fr; gap: 2.2rem; align-items: start;" class="dashboard-layout-grid">
      
      <!-- Profile Card Sidebar -->
      <aside style="background: #fff; border-radius: var(--radius-xl); padding: 2.2rem 1.8rem; box-shadow: var(--shadow-sm); border: 1px solid var(--border-light); text-align: center;">
        <div style="width: 90px; height: 90px; border-radius: 50%; background: linear-gradient(135deg, var(--primary) 0%, #FF8FAB 100%); color: white; font-size: 2.4rem; font-weight: 800; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.25rem; box-shadow: 0 8px 20px rgba(243, 107, 145, 0.3);">
          <?= strtoupper(substr($user['name'], 0, 1)) ?>
        </div>

        <h3 style="font-size: 1.35rem; margin-bottom: 0.25rem;"><?= htmlspecialchars($user['name']) ?></h3>
        <span class="badge badge-approved" style="font-size: 0.75rem; padding: 0.2rem 0.65rem;">
          <?= htmlspecialchars($user['occupation'] ?: 'Penyayang Anabul') ?>
        </span>

        <div style="margin: 1.5rem 0; border-top: 1px solid var(--border-light); border-bottom: 1px solid var(--border-light); padding: 1rem 0; font-size: 0.88rem; text-align: left; display: flex; flex-direction: column; gap: 0.6rem; color: var(--text-muted);">
          <div><i class="fa-solid fa-envelope" style="color: var(--primary); width: 22px;"></i> <?= htmlspecialchars($user['email']) ?></div>
          <div><i class="fa-solid fa-phone" style="color: var(--primary); width: 22px;"></i> <?= htmlspecialchars($user['phone'] ?: '-') ?></div>
          <div><i class="fa-solid fa-location-dot" style="color: var(--primary); width: 22px;"></i> <?= htmlspecialchars($user['address'] ?: 'Indonesia') ?></div>
          <div><i class="fa-regular fa-calendar-check" style="color: var(--primary); width: 22px;"></i> Bergabung: <?= htmlspecialchars($user['registered_at'] ?? date('Y-m-d')) ?></div>
        </div>

        <button onclick="document.getElementById('edit-profile-modal').classList.add('active')" class="btn btn-secondary btn-sm" style="width: 100%; justify-content: center;">
          <i class="fa-solid fa-pen-to-square"></i> Edit Data Diri
        </button>

        <!-- Navigation Tabs -->
        <div style="display: flex; flex-direction: column; gap: 0.5rem; margin-top: 1.8rem; text-align: left;">
          <button class="dash-tab-btn active" id="tab-btn-apps" onclick="switchDashboardTab('apps', this)" style="display: flex; align-items: center; gap: 0.8rem; padding: 0.85rem 1.2rem; border-radius: var(--radius-pill); border: none; background: var(--primary-light); color: var(--primary); font-weight: 700; cursor: pointer; width: 100%;">
            <i class="fa-solid fa-file-signature"></i> Pengajuan Saya
            <span style="margin-left: auto; background: white; padding: 0.15rem 0.6rem; border-radius: var(--radius-pill); font-size: 0.75rem; color: var(--text-main); box-shadow: var(--shadow-sm);"><?= count($userApps) ?></span>
          </button>

          <button class="dash-tab-btn" id="tab-btn-favs" onclick="switchDashboardTab('favs', this)" style="display: flex; align-items: center; gap: 0.8rem; padding: 0.85rem 1.2rem; border-radius: var(--radius-pill); border: none; background: transparent; color: var(--text-muted); font-weight: 700; cursor: pointer; width: 100%;">
            <i class="fa-solid fa-heart"></i> Hewan Favorit
            <span style="margin-left: auto; background: white; padding: 0.15rem 0.6rem; border-radius: var(--radius-pill); font-size: 0.75rem; color: var(--text-main); box-shadow: var(--shadow-sm);"><?= count($favPets) ?></span>
          </button>
        </div>
      </aside>

      <!-- Main Content Panel -->
      <section style="background: #fff; border-radius: var(--radius-xl); padding: 2.5rem; box-shadow: var(--shadow-sm); border: 1px solid var(--border-light);">
        
        <!-- Tab 1: Pengajuan Adopsi -->
        <div id="tab-content-apps">
          <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 0.5rem;">
            <div>
              <h2 style="font-size: 1.4rem; margin-bottom: 0.2rem;">Riwayat Pengajuan Adopsi</h2>
              <p style="color: var(--text-muted); font-size: 0.9rem;">Status evaluasi dan wawancara oleh tim shelter PawMate.</p>
            </div>
            <a href="animals.php" class="btn btn-secondary btn-sm">
              <i class="fa-solid fa-plus"></i> Ajukan Adopsi Lainnya
            </a>
          </div>

          <?php if (empty($userApps)): ?>
            <div style="text-align: center; padding: 3.5rem 1.5rem; background: #FFF9FB; border-radius: var(--radius-lg); border: 1px dashed #F5C6D6;">
              <div style="font-size: 2.5rem; color: var(--primary); margin-bottom: 0.75rem;">
                <i class="fa-solid fa-file-circle-question"></i>
              </div>
              <h3 style="font-size: 1.2rem; margin-bottom: 0.35rem;">Belum Ada Pengajuan Adopsi</h3>
              <p style="color: var(--text-muted); font-size: 0.9rem; max-width: 420px; margin: 0 auto 1.25rem;">
                Anda belum mengajukan adopsi hewan apapun. Buka katalog untuk menemukan sahabat yang siap mengisi hari-hari Anda.
              </p>
              <a href="animals.php" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-paw"></i> Lihat Katalog Hewan
              </a>
            </div>
          <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 1.25rem;">
              <?php foreach ($userApps as $app): 
                $badgeClass = 'badge-pending';
                if ($app['status'] === 'Dalam Proses') $badgeClass = 'badge-process';
                if ($app['status'] === 'Disetujui') $badgeClass = 'badge-approved';
                if ($app['status'] === 'Ditolak') $badgeClass = 'badge-rejected';
              ?>
                <div style="background: #FFF9FB; border-radius: var(--radius-lg); padding: 1.5rem; border: 1px solid #F5DDE5; display: flex; gap: 1.5rem; align-items: flex-start; flex-wrap: wrap;">
                  <img src="<?= htmlspecialchars($app['pet_image']) ?>" alt="<?= htmlspecialchars($app['pet_name']) ?>" style="width: 110px; height: 110px; border-radius: var(--radius-md); object-fit: cover; flex-shrink: 0;">
                  
                  <div style="flex: 1; min-width: 250px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem; flex-wrap: wrap; gap: 0.5rem;">
                      <div>
                        <h3 style="font-size: 1.25rem; margin-bottom: 0.2rem;"><?= htmlspecialchars($app['pet_name']) ?> (<?= htmlspecialchars($app['pet_species']) ?>)</h3>
                        <span style="font-size: 0.82rem; color: var(--text-muted);">
                          Nomor Berkas: <strong><?= htmlspecialchars($app['id']) ?></strong> • Tanggal: <?= htmlspecialchars($app['applied_at']) ?>
                        </span>
                      </div>
                      <span class="badge <?= $badgeClass ?>" style="font-size: 0.85rem; padding: 0.3rem 0.8rem;">
                        <i class="fa-solid fa-circle" style="font-size: 0.5rem;"></i> <?= htmlspecialchars($app['status']) ?>
                      </span>
                    </div>

                    <div style="font-size: 0.88rem; color: var(--text-muted); margin-bottom: 0.85rem; line-height: 1.5;">
                      <strong>Hunian:</strong> <?= htmlspecialchars($app['housing_type']) ?><br>
                      <strong>Alasan:</strong> "<?= htmlspecialchars($app['adoption_reason']) ?>"
                    </div>

                    <?php if (!empty($app['admin_notes'])): ?>
                    <div style="background: #fff; border-radius: var(--radius-sm); padding: 0.75rem 1rem; border-left: 3px solid var(--primary); font-size: 0.85rem; color: var(--text-main); margin-bottom: 0.75rem;">
                      <strong style="color: var(--primary);"><i class="fa-solid fa-comment-dots"></i> Catatan Shelter:</strong> <?= htmlspecialchars($app['admin_notes']) ?>
                    </div>
                    <?php endif; ?>

                    <div style="display: flex; gap: 0.75rem; align-items: center;">
                      <a href="animal-detail.php?id=<?= urlencode($app['pet_id']) ?>" class="btn btn-secondary btn-sm">
                        Lihat Hewan
                      </a>
                      <?php if ($app['status'] === 'Menunggu'): ?>
                        <a href="user-dashboard.php?cancel_app=<?= urlencode($app['id']) ?>" onclick="return confirm('Batalkan pengajuan adopsi ini?')" class="btn btn-outline btn-sm" style="color: #DC2626; border-color: #FECACA;">
                          Batalkan Berkas
                        </a>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>

        <!-- Tab 2: Koleksi Favorit -->
        <div id="tab-content-favs" style="display: none;">
          <div style="margin-bottom: 1.5rem;">
            <h2 style="font-size: 1.4rem; margin-bottom: 0.2rem;">Hewan Favorit Anda</h2>
            <p style="color: var(--text-muted); font-size: 0.9rem;">Daftar anabul yang Anda simpan di wishlist.</p>
          </div>

          <?php if (empty($favPets)): ?>
            <div style="text-align: center; padding: 3.5rem 1.5rem; background: #FFF9FB; border-radius: var(--radius-lg); border: 1px dashed #F5C6D6;">
              <div style="font-size: 2.5rem; color: var(--primary); margin-bottom: 0.75rem;">
                <i class="fa-regular fa-heart"></i>
              </div>
              <h3 style="font-size: 1.2rem; margin-bottom: 0.35rem;">Belum Ada Hewan Favorit</h3>
              <p style="color: var(--text-muted); font-size: 0.9rem; max-width: 420px; margin: 0 auto 1.25rem;">
                Klik tombol ikon hati pada kartu anabul di katalog untuk menyimpannya ke daftar ini.
              </p>
              <a href="animals.php" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-paw"></i> Buka Katalog Hewan
              </a>
            </div>
          <?php else: ?>
            <div class="pet-grid" style="grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));">
              <?php foreach ($favPets as $fPet): 
                $isMale = $fPet['gender'] === 'Jantan';
              ?>
                <div class="pet-card" data-pet-id="<?= htmlspecialchars($fPet['id']) ?>">
                  <div class="pet-card-image-wrap">
                    <span class="pet-status-pill badge badge-approved">
                      <i class="fa-solid fa-circle" style="font-size:0.5rem"></i> <?= htmlspecialchars($fPet['status']) ?>
                    </span>
                    <button class="pet-favorite-btn active" data-favorite-pet="<?= htmlspecialchars($fPet['id']) ?>" onclick="handleToggleFavorite('<?= htmlspecialchars($fPet['id']) ?>', event)" title="Hapus dari favorit">
                      <i class="fa-solid fa-heart"></i>
                    </button>
                    <img src="<?= htmlspecialchars($fPet['image']) ?>" alt="<?= htmlspecialchars($fPet['name']) ?>">
                  </div>
                  <div class="pet-card-body">
                    <div class="pet-card-meta">
                      <h3 class="pet-name"><?= htmlspecialchars($fPet['name']) ?></h3>
                      <span class="pet-gender-badge <?= $isMale ? 'gender-male' : 'gender-female' ?>">
                        <i class="fa-solid <?= $isMale ? 'fa-mars' : 'fa-venus' ?>"></i> <?= htmlspecialchars($fPet['gender']) ?>
                      </span>
                    </div>
                    <span class="pet-breed-tag"><?= htmlspecialchars($fPet['breed'] ?: $fPet['species']) ?> • <?= htmlspecialchars($fPet['age']) ?></span>
                    <div class="pet-card-footer" style="margin-top: 1rem;">
                      <span class="pet-location"><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($fPet['location']) ?></span>
                      <a href="animal-detail.php?id=<?= urlencode($fPet['id']) ?>" class="btn btn-secondary btn-sm">Detail</a>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>

      </section>

    </div>
  </main>

  <!-- Edit Profile Modal -->
  <div class="modal-backdrop" id="edit-profile-modal">
    <div class="modal-card">
      <div class="modal-header">
        <h3 class="modal-title"><i class="fa-solid fa-user-pen" style="color:var(--primary)"></i> Edit Data Diri</h3>
        <button class="modal-close" onclick="document.getElementById('edit-profile-modal').classList.remove('active')"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <form method="POST" action="user-dashboard.php">
        <input type="hidden" name="action" value="update_profile">
        <div class="modal-body">
          <div class="form-group">
            <label class="form-label" for="edit-name">Nama Lengkap <span class="required">*</span></label>
            <input type="text" id="edit-name" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label" for="edit-phone">Nomor WhatsApp <span class="required">*</span></label>
            <input type="tel" id="edit-phone" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label" for="edit-occupation">Pekerjaan</label>
            <input type="text" id="edit-occupation" name="occupation" class="form-control" value="<?= htmlspecialchars($user['occupation'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label class="form-label" for="edit-address">Alamat Domisili</label>
            <textarea id="edit-address" name="address" class="form-control" rows="2"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" onclick="document.getElementById('edit-profile-modal').classList.remove('active')">Batal</button>
          <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan</button>
        </div>
      </form>
    </div>
  </div>

  <style>
    @media (max-width: 900px) {
      .dashboard-layout-grid {
        grid-template-columns: 1fr !important;
      }
    }
  </style>

  <script>
    function switchDashboardTab(tab, btn) {
      const tabApps = document.getElementById('tab-content-apps');
      const tabFavs = document.getElementById('tab-content-favs');
      const btnApps = document.getElementById('tab-btn-apps');
      const btnFavs = document.getElementById('tab-btn-favs');

      if (tab === 'apps') {
        tabApps.style.display = 'block';
        tabFavs.style.display = 'none';
        btnApps.style.background = 'var(--primary-light)';
        btnApps.style.color = 'var(--primary)';
        btnFavs.style.background = 'transparent';
        btnFavs.style.color = 'var(--text-muted)';
      } else {
        tabApps.style.display = 'none';
        tabFavs.style.display = 'block';
        btnFavs.style.background = 'var(--primary-light)';
        btnFavs.style.color = 'var(--primary)';
        btnApps.style.background = 'transparent';
        btnApps.style.color = 'var(--text-muted)';
      }
    }
  </script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
