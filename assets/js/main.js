// PawMate - Shared Utilities & Main Frontend Scripts

// Toast Notification System
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

// Global Favorite Toggle
function handleToggleFavorite(petId, e) {
  if (e) {
    e.stopPropagation();
    e.preventDefault();
  }
  const isFav = DB.toggleFavorite(petId);
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

  const pet = DB.getPetById(petId);
  const petName = pet ? pet.name : 'Hewan';
  if (isFav) {
    showToast('Tersimpan di Favorit', `${petName} berhasil ditambahkan ke daftar favorit Anda.`, 'success');
  } else {
    showToast('Dihapus dari Favorit', `${petName} dihapus dari daftar favorit.`, 'info');
  }

  // If on user dashboard favorites tab, trigger refresh
  if (typeof renderUserFavorites === 'function') {
    renderUserFavorites();
  }
}

// Render Reusable Pinterest-Style Pet Card
function createPetCardHTML(pet) {
  const isFav = DB.isFavorite(pet.id);
  const isMale = pet.gender === 'Jantan';
  const isAvailable = pet.status === 'Tersedia';

  let statusBadgeClass = 'badge-approved';
  if (pet.status === 'Dalam Proses') statusBadgeClass = 'badge-process';
  if (pet.status === 'Sudah Diadopsi') statusBadgeClass = 'badge-rejected';

  return `
    <div class="pet-card" data-pet-id="${pet.id}">
      <div class="pet-card-image-wrap">
        <span class="pet-status-pill badge ${statusBadgeClass}">
          <i class="fa-solid fa-circle" style="font-size:0.5rem"></i> ${pet.status}
        </span>
        <button class="pet-favorite-btn ${isFav ? 'active' : ''}" data-favorite-pet="${pet.id}" onclick="handleToggleFavorite('${pet.id}', event)" title="Simpan ke favorit">
          <i class="${isFav ? 'fa-solid' : 'fa-regular'} fa-heart"></i>
        </button>
        <img src="${pet.image}" alt="${pet.name}" loading="lazy" onerror="this.src='https://images.unsplash.com/photo-1543466835-00a7907e9de1?auto=format&fit=crop&w=800&q=80'">
      </div>
      <div class="pet-card-body">
        <div class="pet-card-meta">
          <h3 class="pet-name">${pet.name}</h3>
          <span class="pet-gender-badge ${isMale ? 'gender-male' : 'gender-female'}">
            <i class="fa-solid ${isMale ? 'fa-mars' : 'fa-venus'}"></i> ${pet.gender}
          </span>
        </div>
        <span class="pet-breed-tag">${pet.breed || pet.species} • ${pet.age}</span>
        
        <div class="pet-tags-row">
          <span class="pet-tag"><i class="fa-solid fa-shield-cat"></i> ${pet.vaccinated || 'Vaksin Rutin'}</span>
          <span class="pet-tag"><i class="fa-solid fa-shapes"></i> ${pet.species}</span>
        </div>

        <div class="pet-card-footer">
          <span class="pet-location">
            <i class="fa-solid fa-location-dot"></i> ${pet.location}
          </span>
          <a href="animal-detail.html?id=${pet.id}" class="btn btn-secondary btn-sm">
            Lihat Detail <i class="fa-solid fa-arrow-right" style="font-size:0.75rem"></i>
          </a>
        </div>
      </div>
    </div>
  `;
}

// Setup User Authentication State in Standard Navbar
function updateNavbarAuth() {
  const navActions = document.getElementById('navbar-auth-actions');
  if (!navActions) return;

  const currentUser = DB.getCurrentUser();

  if (currentUser) {
    const isUserAdmin = currentUser.role === 'admin';
    navActions.innerHTML = `
      <div style="display:flex; align-items:center; gap:0.75rem;">
        <a href="${isUserAdmin ? 'admin-dashboard.html' : 'user-dashboard.html'}" class="user-menu-btn">
          <div class="user-avatar-sm">
            ${currentUser.name.charAt(0).toUpperCase()}
          </div>
          <span class="user-name-label">${currentUser.name.split(' ')[0]}</span>
          <span class="badge ${isUserAdmin ? 'badge-tag' : 'badge-approved'}" style="font-size:0.7rem; padding:0.15rem 0.5rem;">
            ${isUserAdmin ? 'Admin' : 'Member'}
          </span>
        </a>
        <button onclick="handleLogout()" class="btn btn-outline btn-sm" title="Keluar">
          <i class="fa-solid fa-arrow-right-from-bracket"></i>
        </button>
      </div>
    `;
  } else {
    navActions.innerHTML = `
      <a href="login.html" class="btn btn-outline btn-sm">Masuk</a>
      <a href="register.html" class="btn btn-primary btn-sm">Daftar</a>
    `;
  }
}

// Global Logout Handler
function handleLogout() {
  if (confirm('Apakah Anda yakin ingin keluar dari akun?')) {
    DB.logout();
    showToast('Berhasil Keluar', 'Anda telah keluar dari sesi akun.', 'info');
    setTimeout(() => {
      window.location.href = 'login.html';
    }, 500);
  }
}

// Mobile Navbar Toggle
function setupMobileMenu() {
  const toggleBtn = document.getElementById('mobile-menu-toggle');
  const navMenu = document.getElementById('nav-menu-list');
  if (toggleBtn && navMenu) {
    toggleBtn.addEventListener('click', () => {
      navMenu.classList.toggle('active');
    });
  }
}

// Initialize on DOM load
document.addEventListener('DOMContentLoaded', () => {
  updateNavbarAuth();
  setupMobileMenu();
});
