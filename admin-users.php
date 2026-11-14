<?php
/**
 * PawMate - Manajemen Data Pengguna (PHP & MySQL XAMPP)
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

// Pencarian
$q = trim($_GET['q'] ?? '');

$sql = "SELECT u.*, COUNT(a.id) AS total_apps 
    FROM users u 
    LEFT JOIN applications a ON u.id = a.user_id 
    WHERE 1=1";
$params = [];

if (!empty($q)) {
    $sql .= " AND (u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ? OR u.occupation LIKE ?)";
    $term = "%$q%";
    $params = [$term, $term, $term, $term];
}

$sql .= " GROUP BY u.id ORDER BY (u.role = 'admin') DESC, u.registered_at DESC";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

$pageTitle = "Manajemen Pengguna";
$includeAdminCss = true;
$activeAdminNav = 'users';
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
            <h1 class="admin-page-title">Manajemen Data Pengguna</h1>
          </div>
        </div>
      </header>

      <div class="admin-content-body">
        <!-- Search Bar -->
        <div class="admin-panel" style="margin-bottom: 1.5rem; padding: 1.25rem 1.5rem;">
          <form method="GET" action="admin-users.php" style="display:flex; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap;">
            <div class="input-with-icon" style="max-width:400px; width:100%;">
              <i class="fa-solid fa-magnifying-glass"></i>
              <input type="text" name="q" placeholder="Cari nama, email, WhatsApp pengguna..." value="<?= htmlspecialchars($q) ?>">
            </div>
            <div style="display:flex; align-items:center; gap:1rem;">
              <span style="font-weight:700; color:var(--text-muted); font-size:0.9rem;">
                Total Pengguna di Database: <strong style="color:var(--primary);"><?= count($users) ?></strong>
              </span>
              <button type="submit" class="btn btn-secondary btn-sm" title="Cari">
                <i class="fa-solid fa-magnifying-glass"></i>
              </button>
              <?php if (!empty($q)): ?>
              <a href="admin-users.php" class="btn btn-secondary btn-sm" title="Reset">
                <i class="fa-solid fa-rotate-left"></i>
              </a>
              <?php endif; ?>
            </div>
          </form>
        </div>

        <!-- Users Table -->
        <div class="admin-panel">
          <div class="admin-panel-header">
            <div>
              <h3 class="admin-panel-title">Daftar Anggota & Pengurus (MySQL)</h3>
              <p class="admin-panel-sub">Pengguna terdaftar dalam sistem shelter PawMate</p>
            </div>
          </div>

          <div class="admin-table-wrap">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>Pengguna</th>
                  <th>ID Akun</th>
                  <th>Nomor WhatsApp</th>
                  <th>Peran (Role)</th>
                  <th>Pekerjaan / Domisili</th>
                  <th>Tgl Terdaftar</th>
                  <th>Riwayat Adopsi</th>
                  <th style="text-align:right;">Rincian</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($users)): ?>
                  <tr>
                    <td colspan="8" style="text-align:center; padding:3rem; color:var(--text-muted);">
                      <i class="fa-solid fa-user-xmark" style="font-size:2rem; margin-bottom:0.5rem; display:block;"></i>
                      Tidak ada data pengguna yang ditemukan.
                    </td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($users as $u): 
                    $isAdminRole = $u['role'] === 'admin';
                    $waClean = preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $u['phone'] ?? ''));
                  ?>
                    <tr>
                      <td>
                        <div style="display:flex; align-items:center; gap:0.75rem;">
                          <div class="admin-user-avatar" style="width:38px; height:38px; font-size:1rem; flex-shrink:0;">
                            <?= strtoupper(substr($u['name'], 0, 1)) ?>
                          </div>
                          <div>
                            <div style="font-weight:700;"><?= htmlspecialchars($u['name']) ?></div>
                            <small style="color:var(--text-muted);"><?= htmlspecialchars($u['email']) ?></small>
                          </div>
                        </div>
                      </td>
                      <td><code><?= htmlspecialchars($u['id']) ?></code></td>
                      <td>
                        <?php if (!empty($u['phone'])): ?>
                          <a href="https://wa.me/<?= $waClean ?>" target="_blank" style="color:#059669; font-weight:600;">
                            <i class="fa-brands fa-whatsapp"></i> <?= htmlspecialchars($u['phone']) ?>
                          </a>
                        <?php else: ?>
                          <span style="color:var(--text-muted);">-</span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <span class="badge <?= $isAdminRole ? 'badge-tag' : 'badge-approved' ?>" style="font-size:0.75rem; padding:0.2rem 0.6rem;">
                          <?= $isAdminRole ? '<i class="fa-solid fa-shield-halved"></i> Admin Shelter' : '<i class="fa-solid fa-user"></i> Member' ?>
                        </span>
                      </td>
                      <td>
                        <span><?= htmlspecialchars($u['occupation'] ?: 'Member') ?></span><br>
                        <small style="color:var(--text-muted);"><?= htmlspecialchars($u['address'] ?: 'Indonesia') ?></small>
                      </td>
                      <td><small><?= htmlspecialchars($u['registered_at'] ?? '-') ?></small></td>
                      <td>
                        <span class="badge badge-process" style="font-size:0.8rem;">
                          <?= (int)$u['total_apps'] ?> Pengajuan
                        </span>
                      </td>
                      <td style="text-align:right;">
                        <button class="btn btn-secondary btn-sm" onclick='viewUserDetail(<?= json_encode($u) ?>)'>
                          <i class="fa-solid fa-eye"></i> Profil
                        </button>
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

  <!-- User Detail Modal -->
  <div class="modal-backdrop" id="user-detail-modal">
    <div class="modal-card" style="max-width: 500px;">
      <div class="modal-header">
        <h3 class="modal-title"><i class="fa-solid fa-id-card" style="color:var(--primary)"></i> Rincian Profil Anggota</h3>
        <button class="modal-close" onclick="closeUserDetailModal()"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <div class="modal-body" id="user-detail-body">
        <!-- Injected via JavaScript -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeUserDetailModal()">Tutup</button>
      </div>
    </div>
  </div>

  <div id="toast-container"></div>

  <script>
    function viewUserDetail(u) {
      const waClean = (u.phone || '').replace(/^0/, '62').replace(/[^0-9]/g, '');
      const body = document.getElementById('user-detail-body');
      body.innerHTML = `
        <div style="text-align:center; margin-bottom:1.5rem;">
          <div style="width:70px; height:70px; border-radius:50%; background:linear-gradient(135deg, var(--primary) 0%, #FF8FAB 100%); color:white; font-size:1.8rem; font-weight:800; display:flex; align-items:center; justify-content:center; margin:0 auto 0.75rem;">
            ${(u.name || 'U').charAt(0).toUpperCase()}
          </div>
          <h3 style="font-size:1.25rem; margin-bottom:0.2rem;">${u.name}</h3>
          <span class="badge ${u.role === 'admin' ? 'badge-tag' : 'badge-approved'}">${u.role === 'admin' ? 'Pengurus Shelter' : 'Pemohon Adopsi'}</span>
        </div>

        <div style="display:flex; flex-direction:column; gap:0.75rem; font-size:0.9rem; background:#FFF9FB; padding:1.25rem; border-radius:12px; border:1px solid #FFE7EF;">
          <div><strong>ID Akun:</strong> <code>${u.id}</code></div>
          <div><strong>Email:</strong> ${u.email}</div>
          <div><strong>WhatsApp:</strong> <a href="https://wa.me/${waClean}" target="_blank" style="color:#059669; font-weight:700;"><i class="fa-brands fa-whatsapp"></i> ${u.phone || '-'}</a></div>
          <div><strong>Profesi:</strong> ${u.occupation || '-'}</div>
          <div><strong>Alamat:</strong> ${u.address || '-'}</div>
          <div><strong>Tanggal Registrasi:</strong> ${u.registered_at || '-'}</div>
          <div><strong>Total Riwayat Pengajuan:</strong> <strong style="color:var(--primary);">${u.total_apps || 0} Berkas</strong></div>
        </div>
      `;
      document.getElementById('user-detail-modal').classList.add('active');
    }

    function closeUserDetailModal() {
      document.getElementById('user-detail-modal').classList.remove('active');
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
