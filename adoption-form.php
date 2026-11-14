<?php
/**
 * PawMate - Formulir Pengajuan Adopsi (PHP & MySQL)
 */
require_once __DIR__ . '/config/auth.php';

$db = getDB();
$currentUser = getCurrentUser();

$petId = $_GET['id'] ?? ($_POST['pet_id'] ?? '');

// Ambil pet yang dipilih
$pet = null;
if (!empty($petId)) {
    $stmtPet = $db->prepare("SELECT * FROM pets WHERE id = ?");
    $stmtPet->execute([$petId]);
    $pet = $stmtPet->fetch();
}

// Jika petId tidak diberikan atau tidak valid, ambil pet pertama yang Tersedia
if (!$pet) {
    $stmtFirst = $db->query("SELECT * FROM pets WHERE status = 'Tersedia' LIMIT 1");
    $pet = $stmtFirst->fetch();
    if ($pet) {
        $petId = $pet['id'];
    }
}

// Cek apakah ada pengajuan aktif yang duplikat
$hasActiveApplication = false;
if ($currentUser && $pet) {
    $stmtCheckApp = $db->prepare("SELECT id FROM applications WHERE user_id = ? AND pet_id = ? AND status IN ('Menunggu', 'Dalam Proses')");
    $stmtCheckApp->execute([$currentUser['id'], $pet['id']]);
    $hasActiveApplication = (bool)$stmtCheckApp->fetch();
}

