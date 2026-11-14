<?php
/**
 * PawMate - Registration Page (PHP & MySQL)
 */
require_once __DIR__ . '/config/auth.php';

if (isLoggedIn()) {
    header('Location: user-dashboard.php');
    exit;
}

$errorMsg = '';
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($name) || empty($email) || empty($phone) || empty($password)) {
        $errorMsg = 'Mohon lengkapi semua kolom pendaftaran.';
    } elseif (strlen($password) < 5) {
        $errorMsg = 'Kata sandi minimal terdiri dari 5 karakter.';
    } elseif ($password !== $confirmPassword) {
        $errorMsg = 'Konfirmasi kata sandi tidak cocok dengan kata sandi.';
    } else {
        // Cek email ganda
        $stmtCheck = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmtCheck->execute([$email]);
        if ($stmtCheck->fetch()) {
            $errorMsg = 'Alamat email ini sudah terdaftar. Silakan gunakan email lain atau masuk.';
        } else {
            $newId = 'usr-' . time();
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $today = date('Y-m-d');

            $stmtInsert = $db->prepare("INSERT INTO users 
                (id, name, email, phone, password, role, address, occupation, registered_at)
                VALUES (?, ?, ?, ?, ?, 'user', 'Indonesia', 'Penyayang Hewan', ?)");
            $stmtInsert->execute([$newId, $name, $email, $phone, $hash, $today]);

            // Ambil data user yang baru dibuat
            $stmtUser = $db->prepare("SELECT * FROM users WHERE id = ?");
            $stmtUser->execute([$newId]);
            $newUser = $stmtUser->fetch();

            loginUser($newUser);
            setFlash('success', 'Registrasi Berhasil', 'Akun Anda telah dibuat. Selamat bergabung, ' . $name . '!');
            header('Location: user-dashboard.php');
            exit;
        }
    }
}

$pageTitle = "Daftar Akun Baru";
require_once __DIR__ . '/includes/header.php';
?>

  <!-- Site Header / Navbar -->
  <header class="site-header">
    <div class="container navbar">
      <a href="index.php" class="logo-brand">
        <div class="logo-icon-wrap">
          <i class="fa-solid fa-paw"></i>
        </div>
        <span>PawMate</span>
      </a>

      <div class="nav-actions">
        <a href="index.php" class="btn btn-outline btn-sm"><i class="fa-solid fa-arrow-left"></i> Kembali ke Beranda</a>
      </div>
    </div>
  </header>

  <div class="auth-page-wrap" style="min-height: calc(100vh - 160px); display: flex; align-items: center; justify-content: center; padding: 3rem 1.5rem; background: radial-gradient(circle at top right, #FFE8F0 0%, #FFF9FB 60%);">
    <div class="auth-card" style="background: #fff; border-radius: var(--radius-xl); padding: 2.8rem 2.5rem; width: 100%; max-width: 480px; box-shadow: var(--shadow-lg); border: 1px solid var(--border-light);">
      <div style="text-align: center; margin-bottom: 2rem;">
        <div class="logo-icon-wrap" style="margin: 0 auto 1rem; width: 50px; height: 50px; font-size: 1.5rem;">
          <i class="fa-solid fa-paw"></i>
        </div>
        <h1 style="font-size: 1.85rem; margin-bottom: 0.35rem;">Bergabung Bersama Kami</h1>
        <p style="color: var(--text-muted); font-size: 0.95rem;">Buat akun untuk mengajukan adopsi dan menyelamatkan anabul.</p>
      </div>

      <!-- Error Alert Box -->
      <?php if (!empty($errorMsg)): ?>
      <div style="background:#FEE2E2; color:#DC2626; border:1px solid #FECACA; padding:0.8rem 1rem; border-radius:var(--radius-md); font-size:0.88rem; margin-bottom:1.25rem;">
        <i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($errorMsg) ?>
      </div>
      <?php endif; ?>

      <form method="POST" action="register.php">
        <div class="form-group">
          <label class="form-label" for="reg-name">Nama Lengkap <span class="required">*</span></label>
          <div class="input-with-icon">
            <i class="fa-solid fa-user"></i>
            <input type="text" id="reg-name" name="name" class="form-control" placeholder="cth. Rista Purnamasari" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="reg-email">Alamat Email <span class="required">*</span></label>
          <div class="input-with-icon">
            <i class="fa-solid fa-envelope"></i>
            <input type="email" id="reg-email" name="email" class="form-control" placeholder="nama@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="reg-phone">Nomor WhatsApp <span class="required">*</span></label>
          <div class="input-with-icon">
            <i class="fa-solid fa-phone"></i>
            <input type="tel" id="reg-phone" name="phone" class="form-control" placeholder="081234567890" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" required>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="reg-password">Kata Sandi (Minimal 5 karakter) <span class="required">*</span></label>
          <div class="input-password-group input-with-icon">
            <i class="fa-solid fa-lock"></i>
            <input type="password" id="reg-password" name="password" class="form-control" placeholder="Buat kata sandi aman" required>
            <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('reg-password', this)" title="Lihat kata sandi">
              <i class="fa-solid fa-eye"></i>
            </button>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="reg-confirm-password">Konfirmasi Kata Sandi <span class="required">*</span></label>
          <div class="input-password-group input-with-icon">
            <i class="fa-solid fa-lock"></i>
            <input type="password" id="reg-confirm-password" name="confirm_password" class="form-control" placeholder="Ulangi kata sandi" required>
            <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('reg-confirm-password', this)" title="Lihat kata sandi">
              <i class="fa-solid fa-eye"></i>
            </button>
          </div>
        </div>

        <div style="margin-bottom: 1.5rem; font-size: 0.85rem; color: var(--text-muted);">
          Dengan mendaftar, Anda menyetujui komitmen kesejahteraan hewan dan kebijakan privasi PawMate.
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%; height:48px; font-size:1rem;">
          <i class="fa-solid fa-user-plus"></i> Buat Akun Sekarang
        </button>
      </form>

      <div style="text-align:center; margin-top:1.75rem; font-size:0.92rem; color:var(--text-muted);">
        Sudah memiliki akun? <a href="login.php" style="color:var(--primary); font-weight:700;">Masuk di sini</a>
      </div>
    </div>
  </div>

  <script>
    function togglePasswordVisibility(inputId, btn) {
      const input = document.getElementById(inputId);
      if (!input) return;
      if (input.type === 'password') {
        input.type = 'text';
        btn.innerHTML = '<i class="fa-solid fa-eye-slash"></i>';
      } else {
        input.type = 'password';
        btn.innerHTML = '<i class="fa-solid fa-eye"></i>';
      }
    }
  </script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
