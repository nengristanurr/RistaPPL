<?php
/**
 * PawMate - Reusable HTML Footer
 */
$flash = getFlash();
?>
  <!-- Footer -->
  <footer class="site-footer">
    <div class="container">
      <div class="footer-grid">
        <div class="footer-brand-col">
          <div class="footer-brand">
            <div class="logo-icon-wrap">
              <i class="fa-solid fa-paw"></i>
            </div>
            <span>PawMate</span>
          </div>
          <p class="footer-desc">
            PawMate adalah platform adopsi hewan non-profit yang menghubungkan ribuan anabul terlantar dengan keluarga baru yang penuh kasih sayang. Bersama kita wujudkan rumah hangat bagi mereka.
          </p>
          <div class="footer-socials">
            <a href="#" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
            <a href="#" aria-label="TikTok"><i class="fa-brands fa-tiktok"></i></a>
            <a href="#" aria-label="Facebook"><i class="fa-brands fa-facebook"></i></a>
            <a href="#" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
          </div>
        </div>

        <div class="footer-col">
          <h4>Jelajahi</h4>
          <ul class="footer-links">
            <li><a href="index.php">Beranda</a></li>
            <li><a href="animals.php">Katalog Hewan</a></li>
            <li><a href="animals.php?species=Anjing">Adopsi Anjing</a></li>
            <li><a href="animals.php?species=Kucing">Adopsi Kucing</a></li>
            <li><a href="index.php#steps">Alur Adopsi</a></li>
          </ul>
        </div>

        <div class="footer-col">
          <h4>Akun & Layanan</h4>
          <ul class="footer-links">
            <li><a href="login.php">Masuk Akun</a></li>
            <li><a href="register.php">Pendaftaran Member</a></li>
            <li><a href="user-dashboard.php">Dashboard Saya</a></li>
            <li><a href="admin-dashboard.php">Portal Pengurus (Admin)</a></li>
          </ul>
        </div>

        <div class="footer-col">
          <h4>Hubungi Kami</h4>
          <div class="footer-contact-item">
            <i class="fa-solid fa-location-dot"></i>
            <span>Jl. Kasih Anabul No. 88, Kebayoran Baru, Jakarta Selatan</span>
          </div>
          <div class="footer-contact-item">
            <i class="fa-solid fa-envelope"></i>
            <span>halo@pawmate.id</span>
          </div>
          <div class="footer-contact-item">
            <i class="fa-solid fa-phone"></i>
            <span>+62 812-3456-7890 (WhatsApp)</span>
          </div>
        </div>
      </div>

      <div class="footer-bottom">
        <p>&copy; 2026 PawMate Indonesia (Powered by PHP & MySQL XAMPP). Hak cipta dilindungi undang-undang.</p>
        <p>Dirancang dengan penuh <i class="fa-solid fa-heart" style="color:var(--primary)"></i> untuk hewan terlantar.</p>
      </div>
    </div>
  </footer>

  <div id="toast-container"></div>

  <script>
    // Toast Notification Utility
    function showToast(title, message = '', type = 'info') {
      let container = document.getElementById('toast-container');
      if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        document.body.appendChild(container);
      }

      const toast = document.createElement('div');
      toast.className = `toast ${type}`;

      let iconClass = 'fa-solid fa-circle-info';
      if (type === 'success') iconClass = 'fa-solid fa-circle-check';
      if (type === 'error') iconClass = 'fa-solid fa-triangle-exclamation';

      toast.innerHTML = `
        <div class="toast-icon"><i class="${iconClass}"></i></div>
        <div class="toast-content">
          <div class="toast-title">${title}</div>
          ${message ? `<div class="toast-desc">${message}</div>` : ''}
        </div>
        <button class="toast-close" onclick="this.parentElement.remove()"><i class="fa-solid fa-xmark"></i></button>
      `;

      container.appendChild(toast);

      setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        toast.style.transition = 'all 0.3s ease';
        setTimeout(() => toast.remove(), 300);
      }, 4000);
    }

    // Toggle favorite via AJAX to MySQL
    function handleToggleFavorite(petId, e) {
      if (e) {
        e.stopPropagation();
        e.preventDefault();
      }

      fetch('api/toggle-favorite.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'pet_id=' + encodeURIComponent(petId)
      })
      .then(res => res.json())
      .then(data => {
        if (!data.success) {
          if (data.redirect) {
            window.location.href = data.redirect;
            return;
          }
          showToast('Pemberitahuan', data.message || 'Terjadi kesalahan.', 'error');
          return;
        }

        const isFav = data.is_favorite;
        const btns = document.querySelectorAll(`[data-favorite-pet="${petId}"]`);
        btns.forEach(btn => {
          if (isFav) {
            btn.classList.add('active');
            btn.innerHTML = '<i class="fa-solid fa-heart"></i>';
          } else {
            btn.classList.remove('active');
            btn.innerHTML = '<i class="fa-regular fa-heart"></i>';
          }
        });

        if (isFav) {
          showToast('Tersimpan di Favorit', `${data.pet_name || 'Hewan'} ditambahkan ke favorit.`, 'success');
        } else {
          showToast('Dihapus dari Favorit', `${data.pet_name || 'Hewan'} dihapus dari favorit.`, 'info');
        }

        if (typeof renderUserFavorites === 'function') {
          location.reload();
        }
      })
      .catch(err => {
        console.error(err);
        showToast('Koneksi Gagal', 'Tidak dapat menghubungi server MySQL.', 'error');
      });
    }

    // Setup Mobile Navigation Menu
    const toggleBtn = document.getElementById('mobile-menu-toggle');
    const navMenu = document.getElementById('nav-menu-list');
    if (toggleBtn && navMenu) {
      toggleBtn.addEventListener('click', () => {
        navMenu.classList.toggle('active');
      });
    }

    <?php if ($flash): ?>
    // Auto-display flash message from PHP
    document.addEventListener('DOMContentLoaded', () => {
      showToast(<?= json_encode($flash['title']) ?>, <?= json_encode($flash['text']) ?>, <?= json_encode($flash['type']) ?>);
    });
    <?php endif; ?>
  </script>
</body>
</html>
