<?php
/**
 * PawMate - Login Page (PHP & MySQL)
 */
require_once __DIR__ . '/config/auth.php';

// Redirect jika sudah login
if (isLoggedIn()) {
    $user = getCurrentUser();
    if ($user['role'] === 'admin') {
        header('Location: admin-dashboard.php');
    } else {
        header('Location: user-dashboard.php');
    }
    exit;
}

$errorMsg = '';
$db = getDB();

// Handle Demo 1-Click Login
if (isset($_GET['demo'])) {
    $demoRole = $_GET['demo'];
    $targetEmail = ($demoRole === 'admin') ? 'admin@pawmate.id' : 'rista@example.com';
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$targetEmail]);
    $demoUser = $stmt->fetch();

    if ($demoUser) {
        loginUser($demoUser);
        if ($demoUser['role'] === 'admin') {
            setFlash('success', 'Login Admin Berhasil', 'Selamat datang di Portal Admin PawMate!');
            header('Location: admin-dashboard.php');
        } else {
            setFlash('success', 'Login Berhasil', 'Selamat datang kembali, ' . $demoUser['name'] . '!');
            header('Location: user-dashboard.php');
        }
        exit;
    }
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $errorMsg = 'Harap isi alamat email dan kata sandi Anda.';
    } else {
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && verifyUserPassword($password, $user['password'])) {
            loginUser($user);
            if ($user['role'] === 'admin') {
                setFlash('success', 'Login Admin Berhasil', 'Selamat datang di Portal Admin PawMate!');
                header('Location: admin-dashboard.php');
            } else {
                setFlash('success', 'Masuk Berhasil', 'Selamat datang kembali, ' . $user['name'] . '!');
                header('Location: user-dashboard.php');
            }
            exit;
        } else {
            $errorMsg = 'Alamat email atau kata sandi tidak cocok. Silakan coba lagi.';
        }
    }
}

$pageTitle = "Masuk Akun";
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
    <div class="auth-card" style="background: #fff; border-radius: var(--radius-xl); padding: 2.8rem 2.5rem; width: 100%; max-width: 460px; box-shadow: var(--shadow-lg); border: 1px solid var(--border-light);">
      <div style="text-align: center; margin-bottom: 2rem;">
        <div class="logo-icon-wrap" style="margin: 0 auto 1rem; width: 50px; height: 50px; font-size: 1.5rem;">
          <i class="fa-solid fa-paw"></i>
        </div>
        <h1 style="font-size: 1.85rem; margin-bottom: 0.35rem;">Selamat Datang!</h1>
        <p style="color: var(--text-muted); font-size: 0.95rem;">Masuk untuk mengelola adopsi dan anabul favorit Anda.</p>
      </div>

      <!-- Error Alert Box -->
      <?php if (!empty($errorMsg)): ?>
      <div style="background:#FEE2E2; color:#DC2626; border:1px solid #FECACA; padding:0.8rem 1rem; border-radius:var(--radius-md); font-size:0.88rem; margin-bottom:1.25rem;">
        <i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($errorMsg) ?>
      </div>
      <?php endif; ?>

      <form method="POST" action="login.php">
        <div class="form-group">
          <label class="form-label" for="login-email">Email <span class="required">*</span></label>
          <div class="input-with-icon">
            <i class="fa-solid fa-envelope"></i>
            <input type="email" id="login-email" name="email" class="form-control" placeholder="nama@email.com" value="<?= htmlspecialchars($_POST['email'] ?? 'rista@example.com') ?>" required>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="login-password">Kata Sandi <span class="required">*</span></label>
          <div class="input-password-group input-with-icon">
            <i class="fa-solid fa-lock"></i>
            <input type="password" id="login-password" name="password" class="form-control" placeholder="Masukkan kata sandi" value="password" required>
            <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('login-password', this)" title="Lihat kata sandi">
              <i class="fa-solid fa-eye"></i>
            </button>
          </div>
        </div>

        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; font-size:0.85rem;">
          <label class="checkbox-label" style="font-size:0.85rem;">
            <input type="checkbox" checked> Ingat saya
          </label>
          <a href="#" onclick="alert('Untuk demonstrasi, silakan gunakan tombol Akses Cepat Demo di bawah.'); return false;" style="color:var(--primary); font-weight:600;">Lupa Sandi?</a>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%; height:48px; font-size:1rem;">
          <i class="fa-solid fa-arrow-right-to-bracket"></i> Masuk Sekarang
        </button>
      </form>

      <!-- Quick Demo Login Switcher -->
      <div style="margin-top: 1.8rem; padding: 1.25rem; background: #FFF9FB; border-radius: var(--radius-md); border: 1px dashed #F5C6D6;">
        <div style="font-size:0.8rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; margin-bottom:0.75rem; text-align:center;">
          <i class="fa-solid fa-bolt" style="color:#F59E0B"></i> Akses Cepat Demo (Pengujian XAMPP)
        </div>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.6rem;">
          <a href="login.php?demo=user" class="btn btn-secondary btn-sm" style="font-size:0.82rem; text-align:center;">
            <i class="fa-solid fa-user"></i> Akun Pengguna
          </a>
          <a href="login.php?demo=admin" class="btn btn-navy btn-sm" style="font-size:0.82rem; text-align:center; background:#1E2538; color:white;">
            <i class="fa-solid fa-shield-halved"></i> Akun Admin
          </a>
        </div>
        <p style="font-size:0.75rem; color:#94A3B8; margin-top:0.6rem; text-align:center;">
          * Data login terhubung langsung ke database MySQL (<code>pawmate_db</code>) di XAMPP.
        </p>
      </div>

      <div style="text-align:center; margin-top:1.75rem; font-size:0.92rem; color:var(--text-muted);">
        Belum punya akun? <a href="register.php" style="color:var(--primary); font-weight:700;">Daftar di sini</a>
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
