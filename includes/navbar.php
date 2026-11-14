<?php
/**
 * PawMate - Public Navbar
 */
$currentUser = getCurrentUser();
$activePage = $activePage ?? '';
?>
<header class="site-header">
  <div class="container navbar">
    <a href="index.php" class="logo-brand">
      <div class="logo-icon-wrap">
        <i class="fa-solid fa-paw"></i>
      </div>
      <span>PawMate</span>
    </a>

    <button class="nav-toggle-btn" id="mobile-menu-toggle" aria-label="Toggle navigation">
      <i class="fa-solid fa-bars"></i>
    </button>

    <ul class="nav-menu" id="nav-menu-list">
      <li><a href="index.php" class="nav-link <?= $activePage === 'home' ? 'active' : '' ?>">Beranda</a></li>
      <li><a href="animals.php" class="nav-link <?= $activePage === 'animals' ? 'active' : '' ?>">Hewan</a></li>
      <li><a href="index.php#about" class="nav-link">Tentang Kami</a></li>
      <li><a href="index.php#steps" class="nav-link">Alur Adopsi</a></li>
      <li><a href="user-dashboard.php" class="nav-link <?= $activePage === 'user-dashboard' ? 'active' : '' ?>">Dashboard Saya</a></li>
    </ul>

    <div class="nav-actions" id="navbar-auth-actions">
      <?php if ($currentUser): ?>
        <div style="display:flex; align-items:center; gap:0.75rem;">
          <a href="<?= $currentUser['role'] === 'admin' ? 'admin-dashboard.php' : 'user-dashboard.php' ?>" class="user-menu-btn">
            <div class="user-avatar-sm">
              <?= strtoupper(substr($currentUser['name'], 0, 1)) ?>
            </div>
            <span class="user-name-label"><?= htmlspecialchars(explode(' ', $currentUser['name'])[0]) ?></span>
            <span class="badge <?= $currentUser['role'] === 'admin' ? 'badge-tag' : 'badge-approved' ?>" style="font-size:0.7rem; padding:0.15rem 0.5rem;">
              <?= $currentUser['role'] === 'admin' ? 'Admin' : 'Member' ?>
            </span>
          </a>
          <a href="logout.php" onclick="return confirm('Apakah Anda yakin ingin keluar dari akun?')" class="btn btn-outline btn-sm" title="Keluar">
            <i class="fa-solid fa-arrow-right-from-bracket"></i>
          </a>
        </div>
      <?php else: ?>
        <a href="login.php" class="btn btn-outline btn-sm">Masuk</a>
        <a href="register.php" class="btn btn-primary btn-sm">Daftar</a>
      <?php endif; ?>
    </div>
  </div>
</header>
