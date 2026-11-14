// PawMate - Authentication Helper (Simulation / Demo)

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

// Quick Demo Login (Convenient for evaluators & students)
function loginAsDemo(role) {
  if (role === 'admin') {
    const adminUser = DB.getUserByEmail('admin@pawmate.id');
    if (adminUser) {
      DB.setCurrentUser(adminUser);
      showToast('Login Admin Berhasil', 'Selamat datang di Portal Admin PawMate!', 'success');
      setTimeout(() => window.location.href = 'admin-dashboard.html', 800);
    }
  } else {
    const regularUser = DB.getUserByEmail('rista@example.com');
    if (regularUser) {
      DB.setCurrentUser(regularUser);
      showToast('Login Berhasil', 'Selamat datang kembali, Rista!', 'success');
      setTimeout(() => window.location.href = 'user-dashboard.html', 800);
    }
  }
}

// Login Handler
function handleLoginSubmit(e) {
  e.preventDefault();
  const emailInput = document.getElementById('login-email');
  const passwordInput = document.getElementById('login-password');
  const errEl = document.getElementById('login-error-msg');

  if (errEl) errEl.style.display = 'none';

  const email = emailInput.value.trim();
  const password = passwordInput.value;

  if (!email || !password) {
    if (errEl) {
      errEl.textContent = 'Harap isi alamat email dan kata sandi Anda.';
      errEl.style.display = 'block';
    }
    return;
  }

  const user = DB.getUserByEmail(email);

  // Simple simulated authentication
  if (!user || user.password !== password) {
    if (errEl) {
      errEl.textContent = 'Alamat email atau kata sandi tidak cocok. Silakan coba lagi.';
      errEl.style.display = 'block';
    }
    return;
  }

  DB.setCurrentUser(user);
  showToast('Masuk Berhasil', `Selamat datang kembali, ${user.name}!`, 'success');

  setTimeout(() => {
    if (user.role === 'admin') {
      window.location.href = 'admin-dashboard.html';
    } else {
      window.location.href = 'user-dashboard.html';
    }
  }, 700);
}

// Register Handler
function handleRegisterSubmit(e) {
  e.preventDefault();
  const name = document.getElementById('reg-name').value.trim();
  const email = document.getElementById('reg-email').value.trim();
  const phone = document.getElementById('reg-phone').value.trim();
  const password = document.getElementById('reg-password').value;
  const confirmPassword = document.getElementById('reg-confirm-password').value;
  const errEl = document.getElementById('reg-error-msg');

  if (errEl) errEl.style.display = 'none';

  if (!name || !email || !phone || !password || !confirmPassword) {
    if (errEl) {
      errEl.textContent = 'Mohon lengkapi semua kolom pendaftaran.';
      errEl.style.display = 'block';
    }
    return;
  }

  if (password.length < 5) {
    if (errEl) {
      errEl.textContent = 'Kata sandi minimal terdiri dari 5 karakter.';
      errEl.style.display = 'block';
    }
    return;
  }

  if (password !== confirmPassword) {
    if (errEl) {
      errEl.textContent = 'Konfirmasi kata sandi tidak cocok dengan kata sandi.';
      errEl.style.display = 'block';
    }
    return;
  }

  // Check if email already registered
  const existing = DB.getUserByEmail(email);
  if (existing) {
    if (errEl) {
      errEl.textContent = 'Alamat email ini sudah terdaftar. Silakan gunakan email lain atau masuk.';
      errEl.style.display = 'block';
    }
    return;
  }

  const newUser = {
    id: 'usr-' + Date.now(),
    name,
    email,
    phone,
    password, // Stored in LocalStorage for demo purposes
    role: 'user',
    registeredAt: new Date().toISOString().slice(0, 10),
    address: 'Indonesia',
    occupation: 'Penyayang Hewan'
  };

  DB.saveUser(newUser);
  DB.setCurrentUser(newUser);

  showToast('Registrasi Berhasil', `Akun Anda telah dibuat. Selamat bergabung, ${name}!`, 'success');

  setTimeout(() => {
    window.location.href = 'user-dashboard.html';
  }, 900);
}