$errorMsg = '';
$successMsg = '';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $applicantName = trim($_POST['applicant_name'] ?? '');
    $applicantEmail = trim($_POST['applicant_email'] ?? '');
    $applicantPhone = trim($_POST['applicant_phone'] ?? '');
    $applicantOccupation = trim($_POST['applicant_occupation'] ?? '');
    $applicantAddress = trim($_POST['applicant_address'] ?? '');
    $applicantHousing = trim($_POST['applicant_housing'] ?? '');
    $applicantExperience = trim($_POST['applicant_experience'] ?? '');
    $applicantReason = trim($_POST['applicant_reason'] ?? '');
    $applicantCommitment = isset($_POST['applicant_commitment']) ? 1 : 0;

    if (!$pet) {
        $errorMsg = 'Hewan yang dipilih tidak valid.';
    } elseif (empty($applicantName) || empty($applicantEmail) || empty($applicantPhone) || empty($applicantAddress) || empty($applicantHousing) || empty($applicantExperience) || empty($applicantReason)) {
        $errorMsg = 'Mohon lengkapi semua data wajib pada formulir pengajuan.';
    } elseif (!$applicantCommitment) {
        $errorMsg = 'Anda harus menyetujui komitmen pemeliharaan seumur hidup.';
    } else {
        // Tentukan User ID
        $userId = null;
        if ($currentUser) {
            $userId = $currentUser['id'];
        } else {
            // Cek apakah email sudah ada di database
            $stmtUserCheck = $db->prepare("SELECT * FROM users WHERE email = ?");
            $stmtUserCheck->execute([$applicantEmail]);
            $foundUser = $stmtUserCheck->fetch();

            if ($foundUser) {
                $userId = $foundUser['id'];
                loginUser($foundUser);
            } else {
                // Buat user otomatis untuk pemohon
                $newUserId = 'usr-' . time();
                $stmtNewUser = $db->prepare("INSERT INTO users 
                    (id, name, email, phone, password, role, address, occupation, registered_at)
                    VALUES (?, ?, ?, ?, ?, 'user', ?, ?, ?)");
                $stmtNewUser->execute([
                    $newUserId,
                    $applicantName,
                    $applicantEmail,
                    $applicantPhone,
                    password_hash('password', PASSWORD_DEFAULT),
                    $applicantAddress,
                    $applicantOccupation,
                    date('Y-m-d')
                ]);
                $userId = $newUserId;
                
                // Ambil dan buat session
                $stmtGetNew = $db->prepare("SELECT * FROM users WHERE id = ?");
                $stmtGetNew->execute([$newUserId]);
                loginUser($stmtGetNew->fetch());
            }
        }

        // Cek lagi apakah ada duplicate aktif
        $stmtDup = $db->prepare("SELECT id FROM applications WHERE user_id = ? AND pet_id = ? AND status IN ('Menunggu', 'Dalam Proses')");
        $stmtDup->execute([$userId, $pet['id']]);
        if ($stmtDup->fetch()) {
            $errorMsg = 'Anda sudah memiliki berkas pengajuan aktif untuk hewan ini.';
        } else {
            // Simpan Berkas Pengajuan ke MySQL
            $appId = 'app-' . rand(1000, 9999);
            $appliedAt = date('Y-m-d H:i:s');
            $adminNotes = 'Menunggu review awal berkas oleh tim shelter PawMate.';

            $stmtInsertApp = $db->prepare("INSERT INTO applications 
                (id, user_id, pet_id, user_name, user_email, user_phone, pet_name, pet_species, pet_image, address, occupation, housing_type, pet_experience, adoption_reason, commitment_agreed, status, admin_notes, applied_at)
                VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Menunggu', ?, ?)");
            $stmtInsertApp->execute([
                $appId,
                $userId,
                $pet['id'],
                $applicantName,
                $applicantEmail,
                $applicantPhone,
                $pet['name'],
                $pet['species'],
                $pet['image'],
                $applicantAddress,
                $applicantOccupation,
                $applicantHousing,
                $applicantExperience,
                $applicantReason,
                1,
                $adminNotes,
                $appliedAt
            ]);

            setFlash('success', 'Pengajuan Berhasil!', 'Pengajuan adopsi untuk ' . $pet['name'] . ' telah tersimpan di database MySQL XAMPP.');
            header('Location: user-dashboard.php');
            exit;
        }
    }
}

$pageTitle = "Formulir Pengajuan Adopsi";
$activePage = 'animals';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

  <main class="container" style="padding-top: 2rem; padding-bottom: 5rem;">
    <!-- Breadcrumb -->
    <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.9rem; color: var(--text-muted); margin-bottom: 1.5rem;">
      <a href="index.php" style="color: var(--text-muted);">Beranda</a>
      <i class="fa-solid fa-chevron-right" style="font-size: 0.75rem;"></i>
      <a href="animals.php" style="color: var(--text-muted);">Katalog Hewan</a>
      <i class="fa-solid fa-chevron-right" style="font-size: 0.75rem;"></i>
      <span style="color: var(--primary); font-weight: 700;">Formulir Pengajuan Adopsi</span>
    </div>

    <!-- Duplicate Warning Banner -->
    <?php if ($hasActiveApplication): ?>
    <div style="background: #FEF3C7; border: 1px solid #FCD34D; color: #92400E; padding: 1.25rem 1.5rem; border-radius: var(--radius-lg); margin-bottom: 2rem; display: flex; align-items: center; gap: 1rem;">
      <i class="fa-solid fa-triangle-exclamation" style="font-size: 1.5rem; color: #D97706;"></i>
      <div>
        <h4 style="margin-bottom: 0.25rem; font-size: 1.05rem;">Pengajuan Anda Sedang Diproses</h4>
        <p style="margin: 0; font-size: 0.9rem;">
          Anda telah memiliki berkas permohonan yang aktif untuk <strong><?= htmlspecialchars($pet['name']) ?></strong>. Anda dapat melihat statusnya di <a href="user-dashboard.php" style="color: #B45309; text-decoration: underline; font-weight: 700;">Dashboard Pengguna</a>.
        </p>
      </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($errorMsg)): ?>
    <div style="background: #FEE2E2; border: 1px solid #FECACA; color: #DC2626; padding: 1rem 1.25rem; border-radius: var(--radius-lg); margin-bottom: 1.5rem;">
      <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($errorMsg) ?>
    </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 1fr 360px; gap: 2.5rem; align-items: start;" class="form-layout-grid">
      
      <!-- Main Form -->
      <div style="background: #fff; border-radius: var(--radius-xl); padding: 2.8rem; box-shadow: var(--shadow-md); border: 1px solid var(--border-light);">
        <div style="margin-bottom: 2rem;">
          <h1 style="font-size: 2rem; margin-bottom: 0.5rem;">Formulir Pengajuan Adopsi</h1>
          <p style="color: var(--text-muted); font-size: 0.95rem;">
            Isi informasi di bawah dengan jujur untuk membantu kami memastikan anabul mendapatkan lingkungan terbaik. Data tersimpan ke database MySQL XAMPP.
          </p>
        </div>

        <form method="POST" action="adoption-form.php?id=<?= urlencode($pet['id'] ?? '') ?>">
          <input type="hidden" name="pet_id" value="<?= htmlspecialchars($pet['id'] ?? '') ?>">

          <!-- Section 1: Data Diri -->
          <div style="font-size: 1.25rem; padding-bottom: 0.6rem; margin-bottom: 1.5rem; border-bottom: 2px solid var(--primary-light); display: flex; align-items: center; gap: 0.6rem; color: var(--text-main);">
            <i class="fa-solid fa-user" style="color: var(--primary);"></i> 1. Informasi Data Diri Pemohon
          </div>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.25rem;" class="form-2-col">
            <div class="form-group">
              <label class="form-label" for="applicant-name">Nama Lengkap <span class="required">*</span></label>
              <input type="text" id="applicant-name" name="applicant_name" class="form-control" placeholder="Nama sesuai KTP" value="<?= htmlspecialchars($_POST['applicant_name'] ?? ($currentUser['name'] ?? '')) ?>" required>
            </div>

            <div class="form-group">
              <label class="form-label" for="applicant-email">Alamat Email <span class="required">*</span></label>
              <input type="email" id="applicant-email" name="applicant_email" class="form-control" placeholder="nama@email.com" value="<?= htmlspecialchars($_POST['applicant_email'] ?? ($currentUser['email'] ?? '')) ?>" required>
            </div>
          </div>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.25rem;" class="form-2-col">
            <div class="form-group">
              <label class="form-label" for="applicant-phone">Nomor WhatsApp Aktif <span class="required">*</span></label>
              <input type="tel" id="applicant-phone" name="applicant_phone" class="form-control" placeholder="081234567890" value="<?= htmlspecialchars($_POST['applicant_phone'] ?? ($currentUser['phone'] ?? '')) ?>" required>
            </div>

            <div class="form-group">
              <label class="form-label" for="applicant-occupation">Pekerjaan / Profesi <span class="required">*</span></label>
              <input type="text" id="applicant-occupation" name="applicant_occupation" class="form-control" placeholder="cth. Desainer Grafis, Karyawan Swasta" value="<?= htmlspecialchars($_POST['applicant_occupation'] ?? ($currentUser['occupation'] ?? '')) ?>" required>
            </div>
          </div>

          <div class="form-group" style="margin-bottom: 2rem;">
            <label class="form-label" for="applicant-address">Alamat Domisili Lengkap <span class="required">*</span></label>
            <textarea id="applicant-address" name="applicant_address" class="form-control" rows="2" placeholder="Nama jalan, nomor rumah, RT/RW, kecamatan, kota..." required><?= htmlspecialchars($_POST['applicant_address'] ?? ($currentUser['address'] ?? '')) ?></textarea>
          </div>

          <!-- Section 2: Lingkungan & Pengalaman -->
          <div style="font-size: 1.25rem; padding-bottom: 0.6rem; margin-bottom: 1.5rem; border-bottom: 2px solid var(--primary-light); display: flex; align-items: center; gap: 0.6rem; color: var(--text-main);">
            <i class="fa-solid fa-house-chimney-window" style="color: var(--primary);"></i> 2. Lingkungan Tempat Tinggal & Pengalaman
          </div>

          <div class="form-group" style="margin-bottom: 1.25rem;">
            <label class="form-label" for="applicant-housing">Tipe Tempat Tinggal <span class="required">*</span></label>
            <select id="applicant-housing" name="applicant_housing" class="form-control" required>
              <option value="">-- Pilih Tipe Tempat Tinggal --</option>
              <option value="Rumah dengan Halaman Berpagar" <?= (($_POST['applicant_housing'] ?? '') === 'Rumah dengan Halaman Berpagar') ? 'selected' : '' ?>>Rumah dengan Halaman Berpagar</option>
              <option value="Rumah Tanpa Halaman / Halaman Terbuka" <?= (($_POST['applicant_housing'] ?? '') === 'Rumah Tanpa Halaman / Halaman Terbuka') ? 'selected' : '' ?>>Rumah Tanpa Halaman / Halaman Terbuka</option>
              <option value="Apartemen / Kondominium (Pet Friendly)" <?= (($_POST['applicant_housing'] ?? '') === 'Apartemen / Kondominium (Pet Friendly)') ? 'selected' : '' ?>>Apartemen / Kondominium (Pet Friendly)</option>
              <option value="Kamar Kost / Kontrakan" <?= (($_POST['applicant_housing'] ?? '') === 'Kamar Kost / Kontrakan') ? 'selected' : '' ?>>Kamar Kost / Kontrakan</option>
              <option value="Lainnya" <?= (($_POST['applicant_housing'] ?? '') === 'Lainnya') ? 'selected' : '' ?>>Lainnya</option>
            </select>
          </div>

          <div class="form-group" style="margin-bottom: 1.25rem;">
            <label class="form-label" for="applicant-experience">Pengalaman Memelihara Hewan <span class="required">*</span></label>
            <textarea id="applicant-experience" name="applicant_experience" class="form-control" rows="2" placeholder="Ceritakan riwayat hewan peliharaan Anda sebelumnya atau saat ini..." required><?= htmlspecialchars($_POST['applicant_experience'] ?? 'Pernah memelihara hewan peliharaan sebelumnya dengan penuh tanggung jawab.') ?></textarea>
          </div>

          <div class="form-group" style="margin-bottom: 2rem;">
            <label class="form-label" for="applicant-reason">Alasan Ingin Mengadopsi <?= $pet ? htmlspecialchars($pet['name']) : 'Hewan Ini' ?> <span class="required">*</span></label>
            <textarea id="applicant-reason" name="applicant_reason" class="form-control" rows="3" placeholder="Mengapa Anda tertarik dengan anabul ini? Bagaimana rencana perawatan Anda?" required><?= htmlspecialchars($_POST['applicant_reason'] ?? 'Ingin memberikan rumah yang hangat, penuh kasih sayang, dan merawatnya sebagai bagian dari keluarga.') ?></textarea>
          </div>

          <!-- Section 3: Pernyataan Komitmen -->
          <div style="background: #FFF9FB; border-radius: var(--radius-lg); padding: 1.5rem; border: 1px solid #FFE7EF; margin-bottom: 2rem;">
            <label class="checkbox-label" style="align-items: flex-start; cursor: pointer;">
              <input type="checkbox" name="applicant_commitment" value="1" id="applicant-commitment" checked required style="margin-top: 0.25rem;">
              <span style="font-size: 0.9rem; line-height: 1.6; color: var(--text-main);">
                <strong>Pernyataan Komitmen:</strong> Saya menyatakan bahwa semua informasi yang saya berikan adalah benar. Saya berkomitmen untuk tidak memperjualbelikan, menelantarkan, dan siap memberikan perawatan medis, pakan bergizi, serta kasih sayang seumur hidup bagi anabul ini.
              </span>
            </label>
          </div>

          <div style="display: flex; gap: 1rem; align-items: center;">
            <button type="submit" class="btn btn-primary btn-lg" style="flex: 1;" <?= $hasActiveApplication ? 'disabled' : '' ?>>
              <i class="fa-solid fa-paper-plane"></i> Kirim Formulir Pengajuan
            </button>
            <a href="animals.php" class="btn btn-secondary btn-lg">Batal</a>
          </div>
        </form>
      </div>

      <!-- Sticky Sidebar: Selected Pet Summary -->
      <?php if ($pet): ?>
      <aside style="position: sticky; top: 100px; background: #fff; border-radius: var(--radius-xl); padding: 1.75rem; box-shadow: var(--shadow-md); border: 1px solid var(--border-light);">
        <div style="font-size: 0.85rem; text-transform: uppercase; font-weight: 700; color: var(--text-muted); margin-bottom: 0.75rem;">
          <i class="fa-solid fa-heart" style="color: var(--primary);"></i> Hewan yang Dipilih
        </div>

        <img src="<?= htmlspecialchars($pet['image']) ?>" alt="<?= htmlspecialchars($pet['name']) ?>" style="width: 100%; height: 220px; border-radius: var(--radius-lg); object-fit: cover; margin-bottom: 1.25rem;">

        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
          <h3 style="font-size: 1.5rem; margin: 0;"><?= htmlspecialchars($pet['name']) ?></h3>
          <span class="badge badge-approved" style="font-size: 0.8rem;"><?= htmlspecialchars($pet['status']) ?></span>
        </div>

        <div style="color: var(--text-muted); font-size: 0.95rem; margin-bottom: 1.25rem;">
          <?= htmlspecialchars($pet['breed'] ?: $pet['species']) ?> • <?= htmlspecialchars($pet['age']) ?>
        </div>

        <div style="border-top: 1px solid var(--border-light); padding-top: 1rem; font-size: 0.9rem; display: flex; flex-direction: column; gap: 0.6rem;">
          <div><i class="fa-solid fa-venus-mars" style="color: var(--primary); width: 20px;"></i> Jenis Kelamin: <strong><?= htmlspecialchars($pet['gender']) ?></strong></div>
          <div><i class="fa-solid fa-location-dot" style="color: var(--primary); width: 20px;"></i> Lokasi: <strong><?= htmlspecialchars($pet['location']) ?></strong></div>
          <div><i class="fa-solid fa-shield-cat" style="color: var(--primary); width: 20px;"></i> Vaksinasi: <strong><?= htmlspecialchars($pet['vaccinated'] ?: 'Lengkap') ?></strong></div>
        </div>

        <div style="margin-top: 1.5rem; padding: 0.85rem; background: #FFF9FB; border-radius: var(--radius-md); font-size: 0.82rem; color: var(--text-muted); line-height: 1.5;">
          <i class="fa-solid fa-circle-info" style="color: var(--primary);"></i> Berkas Anda akan diperiksa oleh pengurus shelter dalam waktu 1x24 jam.
        </div>
      </aside>
      <?php endif; ?>

    </div>
  </main>

  <style>
    @media (max-width: 992px) {
      .form-layout-grid {
        grid-template-columns: 1fr !important;
      }
      .form-2-col {
        grid-template-columns: 1fr !important;
      }
    }
  </style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
