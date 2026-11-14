// PawMate - Admin Shared Script

// Ensure Admin Authentication Guard
function checkAdminAuth() {
  const user = DB.getCurrentUser();
  if (!user || user.role !== 'admin') {
    // If not logged in as admin, auto-login as demo admin for easy testing and show notice
    const demoAdmin = DB.getUserByEmail('admin@pawmate.id');
    if (demoAdmin) {
      DB.setCurrentUser(demoAdmin);
      showToast('Sesi Admin Aktif', 'Masuk otomatis sebagai Admin PawMate.', 'info');
    } else {
      window.location.href = 'login.html';
      return null;
    }
  }
  return DB.getCurrentUser();
}

// Calculate Dynamic Dashboard Statistics
function getAdminStats() {
  const pets = DB.getPets();
  const apps = DB.getApplications();

  const totalPets = pets.length;
  const availablePets = pets.filter(p => p.status === 'Tersedia').length;
  const adoptedPets = pets.filter(p => p.status === 'Sudah Diadopsi').length;

  const totalApps = apps.length;
  const pendingApps = apps.filter(a => a.status === 'Menunggu').length;
  const processApps = apps.filter(a => a.status === 'Dalam Proses').length;
  const approvedApps = apps.filter(a => a.status === 'Disetujui').length;
  const rejectedApps = apps.filter(a => a.status === 'Ditolak').length;

  return {
    totalPets,
    availablePets,
    adoptedPets,
    totalApps,
    pendingApps,
    processApps,
    approvedApps,
    rejectedApps
  };
}

// Setup Admin Mobile Sidebar Toggle
function setupAdminSidebar() {
  const toggleBtn = document.getElementById('admin-sidebar-toggle');
  const sidebar = document.querySelector('.admin-sidebar');
  if (toggleBtn && sidebar) {
    toggleBtn.addEventListener('click', () => {
      sidebar.classList.toggle('open');
    });
  }

  // Display current date
  const dateEl = document.getElementById('admin-current-date');
  if (dateEl) {
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    dateEl.textContent = new Date().toLocaleDateString('id-ID', options);
  }
}

// Admin Logout
function handleAdminLogout() {
  if (confirm('Keluar dari portal pengurus PawMate?')) {
    DB.logout();
    window.location.href = 'login.html';
  }
}
