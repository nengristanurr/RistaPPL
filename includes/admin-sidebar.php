<?php
/**
 * PawMate - Admin Sidebar Component (PHP & MySQL)
 */
require_once __DIR__ . '/../config/auth.php';
$db = getDB();

// Hitung total hewan
$totalPets = $db->query("SELECT COUNT(*) FROM pets")->fetchColumn();

// Hitung pengajuan berstatus Menunggu
$pendingApps = $db->query("SELECT COUNT(*) FROM applications WHERE status = 'Menunggu'")->fetchColumn();

$currentAdmin = getCurrentUser();
$adminName = $currentAdmin['name'] ?? 'Admin PawMate';
$adminInitial = strtoupper(substr($adminName, 0, 1));
$activeAdminNav = $activeAdminNav ?? '';
?>
<aside class="admin-sidebar">
  <div class="admin-sidebar-header">
    <a href="admin-dashboard.php" class="admin-brand">
      <div class="paw-badge"><i class="fa-solid fa-paw"></i></div>
      <span>PawMate</span>
    </a>
    <span class="admin-sidebar-badge">Admin</span>
  </div>

  <nav class="admin-nav">
    <div class="admin-nav-heading">Menu Utama</div>
    <a href="admin-dashboard.php" class="admin-nav-item <?= $activeAdminNav === 'dashboard' ? 'active' : '' ?>">
      <i class="fa-solid fa-gauge"></i>
      <span>Dashboard</span>
    </a>
    <a href="admin-animals.php" class="admin-nav-item <?= $activeAdminNav === 'animals' ? 'active' : '' ?>">
      <i class="fa-solid fa-shield-dog"></i>
      <span>Data Hewan</span>
      <span class="nav-count-badge" id="nav-count-pets"><?= $totalPets ?></span>
    </a>
    <a href="admin-applications.php" class="admin-nav-item <?= $activeAdminNav === 'applications' ? 'active' : '' ?>">
      <i class="fa-solid fa-file-signature"></i>
      <span>Pengajuan Adopsi</span>
      <?php if ($pendingApps > 0): ?>
      <span class="nav-count-badge" style="background:#FEF3C7; color:#92400E; font-weight:800;"><?= $pendingApps ?></span>
      <?php endif; ?>
    </a>
    <a href="admin-users.php" class="admin-nav-item <?= $activeAdminNav === 'users' ? 'active' : '' ?>">
      <i class="fa-solid fa-users"></i>
      <span>Data Pengguna</span>
    </a>

    <div class="admin-nav-heading" style="margin-top:1rem;">Navigasi Publik</div>
    <a href="index.php" class="admin-nav-item" target="_blank">
      <i class="fa-solid fa-arrow-up-right-from-square"></i>
      <span>Lihat Website</span>
    </a>
    <a href="animals.php" class="admin-nav-item" target="_blank">
      <i class="fa-solid fa-paw"></i>
      <span>Katalog Publik</span>
    </a>
  </nav>

  <div class="admin-sidebar-footer">
    <div class="admin-user-card">
      <div class="admin-user-avatar"><?= $adminInitial ?></div>
      <div class="admin-user-info">
        <div class="admin-user-name"><?= htmlspecialchars($adminName) ?></div>
        <div class="admin-user-role"><?= htmlspecialchars($currentAdmin['occupation'] ?? 'Shelter Manager') ?></div>
      </div>
      <a href="logout.php" onclick="return confirm('Keluar dari portal pengurus PawMate?')" class="btn-table-action delete" title="Keluar">
        <i class="fa-solid fa-arrow-right-from-bracket"></i>
      </a>
    </div>
  </div>
</aside>
