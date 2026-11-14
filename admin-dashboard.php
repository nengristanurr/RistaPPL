<?php
/**
 * PawMate - Admin Dashboard (PHP & MySQL XAMPP)
 */
require_once __DIR__ . '/config/auth.php';

// Auto-login as demo admin if accessed directly in development mode
if (!isAdmin()) {
    $db = getDB();
    $stmtAdmin = $db->query("SELECT * FROM users WHERE role = 'admin' LIMIT 1");
    $demoAdmin = $stmtAdmin->fetch();
    if ($demoAdmin) {
        loginUser($demoAdmin);
        setFlash('info', 'Sesi Pengurus Aktif', 'Masuk otomatis sebagai Admin PawMate.');
    } else {
        header('Location: login.php');
        exit;
    }
}

$db = getDB();
$currentAdmin = getCurrentUser();

// Handle Reset Demo Data POST
if (isset($_POST['action']) && $_POST['action'] === 'reset_demo') {
    $db->exec("DELETE FROM favorites");
    $db->exec("DELETE FROM applications");
    $db->exec("DELETE FROM pets");
    $db->exec("DELETE FROM users");
    seedInitialData($db);
    setFlash('success', 'Data Direset', 'Database MySQL telah dikembalikan ke data demo awal.');
    header('Location: admin-dashboard.php');
    exit;
}

// 6 Statistik Real-Time dari Database MySQL
$totalPets = (int)$db->query("SELECT COUNT(*) FROM pets")->fetchColumn();
$availablePets = (int)$db->query("SELECT COUNT(*) FROM pets WHERE status = 'Tersedia'")->fetchColumn();
$totalApps = (int)$db->query("SELECT COUNT(*) FROM applications")->fetchColumn();
$pendingApps = (int)$db->query("SELECT COUNT(*) FROM applications WHERE status = 'Menunggu'")->fetchColumn();
$approvedApps = (int)$db->query("SELECT COUNT(*) FROM applications WHERE status = 'Disetujui'")->fetchColumn();
$rejectedApps = (int)$db->query("SELECT COUNT(*) FROM applications WHERE status = 'Ditolak'")->fetchColumn();

