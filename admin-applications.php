<?php
/**
 * PawMate - Manajemen Pengajuan Adopsi (PHP & MySQL XAMPP)
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

// Handle Status Update POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $appId = trim($_POST['app_id'] ?? '');
    $newStatus = trim($_POST['status'] ?? '');
    $adminNotes = trim($_POST['admin_notes'] ?? '');

    if (!empty($appId) && in_array($newStatus, ['Menunggu', 'Dalam Proses', 'Disetujui', 'Ditolak'])) {
        // Ambil data pengajuan sebelumnya
        $stmtApp = $db->prepare("SELECT * FROM applications WHERE id = ?");
        $stmtApp->execute([$appId]);
        $app = $stmtApp->fetch();

        if ($app) {
            // Update status pengajuan
            $stmtUpdate = $db->prepare("UPDATE applications SET status = ?, admin_notes = ? WHERE id = ?");
            $stmtUpdate->execute([$newStatus, $adminNotes, $appId]);

            // Sinkronisasi status hewan ke tabel pets:
            // Jika Disetujui -> hewan otomatis menjadi "Sudah Diadopsi"
            if ($newStatus === 'Disetujui') {
                $stmtPet = $db->prepare("UPDATE pets SET status = 'Sudah Diadopsi' WHERE id = ?");
                $stmtPet->execute([$app['pet_id']]);
            } elseif ($newStatus === 'Dalam Proses') {
                $stmtPet = $db->prepare("UPDATE pets SET status = 'Dalam Proses' WHERE id = ? AND status != 'Sudah Diadopsi'");
                $stmtPet->execute([$app['pet_id']]);
            } elseif ($newStatus === 'Ditolak') {
                // Jika sebelumnya sudah diadopsi oleh pengajuan ini atau dalam proses, kembalikan ke Tersedia jika tidak ada pengajuan lain yang disetujui
                $stmtCheckOther = $db->prepare("SELECT COUNT(*) FROM applications WHERE pet_id = ? AND status = 'Disetujui' AND id != ?");
                $stmtCheckOther->execute([$app['pet_id'], $appId]);
                $otherApproved = $stmtCheckOther->fetchColumn();

                if ($otherApproved == 0) {
                    $stmtPet = $db->prepare("UPDATE pets SET status = 'Tersedia' WHERE id = ?");
                    $stmtPet->execute([$app['pet_id']]);
                }
            }

            setFlash('success', 'Status Diperbarui', "Pengajuan $appId telah diubah menjadi '$newStatus'.");
        }
    }
    header('Location: admin-applications.php');
    exit;
}

// Filter & Pencarian
$q = trim($_GET['q'] ?? '');
$filterStatus = trim($_GET['status'] ?? 'all');

$sql = "SELECT a.*, p.name as pet_name_latest, p.species as pet_species_latest, p.image as pet_img_latest, p.status as pet_status_latest 
    FROM applications a 
    LEFT JOIN pets p ON a.pet_id = p.id 
    WHERE 1=1";
$params = [];

if (!empty($q)) {
    $sql .= " AND (a.id LIKE ? OR a.user_name LIKE ? OR a.user_email LIKE ? OR a.user_phone LIKE ? OR a.pet_name LIKE ?)";
    $term = "%$q%";
    $params = [$term, $term, $term, $term, $term];
}

if ($filterStatus !== 'all' && !empty($filterStatus)) {
    $sql .= " AND a.status = ?";
    $params[] = $filterStatus;
}

$sql .= " ORDER BY (a.status = 'Menunggu') DESC, a.applied_at DESC";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$applications = $stmt->fetchAll();

$pageTitle = "Manajemen Pengajuan Adopsi";
$includeAdminCss = true;
$activeAdminNav = 'applications';
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
            <h1 class="admin-page-title">Manajemen Pengajuan Adopsi</h1>
          </div>
        </div>
      </header>

      <div class="admin-content-body">
        <!-- Filter Controls -->
        <div class="admin-panel" style="margin-bottom: 1.5rem; padding: 1.25rem 1.5rem;">
          <form method="GET" action="admin-applications.php" style="display:flex; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap;">
            <div style="display:flex; align-items:center; gap:0.75rem; flex-grow:1; max-width:400px;">
              <div class="input-with-icon" style="width:100%;">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" name="q" placeholder="Cari nama pemohon, email, hewan..." value="<?= htmlspecialchars($q) ?>">
              </div>
            </div>

            <div style="display:flex; align-items:center; gap:0.75rem; flex-wrap:wrap;">
              <select name="status" class="form-control" style="width:auto; padding:0.6rem 2rem 0.6rem 1rem;" onchange="this.form.submit()">
                <option value="all" <?= $filterStatus === 'all' ? 'selected' : '' ?>>Semua Status</option>
                <option value="Menunggu" <?= $filterStatus === 'Menunggu' ? 'selected' : '' ?>>Menunggu</option>
                <option value="Dalam Proses" <?= $filterStatus === 'Dalam Proses' ? 'selected' : '' ?>>Dalam Proses</option>
                <option value="Disetujui" <?= $filterStatus === 'Disetujui' ? 'selected' : '' ?>>Disetujui</option>
                <option value="Ditolak" <?= $filterStatus === 'Ditolak' ? 'selected' : '' ?>>Ditolak</option>
              </select>

              <button type="submit" class="btn btn-secondary btn-sm" title="Terapkan">
                <i class="fa-solid fa-filter"></i>
              </button>
              <a href="admin-applications.php" class="btn btn-secondary btn-sm" title="Reset">
                <i class="fa-solid fa-rotate-left"></i>
              </a>
            </div>
          </form>
        </div>

        <!-- Table Panel -->
        <div class="admin-panel">
          <div class="admin-panel-header">
            <div>
              <h3 class="admin-panel-title">Berkas Permohonan Masuk (MySQL)</h3>
              <p class="admin-panel-sub">Total <?= count($applications) ?> berkas pengajuan di database shelter</p>
            </div>
          </div>

          <div class="admin-table-wrap">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>No. Berkas</th>
                  <th>Nama Pemohon</th>
                  <th>Kontak WhatsApp</th>
                  <th>Hewan Target</th>
                  <th>Tipe Hunian</th>
                  <th>Tanggal Masuk</th>
                  <th>Status</th>
                  <th style="text-align:right;">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($applications)): ?>
                  <tr>
                    <td colspan="8" style="text-align:center; padding:3rem; color:var(--text-muted);">
                      <i class="fa-regular fa-folder-open" style="font-size:2rem; margin-bottom:0.5rem; display:block;"></i>
                      Tidak ada permohonan adopsi yang sesuai kriteria.
                    </td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($applications as $app): 
                    $badgeClass = 'badge-pending';
                    if ($app['status'] === 'Dalam Proses') $badgeClass = 'badge-process';
                    if ($app['status'] === 'Disetujui') $badgeClass = 'badge-approved';
                    if ($app['status'] === 'Ditolak') $badgeClass = 'badge-rejected';

                    $waPhone = preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $app['user_phone']));
                  ?>
                    <tr>
                      <td><strong><code><?= htmlspecialchars($app['id']) ?></code></strong></td>
                      <td>
                        <div style="font-weight:700;"><?= htmlspecialchars($app['user_name']) ?></div>
                        <small style="color:var(--text-muted);"><?= htmlspecialchars($app['user_email']) ?></small>
                      </td>
                      <td>
                        <a href="https://wa.me/<?= $waPhone ?>" target="_blank" style="color:#059669; font-weight:600;">
                          <i class="fa-brands fa-whatsapp"></i> <?= htmlspecialchars($app['user_phone']) ?>
                        </a>
                      </td>
                      <td>
                        <div style="display:flex; align-items:center; gap:0.6rem;">
                          <img src="<?= htmlspecialchars($app['pet_image']) ?>" alt="<?= htmlspecialchars($app['pet_name']) ?>" class="admin-pet-thumb" style="width:34px; height:34px;">
                          <span><?= htmlspecialchars($app['pet_name']) ?></span>
                        </div>
                      </td>
                      <td><span style="font-size:0.85rem; color:var(--text-muted);"><?= htmlspecialchars($app['housing_type']) ?></span></td>
                      <td><small><?= htmlspecialchars($app['applied_at']) ?></small></td>
                      <td><span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($app['status']) ?></span></td>
                      <td style="text-align:right;">
                        <button class="btn btn-secondary btn-sm" onclick='openReviewModal(<?= json_encode($app) ?>)'>
                          <i class="fa-solid fa-magnifying-glass"></i> Periksa
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

  <!-- Review Application Modal -->
  <div class="modal-backdrop" id="review-app-modal">
    <div class="modal-card" style="max-width: 680px;">
      <div class="modal-header">
        <h3 class="modal-title">
          <i class="fa-solid fa-clipboard-check" style="color:var(--primary)"></i> Evaluasi Berkas Permohonan
        </h3>
        <button class="modal-close" onclick="closeReviewModal()"><i class="fa-solid fa-xmark"></i></button>
      </div>

      <form method="POST" action="admin-applications.php" id="review-form">
        <input type="hidden" name="action" value="update_status">
        <input type="hidden" name="app_id" id="review-app-id" value="">
        <input type="hidden" name="status" id="review-target-status" value="">

        <div class="modal-body" id="review-modal-body">
          <!-- Injected via JavaScript -->
        </div>

        <div class="modal-footer" style="justify-content:space-between; flex-wrap:wrap; gap:0.75rem;">
          <div>
            <button type="button" class="btn btn-secondary" onclick="closeReviewModal()">Tutup</button>
          </div>
          <div style="display:flex; gap:0.5rem; flex-wrap:wrap;">
            <button type="button" class="btn btn-sm" style="background:#DBEAFE; color:#1E40AF; font-weight:700;" onclick="submitStatus('Dalam Proses')">
              <i class="fa-solid fa-spinner"></i> Dalam Proses
            </button>
            <button type="button" class="btn btn-sm" style="background:#FEE2E2; color:#991B1B; font-weight:700;" onclick="submitStatus('Ditolak')">
              <i class="fa-solid fa-xmark"></i> Tolak Berkas
            </button>
            <button type="button" class="btn btn-sm btn-primary" onclick="submitStatus('Disetujui')">
              <i class="fa-solid fa-check"></i> Setujui Adopsi
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <div id="toast-container"></div>

  <script>
    function openReviewModal(app) {
      document.getElementById('review-app-id').value = app.id;
      const body = document.getElementById('review-modal-body');

      let badgeClass = 'badge-pending';
      if (app.status === 'Dalam Proses') badgeClass = 'badge-process';
      if (app.status === 'Disetujui') badgeClass = 'badge-approved';
      if (app.status === 'Ditolak') badgeClass = 'badge-rejected';

      const waPhone = (app.user_phone || '').replace(/^0/, '62').replace(/[^0-9]/g, '');

      body.innerHTML = `
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.25rem; background:#FFF9FB; padding:1.2rem; border-radius:var(--radius-md); border:1px solid #F5DDE5;">
          <div style="display:flex; align-items:center; gap:1rem;">
            <img src="${app.pet_image}" alt="${app.pet_name}" style="width:60px; height:60px; border-radius:12px; object-fit:cover;">
            <div>
              <h4 style="font-size:1.15rem; margin-bottom:0.15rem;">Hewan Target: ${app.pet_name} (${app.pet_species || 'Hewan'})</h4>
              <small style="color:var(--text-muted);">ID Berkas: <code>${app.id}</code> • Diajukan: ${app.applied_at}</small>
            </div>
          </div>
          <div>
            <span class="badge ${badgeClass}" style="font-size:0.9rem;">Status: ${app.status}</span>
          </div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; font-size:0.9rem; margin-bottom:1.2rem;">
          <div><strong>Nama Pemohon:</strong> ${app.user_name}</div>
          <div><strong>Email:</strong> ${app.user_email}</div>
          <div><strong>Nomor WhatsApp:</strong> <a href="https://wa.me/${waPhone}" target="_blank" style="color:#059669; font-weight:700;"><i class="fa-brands fa-whatsapp"></i> ${app.user_phone}</a></div>
          <div><strong>Pekerjaan:</strong> ${app.occupation || '-'}</div>
        </div>

        <div style="font-size:0.9rem; margin-bottom:0.8rem;">
          <strong>Alamat Domisili:</strong>
          <p style="color:var(--text-muted); margin-top:0.2rem;">${app.address}</p>
        </div>

        <div style="font-size:0.9rem; margin-bottom:0.8rem;">
          <strong>Jenis Tempat Tinggal:</strong>
          <p style="color:var(--text-muted); margin-top:0.2rem;">${app.housing_type}</p>
        </div>

        <div style="font-size:0.9rem; margin-bottom:0.8rem;">
          <strong>Pengalaman Memelihara Hewan:</strong>
          <p style="color:var(--text-muted); margin-top:0.2rem;">${app.pet_experience || '-'}</p>
        </div>

        <div style="font-size:0.9rem; margin-bottom:1.2rem;">
          <strong>Alasan Mengadopsi:</strong>
          <p style="color:var(--text-muted); margin-top:0.2rem; line-height:1.6; background:#F8FAFC; padding:0.75rem; border-radius:8px;">"${app.adoption_reason}"</p>
        </div>

        <div class="form-group" style="margin-top:1.25rem;">
          <label class="form-label" for="modal-admin-notes">
            <i class="fa-solid fa-pen-nib" style="color:var(--primary)"></i> Catatan Keputusan / Alasan Shelter (Tersimpan ke Database)
          </label>
          <textarea id="modal-admin-notes" name="admin_notes" class="form-control" rows="2" placeholder="Tuliskan catatan wawancara atau alasan persetujuan/penolakan...">${app.admin_notes || ''}</textarea>
        </div>
      `;

      document.getElementById('review-app-modal').classList.add('active');
    }

    function closeReviewModal() {
      document.getElementById('review-app-modal').classList.remove('active');
    }

    function submitStatus(status) {
      if (confirm(`Apakah Anda yakin ingin mengubah status pengajuan ini menjadi "${status}"?`)) {
        document.getElementById('review-target-status').value = status;
        document.getElementById('review-form').submit();
      }
    }

    // Auto open modal if review=app-xxx in url
    const urlParams = new URLSearchParams(window.location.search);
    const reviewId = urlParams.get('review');
    if (reviewId) {
      const apps = <?= json_encode($applications) ?>;
      const target = apps.find(a => a.id === reviewId);
      if (target) openReviewModal(target);
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
