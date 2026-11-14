<?php
/**
 * PawMate - Detail Hewan Adopsi (PHP & MySQL)
 */
require_once __DIR__ . '/config/auth.php';

$db = getDB();
$currentUser = getCurrentUser();

$petId = $_GET['id'] ?? '';
$stmt = $db->prepare("SELECT * FROM pets WHERE id = ?");
$stmt->execute([$petId]);
$pet = $stmt->fetch();

$isFav = false;
if ($currentUser && $pet) {
    $stmtFav = $db->prepare("SELECT id FROM favorites WHERE user_id = ? AND pet_id = ?");
    $stmtFav->execute([$currentUser['id'], $pet['id']]);
    $isFav = (bool)$stmtFav->fetch();
}

$pageTitle = $pet ? "Detail " . $pet['name'] : "Hewan Tidak Ditemukan";
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
      <span style="color: var(--primary); font-weight: 700;"><?= $pet ? htmlspecialchars($pet['name']) : 'Tidak Ditemukan' ?></span>
    </div>

    <?php if (!$pet): ?>
      <div style="text-align: center; padding: 5rem 1.5rem; background: #fff; border-radius: var(--radius-xl); border: 1px solid var(--border-light); max-width: 600px; margin: 0 auto;">
        <div style="font-size: 3rem; color: var(--primary); margin-bottom: 1rem;">
          <i class="fa-solid fa-face-frown"></i>
        </div>
        <h2 style="margin-bottom: 0.5rem;">Hewan Tidak Ditemukan</h2>
        <p style="color: var(--text-muted); margin-bottom: 2rem;">
          Data hewan yang Anda cari mungkin sudah tidak tersedia atau ID tidak valid.
        </p>
        <a href="animals.php" class="btn btn-primary">
          <i class="fa-solid fa-arrow-left"></i> Kembali ke Katalog Hewan
        </a>
      </div>
    <?php else: 
      $gallery = json_decode($pet['gallery'] ?? '[]', true);
      if (empty($gallery)) {
        $gallery = [$pet['image']];
      }
      $temperaments = json_decode($pet['temperament'] ?? '[]', true);
      if (empty($temperaments)) {
        $temperaments = ['Ramah', 'Penyayang', 'Aktif'];
      }
      $isMale = $pet['gender'] === 'Jantan';
      $isAvailable = $pet['status'] === 'Tersedia';
      
      $statusClass = 'badge-approved';
      if ($pet['status'] === 'Dalam Proses') $statusClass = 'badge-process';
      if ($pet['status'] === 'Sudah Diadopsi') $statusClass = 'badge-rejected';
    ?>
      <div style="display: grid; grid-template-columns: 1.1fr 1fr; gap: 3.5rem; align-items: start; margin-top: 1.5rem;" class="detail-layout-grid">
        
        <!-- Left: Image Gallery -->
        <div style="position: sticky; top: 100px;">
          <div style="width: 100%; height: 480px; border-radius: var(--radius-xl); overflow: hidden; box-shadow: var(--shadow-lg); border: 6px solid #fff; background: #FFF1F5; position: relative;">
            <img id="detail-active-img" src="<?= htmlspecialchars($gallery[0]) ?>" alt="<?= htmlspecialchars($pet['name']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
            
            <button class="pet-favorite-btn <?= $isFav ? 'active' : '' ?>" data-favorite-pet="<?= htmlspecialchars($pet['id']) ?>" onclick="handleToggleFavorite('<?= htmlspecialchars($pet['id']) ?>', event)" style="position: absolute; top: 18px; right: 18px; z-index: 10;" title="Simpan ke favorit">
              <i class="<?= $isFav ? 'fa-solid' : 'fa-regular' ?> fa-heart"></i>
            </button>
            
            <span class="badge <?= $statusClass ?>" style="position: absolute; bottom: 18px; left: 18px; font-size: 0.88rem; padding: 0.35rem 0.9rem; box-shadow: var(--shadow-sm);">
              <i class="fa-solid fa-circle" style="font-size: 0.5rem;"></i> Status: <?= htmlspecialchars($pet['status']) ?>
            </span>
          </div>

          <?php if (count($gallery) > 1): ?>
          <div style="display: flex; gap: 0.85rem; margin-top: 1.25rem; overflow-x: auto; padding-bottom: 0.5rem;">
            <?php foreach ($gallery as $idx => $imgUrl): ?>
              <div class="thumb-item <?= $idx === 0 ? 'active' : '' ?>" onclick="switchDetailImage('<?= htmlspecialchars($imgUrl) ?>', this)" style="width: 90px; height: 80px; border-radius: var(--radius-md); overflow: hidden; cursor: pointer; border: 3px solid <?= $idx === 0 ? 'var(--primary)' : 'transparent' ?>; background: #fff; flex-shrink: 0;">
                <img src="<?= htmlspecialchars($imgUrl) ?>" alt="Thumbnail <?= $idx+1 ?>" style="width: 100%; height: 100%; object-fit: cover;">
              </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>

        <!-- Right: Detail Information -->
        <div style="background: #fff; border-radius: var(--radius-xl); padding: 2.5rem; box-shadow: var(--shadow-md); border: 1px solid var(--border-light);">
          
          <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.5rem;">
            <div>
              <h1 style="font-size: 2.4rem; margin-bottom: 0.35rem;"><?= htmlspecialchars($pet['name']) ?></h1>
              <div style="color: var(--text-muted); font-size: 1.05rem;">
                <?= htmlspecialchars($pet['breed'] ?: $pet['species']) ?>
              </div>
            </div>
            <span class="pet-gender-badge <?= $isMale ? 'gender-male' : 'gender-female' ?>" style="font-size: 1rem; padding: 0.35rem 0.85rem;">
              <i class="fa-solid <?= $isMale ? 'fa-mars' : 'fa-venus' ?>"></i> <?= htmlspecialchars($pet['gender']) ?>
            </span>
          </div>

          <!-- Specs 4-Box Grid -->
          <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin: 1.75rem 0; text-align: center;">
            <div style="background: #FFF9FB; padding: 1rem 0.5rem; border-radius: var(--radius-md); border: 1px solid #FFE7EF;">
              <span style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 0.25rem;">Jenis</span>
              <strong style="color: var(--text-main); font-size: 1rem;"><?= htmlspecialchars($pet['species']) ?></strong>
            </div>
            <div style="background: #FFF9FB; padding: 1rem 0.5rem; border-radius: var(--radius-md); border: 1px solid #FFE7EF;">
              <span style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 0.25rem;">Usia</span>
              <strong style="color: var(--text-main); font-size: 1rem;"><?= htmlspecialchars($pet['age']) ?></strong>
            </div>
            <div style="background: #FFF9FB; padding: 1rem 0.5rem; border-radius: var(--radius-md); border: 1px solid #FFE7EF;">
              <span style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 0.25rem;">Ukuran</span>
              <strong style="color: var(--text-main); font-size: 1rem;"><?= htmlspecialchars($pet['size'] ?: 'Sedang') ?></strong>
            </div>
            <div style="background: #FFF9FB; padding: 1rem 0.5rem; border-radius: var(--radius-md); border: 1px solid #FFE7EF;">
              <span style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 0.25rem;">Lokasi</span>
              <strong style="color: var(--text-main); font-size: 0.9rem;"><?= htmlspecialchars($pet['location']) ?></strong>
            </div>
          </div>

          <!-- Personality Tags -->
          <div style="margin-bottom: 1.8rem;">
            <h4 style="font-size: 0.95rem; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted); margin-bottom: 0.75rem;">Kepribadian & Karakter</h4>
            <div style="display: flex; gap: 0.6rem; flex-wrap: wrap;">
              <?php foreach ($temperaments as $trait): ?>
                <span class="pet-tag" style="padding: 0.45rem 1rem; font-size: 0.88rem; background: var(--secondary); color: var(--text-main);">
                  <i class="fa-solid fa-sparkles" style="color: var(--primary);"></i> <?= htmlspecialchars($trait) ?>
                </span>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- Description -->
          <div style="margin-bottom: 1.8rem;">
            <h4 style="font-size: 0.95rem; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted); margin-bottom: 0.6rem;">Tentang <?= htmlspecialchars($pet['name']) ?></h4>
            <p style="color: var(--text-main); line-height: 1.7; font-size: 1rem;">
              <?= nl2br(htmlspecialchars($pet['description'] ?? 'Anabul yang ramah dan siap diadopsi.')) ?>
            </p>
          </div>

          <!-- Medical & Care Info Cards -->
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 2rem;">
            <div style="background: #F8FAFC; border: 1px solid #E2E8F0; padding: 1.25rem; border-radius: var(--radius-md);">
              <div style="display: flex; align-items: center; gap: 0.6rem; margin-bottom: 0.4rem; color: #059669; font-weight: 700;">
                <i class="fa-solid fa-shield-virus"></i> Status Kesehatan
              </div>
              <p style="font-size: 0.88rem; color: var(--text-muted); margin: 0; line-height: 1.5;">
                <?= htmlspecialchars($pet['health'] ?: 'Sehat prima, siap diadopsi.') ?>
              </p>
              <div style="margin-top: 0.6rem; font-size: 0.82rem; color: var(--text-main);">
                <strong>Vaksinasi:</strong> <?= htmlspecialchars($pet['vaccinated'] ?: 'Lengkap') ?>
              </div>
            </div>

            <div style="background: #F8FAFC; border: 1px solid #E2E8F0; padding: 1.25rem; border-radius: var(--radius-md);">
              <div style="display: flex; align-items: center; gap: 0.6rem; margin-bottom: 0.4rem; color: var(--primary); font-weight: 700;">
                <i class="fa-solid fa-heart-pulse"></i> Kebutuhan Khusus
              </div>
              <p style="font-size: 0.88rem; color: var(--text-muted); margin: 0; line-height: 1.5;">
                <?= htmlspecialchars($pet['care_needs'] ?: 'Membutuhkan kasih sayang dan perhatian rutin.') ?>
              </p>
            </div>
          </div>

          <!-- Action Buttons -->
          <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
            <?php if ($isAvailable): ?>
              <a href="adoption-form.php?id=<?= urlencode($pet['id']) ?>" class="btn btn-primary btn-lg" style="flex: 1; justify-content: center;">
                <i class="fa-solid fa-file-signature"></i> Ajukan Adopsi Sekarang
              </a>
            <?php else: ?>
              <button disabled class="btn btn-secondary btn-lg disabled" style="flex: 1; justify-content: center; opacity: 0.6; cursor: not-allowed;">
                <i class="fa-solid fa-lock"></i> Saat Ini Sedang <?= htmlspecialchars($pet['status']) ?>
              </button>
            <?php endif; ?>
            <a href="animals.php" class="btn btn-secondary btn-lg" title="Kembali ke Katalog">
              <i class="fa-solid fa-arrow-left"></i>
            </a>
          </div>

        </div>
      </div>
    <?php endif; ?>
  </main>

  <style>
    @media (max-width: 900px) {
      .detail-layout-grid {
        grid-template-columns: 1fr !important;
      }
    }
  </style>

  <script>
    function switchDetailImage(url, el) {
      const mainImg = document.getElementById('detail-active-img');
      if (mainImg) mainImg.src = url;
      document.querySelectorAll('.thumb-item').forEach(t => t.style.borderColor = 'transparent');
      if (el) el.style.borderColor = 'var(--primary)';
    }
  </script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