// 5 Pengajuan Adopsi Terbaru
$stmtRecentApps = $db->query("SELECT a.*, p.name as pet_name_joined, p.image as pet_img_joined 
    FROM applications a 
    LEFT JOIN pets p ON a.pet_id = p.id 
    ORDER BY a.applied_at DESC LIMIT 5");
$recentApps = $stmtRecentApps->fetchAll();

// 5 Hewan Terbaru di Shelter
$stmtRecentPets = $db->query("SELECT * FROM pets ORDER BY created_at DESC, id DESC LIMIT 5");
$recentPets = $stmtRecentPets->fetchAll();

$pageTitle = "Admin Dashboard";
$includeAdminCss = true;
$activeAdminNav = 'dashboard';
require_once __DIR__ . '/includes/header.php';
?>

  <div class="admin-layout">
    <!-- Admin Sidebar -->
    <?php require_once __DIR__ . '/includes/admin-sidebar.php'; ?>

    <!-- Admin Main Body -->
    <main class="admin-main">
      <!-- Admin Topbar -->
      <header class="admin-topbar">
        <div class="admin-topbar-left">
          <button class="admin-menu-toggle" id="admin-sidebar-toggle">
            <i class="fa-solid fa-bars"></i>
          </button>
          <div>
            <h1 class="admin-page-title">Ringkasan Dashboard</h1>
          </div>
        </div>

        <div class="admin-topbar-right">
          <div class="admin-date-badge">
            <i class="fa-regular fa-calendar"></i>
            <span><?= date('l, d F Y') ?></span>
          </div>
          <a href="admin-animals.php?action=add" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus"></i> Tambah Hewan
          </a>
        </div>
      </header>

      <!-- Admin Content -->
      <div class="admin-content-body">
        <!-- Admin Greeting Banner -->
        <div style="background: linear-gradient(135deg, #1E2538 0%, #2B344D 100%); color:white; border-radius:var(--radius-xl); padding:2rem 2.2rem; margin-bottom:2rem; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1.25rem;">
          <div>
            <div style="display:inline-flex; align-items:center; gap:0.5rem; background:rgba(243,107,145,0.2); color:#FFA8C2; padding:0.25rem 0.75rem; border-radius:var(--radius-pill); font-size:0.8rem; font-weight:700; margin-bottom:0.75rem;">
              <i class="fa-solid fa-database"></i> Database MySQL: pawmate_db (XAMPP Aktif)
            </div>
            <h2 style="font-size:1.8rem; margin-bottom:0.4rem; color:white;">Selamat Datang, <?= htmlspecialchars($currentAdmin['name'] ?? 'Admin') ?>! 👋</h2>
            <p style="color:#94A3B8; margin:0; font-size:0.95rem;">
              Kelola adopsi hewan, pantau berkas masuk, dan perbarui data anabul di shelter Anda secara langsung melalui PHP & MySQL.
            </p>
          </div>
          <div style="display:flex; gap:0.75rem;">
            <form method="POST" action="admin-dashboard.php" onsubmit="return confirm('Kembalikan database ke data demo awal? Semua perubahan baru akan digantikan dengan seed demo.')">
              <input type="hidden" name="action" value="reset_demo">
              <button type="submit" class="btn btn-outline btn-sm" style="color:white; border-color:rgba(255,255,255,0.25);">
                <i class="fa-solid fa-rotate-left"></i> Reset Data Demo
              </button>
            </form>
          </div>
        </div>

        <!-- 6 Dynamic Stat Cards -->
        <div class="admin-stats-grid">
          <div class="admin-stat-card">
            <div class="admin-stat-icon pink"><i class="fa-solid fa-paw"></i></div>
            <div class="admin-stat-info">
              <span class="admin-stat-label">Total Hewan</span>
              <span class="admin-stat-val"><?= $totalPets ?></span>
              <span class="admin-stat-sub">Terdaftar di shelter</span>
            </div>
          </div>

          <div class="admin-stat-card">
            <div class="admin-stat-icon green"><i class="fa-solid fa-shield-cat"></i></div>
            <div class="admin-stat-info">
              <span class="admin-stat-label">Hewan Tersedia</span>
              <span class="admin-stat-val"><?= $availablePets ?></span>
              <span class="admin-stat-sub">Siap diajukan adopsi</span>
            </div>
          </div>

          <div class="admin-stat-card">
            <div class="admin-stat-icon blue"><i class="fa-solid fa-file-invoice"></i></div>
            <div class="admin-stat-info">
              <span class="admin-stat-label">Total Pengajuan</span>
              <span class="admin-stat-val"><?= $totalApps ?></span>
              <span class="admin-stat-sub">Berkas pemohon masuk</span>
            </div>
          </div>

          <div class="admin-stat-card">
            <div class="admin-stat-icon yellow"><i class="fa-solid fa-clock"></i></div>
            <div class="admin-stat-info">
              <span class="admin-stat-label">Menunggu Verifikasi</span>
              <span class="admin-stat-val"><?= $pendingApps ?></span>
              <span class="admin-stat-sub">Perlu ditinjau pengurus</span>
            </div>
          </div>

          <div class="admin-stat-card">
            <div class="admin-stat-icon teal"><i class="fa-solid fa-circle-check"></i></div>
            <div class="admin-stat-info">
              <span class="admin-stat-label">Pengajuan Disetujui</span>
              <span class="admin-stat-val"><?= $approvedApps ?></span>
              <span class="admin-stat-sub">Berhasil diadopsi</span>
            </div>
          </div>

          <div class="admin-stat-card">
            <div class="admin-stat-icon red"><i class="fa-solid fa-circle-xmark"></i></div>
            <div class="admin-stat-info">
              <span class="admin-stat-label">Pengajuan Ditolak</span>
              <span class="admin-stat-val"><?= $rejectedApps ?></span>
              <span class="admin-stat-sub">Kriteria belum sesuai</span>
            </div>
          </div>
        </div>

        <!-- 2 Side-by-side Tables: Recent Applications & Recent Animals -->
        <div style="display:grid; grid-template-columns:1.2fr 1fr; gap:1.75rem; margin-top:2rem;" class="admin-tables-split">
          
          <!-- Recent Applications Panel -->
          <div class="admin-panel">
            <div class="admin-panel-header">
              <div>
                <h3 class="admin-panel-title">Pengajuan Adopsi Terbaru</h3>
                <p class="admin-panel-sub">5 berkas permohonan terkini di database</p>
              </div>
              <a href="admin-applications.php" class="btn btn-secondary btn-sm">Lihat Semua</a>
            </div>

            <div class="admin-table-wrap">
              <table class="admin-table">
                <thead>
                  <tr>
                    <th>Pemohon</th>
                    <th>Hewan</th>
                    <th>Status</th>
                    <th>Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($recentApps)): ?>
                    <tr><td colspan="4" style="text-align:center; padding:2rem; color:var(--text-muted);">Belum ada pengajuan.</td></tr>
                  <?php else: ?>
                    <?php foreach ($recentApps as $app): 
                      $bClass = 'badge-pending';
                      if ($app['status'] === 'Dalam Proses') $bClass = 'badge-process';
                      if ($app['status'] === 'Disetujui') $bClass = 'badge-approved';
                      if ($app['status'] === 'Ditolak') $bClass = 'badge-rejected';
                    ?>
                      <tr>
                        <td>
                          <div style="font-weight:700;"><?= htmlspecialchars($app['user_name']) ?></div>
                          <small style="color:var(--text-muted);"><?= htmlspecialchars($app['applied_at']) ?></small>
                        </td>
                        <td>
                          <div style="display:flex; align-items:center; gap:0.5rem;">
                            <img src="<?= htmlspecialchars($app['pet_image']) ?>" class="admin-pet-thumb" style="width:32px; height:32px;">
                            <span><?= htmlspecialchars($app['pet_name']) ?></span>
                          </div>
                        </td>
                        <td><span class="badge <?= $bClass ?>"><?= htmlspecialchars($app['status']) ?></span></td>
                        <td>
                          <a href="admin-applications.php?review=<?= urlencode($app['id']) ?>" class="btn btn-secondary btn-sm" style="padding:0.25rem 0.6rem; font-size:0.75rem;">
                            Periksa
                          </a>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Recent Pets Panel -->
          <div class="admin-panel">
            <div class="admin-panel-header">
              <div>
                <h3 class="admin-panel-title">Hewan di Shelter Terbaru</h3>
                <p class="admin-panel-sub">Data anabul yang baru didaftarkan</p>
              </div>
              <a href="admin-animals.php" class="btn btn-secondary btn-sm">Kelola Hewan</a>
            </div>

            <div class="admin-table-wrap">
              <table class="admin-table">
                <thead>
                  <tr>
                    <th>Hewan</th>
                    <th>Jenis & Usia</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($recentPets)): ?>
                    <tr><td colspan="3" style="text-align:center; padding:2rem; color:var(--text-muted);">Belum ada hewan.</td></tr>
                  <?php else: ?>
                    <?php foreach ($recentPets as $p): 
                      $pStatusClass = 'badge-approved';
                      if ($p['status'] === 'Dalam Proses') $pStatusClass = 'badge-process';
                      if ($p['status'] === 'Sudah Diadopsi') $pStatusClass = 'badge-rejected';
                    ?>
                      <tr>
                        <td>
                          <div style="display:flex; align-items:center; gap:0.6rem;">
                            <img src="<?= htmlspecialchars($p['image']) ?>" class="admin-pet-thumb">
                            <div>
                              <div style="font-weight:700;"><?= htmlspecialchars($p['name']) ?></div>
                              <small style="color:var(--text-muted);"><?= htmlspecialchars($p['location']) ?></small>
                            </div>
                          </div>
                        </td>
                        <td>
                          <span><?= htmlspecialchars($p['species']) ?></span><br>
                          <small style="color:var(--text-muted);"><?= htmlspecialchars($p['age']) ?></small>
                        </td>
                        <td><span class="badge <?= $pStatusClass ?>"><?= htmlspecialchars($p['status']) ?></span></td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>

        </div>

      </div>
    </main>
  </div>

  <style>
    @media (max-width: 992px) {
      .admin-tables-split {
        grid-template-columns: 1fr !important;
      }
    }
  </style>

  <div id="toast-container"></div>

  <script>
    // Sidebar toggle for mobile
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
