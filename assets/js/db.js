// PawMate Database & LocalStorage Manager

const STORAGE_KEYS = {
  PETS: 'pawmate_pets',
  APPLICATIONS: 'pawmate_applications',
  USERS: 'pawmate_users',
  CURRENT_USER: 'pawmate_current_user',
  FAVORITES: 'pawmate_favorites'
};

// Initial Demo Pets
const DEFAULT_PETS = [
  {
    id: 'pet-1',
    name: 'Milo',
    species: 'Anjing',
    breed: 'Golden Retriever Mix',
    age: '1.5 Tahun',
    gender: 'Jantan',
    size: 'Sedang',
    location: 'Jakarta Selatan',
    status: 'Tersedia', // Tersedia, Dalam Proses, Sudah Diadopsi
    image: 'https://images.unsplash.com/photo-1552053831-71594a27632d?auto=format&fit=crop&w=800&q=80',
    gallery: [
      'https://images.unsplash.com/photo-1552053831-71594a27632d?auto=format&fit=crop&w=800&q=80',
      'https://images.unsplash.com/photo-1537151625747-768eb6cf92b2?auto=format&fit=crop&w=800&q=80',
      'https://images.unsplash.com/photo-1583511655857-d19b40a7a54e?auto=format&fit=crop&w=800&q=80'
    ],
    description: 'Milo adalah anjing berenergi positif yang sangat menyukai bermain bola dan ramah kepada anak-anak. Milo diselamatkan dari shelter lingkungan dan siap menjadi sahabat setia keluarga Anda.',
    temperament: ['Ramah', 'Aktif', 'Suka Bermain', 'Penyayang'],
    health: 'Sehat prima, sudah disteril, bebas kutu dan jamur.',
    vaccinated: 'Lengkap (Rabies, DHPP)',
    careNeeds: 'Membutuhkan jalan pagi minimal 30 menit dan ruang gerak yang cukup.',
    createdAt: '2026-09-15'
  },
  {
    id: 'pet-2',
    name: 'Luna',
    species: 'Kucing',
    breed: 'British Shorthair Mix',
    age: '8 Bulan',
    gender: 'Betina',
    size: 'Kecil',
    location: 'Bandung',
    status: 'Tersedia',
    image: 'https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?auto=format&fit=crop&w=800&q=80',
    gallery: [
      'https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?auto=format&fit=crop&w=800&q=80',
      'https://images.unsplash.com/photo-1573865526739-10659fec78a5?auto=format&fit=crop&w=800&q=80',
      'https://images.unsplash.com/photo-1495360010541-f48722b34f7d?auto=format&fit=crop&w=800&q=80'
    ],
    description: 'Luna adalah kucing manis bermata bulat yang suka mendengkur ketika dielus di bawah dagu. Sangat tenang dan cocok untuk yang tinggal di apartemen atau rumah minimalis.',
    temperament: ['Manja', 'Tenang', 'Pintar', 'Indoor Cat'],
    health: 'Sangat sehat, rutin potong kuku, telinga bersih.',
    vaccinated: 'Lengkap (Tricat, Rabies)',
    careNeeds: 'Penyisiran bulu halus 2x seminggu dan litter box yang selalu bersih.',
    createdAt: '2026-09-20'
  },
  {
    id: 'pet-3',
    name: 'Coco',
    species: 'Anjing',
    breed: 'Corgi Mix',
    age: '2 Tahun',
    gender: 'Jantan',
    size: 'Sedang',
    location: 'Tangerang Selatan',
    status: 'Tersedia',
    image: 'https://images.unsplash.com/photo-1612536057832-2ff7ead58194?auto=format&fit=crop&w=800&q=80',
    gallery: [
      'https://images.unsplash.com/photo-1612536057832-2ff7ead58194?auto=format&fit=crop&w=800&q=80',
      'https://images.unsplash.com/photo-1589941013453-ec89f33b5e95?auto=format&fit=crop&w=800&q=80'
    ],
    description: 'Coco berkepribadian ceria dan menggemaskan dengan langkah kakinya yang lincah. Sudah terlatih buang air di pee pad dan mengerti perintah dasar seperti duduk dan salaman.',
    temperament: ['Cerdas', 'Lincah', 'Setia', 'Mudah Dilatih'],
    health: 'Sehat, sudah vaksinasi tahunan & obat cacing rutin.',
    vaccinated: 'Lengkap',
    careNeeds: 'Pola makan teratur untuk menjaga berat badan ideal.',
    createdAt: '2026-09-22'
  },
  {
    id: 'pet-4',
    name: 'Bella',
    species: 'Kucing',
    breed: 'Persian Calico',
    age: '1 Tahun',
    gender: 'Betina',
    size: 'Kecil',
    location: 'Jakarta Barat',
    status: 'Tersedia',
    image: 'https://images.unsplash.com/photo-1533738363-b7f9aef128ce?auto=format&fit=crop&w=800&q=80',
    gallery: [
      'https://images.unsplash.com/photo-1533738363-b7f9aef128ce?auto=format&fit=crop&w=800&q=80',
      'https://images.unsplash.com/photo-1561948955-570b270e7c36?auto=format&fit=crop&w=800&q=80'
    ],
    description: 'Bella adalah kucing berbulu lebat tiga warna yang cantik jelita. Memiliki pembawaan anggun dan senang bersantai di dekat jendela menikmati sinar matahari sore.',
    temperament: ['Anggun', 'Penyabar', 'Pemalu Awalnya', 'Lembut'],
    health: 'Sudah steril, gigi bersih, mata sehat terawat.',
    vaccinated: 'Lengkap (Tricat & Rabies)',
    careNeeds: 'Penyisiran bulu setiap hari dan ruangan sejuk (ber-AC lebih disukai).',
    createdAt: '2026-09-25'
  },
  {
    id: 'pet-5',
    name: 'Mochi',
    species: 'Kucing',
    breed: 'Domestic Short Hair',
    age: '5 Bulan',
    gender: 'Jantan',
    size: 'Kecil',
    location: 'Depok',
    status: 'Tersedia',
    image: 'https://images.unsplash.com/photo-1574158622682-e40e69881006?auto=format&fit=crop&w=800&q=80',
    gallery: [
      'https://images.unsplash.com/photo-1574158622682-e40e69881006?auto=format&fit=crop&w=800&q=80',
      'https://images.unsplash.com/photo-1543852786-1cf6624b9987?auto=format&fit=crop&w=800&q=80'
    ],
    description: 'Kitten mungil putih dengan motif belang abu yang super aktif! Suka mengejar tali dan bola kerincing. Sangat sosial dan mudah akrab dengan siapa saja.',
    temperament: ['Super Aktif', 'Pemberani', 'Fasih Mengeong', 'Sosial'],
    health: 'Sehat, sudah obat cacing, bebas kutu.',
    vaccinated: 'Vaksin Tahap 1 Selesai',
    careNeeds: 'Makanan kitten bernutrisi tinggi dan mainan interaktif.',
    createdAt: '2026-09-28'
  },
  {
    id: 'pet-6',
    name: 'Oreo',
    species: 'Anjing',
    breed: 'Border Collie Mix',
    age: '3 Tahun',
    gender: 'Jantan',
    size: 'Sedang',
    location: 'Bekasi',
    status: 'Tersedia',
    image: 'https://images.unsplash.com/photo-1561037404-61cd46aa615b?auto=format&fit=crop&w=800&q=80',
    gallery: [
      'https://images.unsplash.com/photo-1561037404-61cd46aa615b?auto=format&fit=crop&w=800&q=80',
      'https://images.unsplash.com/photo-1517849845537-4d257902454a?auto=format&fit=crop&w=800&q=80'
    ],
    description: 'Oreo adalah teman petualang sejati. Memiliki kecerdasan tinggi, sangat cepat belajar trik baru, dan sangat protektif serta menyayangi pemiliknya.',
    temperament: ['Cerdas Luar Biasa', 'Setia', 'Fokus', 'Protektif'],
    health: 'Prima & atletis, sudah vaksin lengkap dan steril.',
    vaccinated: 'Lengkap',
    careNeeds: 'Memerlukan latihan stimulasi mental dan olahraga harian.',
    createdAt: '2026-10-01'
  },
  {
    id: 'pet-7',
    name: 'Cleo',
    species: 'Kucing',
    breed: 'Siamese Mix',
    age: '1.2 Tahun',
    gender: 'Betina',
    size: 'Kecil',
    location: 'Bogor',
    status: 'Tersedia',
    image: 'https://images.unsplash.com/photo-1513360309081-36f20ca480d0?auto=format&fit=crop&w=800&q=80',
    gallery: [
      'https://images.unsplash.com/photo-1513360309081-36f20ca480d0?auto=format&fit=crop&w=800&q=80',
      'https://images.unsplash.com/photo-1526336024174-e58f5cdd8e13?auto=format&fit=crop&w=800&q=80'
    ],
    description: 'Cleo bermata biru kristal menawan dengan suara vokal yang lembut menyapa saat pemilik pulang ke rumah. Sangat suka tidur melingkar di atas selimut hangat.',
    temperament: ['Komunikatif', 'Hangat', 'Penyayang', 'Manja'],
    health: 'Sehat & terawat, sudah steril.',
    vaccinated: 'Lengkap',
    careNeeds: 'Tempat tidur yang hangat dan perhatian penuh kasih sayang.',
    createdAt: '2026-10-03'
  },
  {
    id: 'pet-8',
    name: 'Rocky',
    species: 'Anjing',
    breed: 'Beagle',
    age: '2.5 Tahun',
    gender: 'Jantan',
    size: 'Sedang',
    location: 'Jakarta Utara',
    status: 'Tersedia',
    image: 'https://images.unsplash.com/photo-1505628346881-b72b27e84530?auto=format&fit=crop&w=800&q=80',
    gallery: [
      'https://images.unsplash.com/photo-1505628346881-b72b27e84530?auto=format&fit=crop&w=800&q=80',
      'https://images.unsplash.com/photo-1544568100-847a948585b9?auto=format&fit=crop&w=800&q=80'
    ],
    description: 'Rocky adalah anjing lucu bertelinga panjang yang selalu ceria mengendus jejak petualangan baru. Sangat bersahabat dengan anak-anak maupun anjing lain.',
    temperament: ['Ceria', 'Eksploratif', 'Ramah', 'Suka Mencium Aroma'],
    health: 'Sangat sehat, rutin kontrol ke dokter hewan.',
    vaccinated: 'Lengkap (DHPPi, Rabies)',
    careNeeds: 'Halaman berpagar aman dan jalan santai harian.',
    createdAt: '2026-10-05'
  }
];

// Initial Demo Users
const DEFAULT_USERS = [
  {
    id: 'usr-admin',
    name: 'Admin PawMate',
    email: 'admin@pawmate.id',
    phone: '081299887766',
    password: 'admin', // Simple demo auth
    role: 'admin',
    registeredAt: '2026-08-01',
    address: 'Jl. Melati Indah No. 12, Jakarta',
    occupation: 'Shelter Manager PawMate'
  },
  {
    id: 'usr-demo',
    name: 'Rista Purnamasari',
    email: 'rista@example.com',
    phone: '081234567890',
    password: 'password', // Simple demo auth
    role: 'user',
    registeredAt: '2026-09-10',
    address: 'Jl. Cempaka Putih No. 45, Jakarta Pusat',
    occupation: 'Creative Designer & Dog Lover'
  }
];

// Initial Demo Applications
const DEFAULT_APPLICATIONS = [
  {
    id: 'app-1001',
    userId: 'usr-demo',
    userName: 'Rista Purnamasari',
    userEmail: 'rista@example.com',
    userPhone: '081234567890',
    petId: 'pet-1',
    petName: 'Milo',
    petSpecies: 'Anjing',
    petImage: 'https://images.unsplash.com/photo-1552053831-71594a27632d?auto=format&fit=crop&w=800&q=80',
    address: 'Jl. Cempaka Putih No. 45, Jakarta Pusat',
    occupation: 'Creative Designer',
    housingType: 'Rumah dengan Halaman Berpagar',
    petExperience: 'Pernah memelihara anjing Golden selama 7 tahun.',
    adoptionReason: 'Mencari sahabat baru setelah anjing sebelumnya meninggal karena usia tua. Keluarga kami sangat menyukai anjing aktif.',
    commitmentAgreed: true,
    status: 'Menunggu', // Menunggu, Dalam Proses, Disetujui, Ditolak
    adminNotes: 'Sedang menunggu jadwal wawancara virtual dengan pemohon.',
    appliedAt: '2026-10-10 14:30'
  }
];

// Initial Demo Favorites for default user
const DEFAULT_FAVORITES = ['pet-2', 'pet-4'];

// Initialize Storage with seed data if not present
function initDB() {
  if (!localStorage.getItem(STORAGE_KEYS.PETS)) {
    localStorage.setItem(STORAGE_KEYS.PETS, JSON.stringify(DEFAULT_PETS));
  }
  if (!localStorage.getItem(STORAGE_KEYS.USERS)) {
    localStorage.setItem(STORAGE_KEYS.USERS, JSON.stringify(DEFAULT_USERS));
  }
  if (!localStorage.getItem(STORAGE_KEYS.APPLICATIONS)) {
    localStorage.setItem(STORAGE_KEYS.APPLICATIONS, JSON.stringify(DEFAULT_APPLICATIONS));
  }
  if (!localStorage.getItem(STORAGE_KEYS.FAVORITES)) {
    localStorage.setItem(STORAGE_KEYS.FAVORITES, JSON.stringify(DEFAULT_FAVORITES));
  }
}

// Data Access Object
const DB = {
  // Pets
  getPets() {
    initDB();
    return JSON.parse(localStorage.getItem(STORAGE_KEYS.PETS)) || [];
  },
  getPetById(id) {
    const pets = this.getPets();
    return pets.find(p => p.id === id) || null;
  },
  savePet(pet) {
    const pets = this.getPets();
    const idx = pets.findIndex(p => p.id === pet.id);
    if (idx >= 0) {
      pets[idx] = pet;
    } else {
      pets.unshift(pet);
    }
    localStorage.setItem(STORAGE_KEYS.PETS, JSON.stringify(pets));
    return pet;
  },
  deletePet(id) {
    let pets = this.getPets();
    pets = pets.filter(p => p.id !== id);
    localStorage.setItem(STORAGE_KEYS.PETS, JSON.stringify(pets));
  },
  updatePetStatus(id, newStatus) {
    const pet = this.getPetById(id);
    if (pet) {
      pet.status = newStatus;
      this.savePet(pet);
    }
  },

  // Applications
  getApplications() {
    initDB();
    return JSON.parse(localStorage.getItem(STORAGE_KEYS.APPLICATIONS)) || [];
  },
  getApplicationById(id) {
    const apps = this.getApplications();
    return apps.find(a => a.id === id) || null;
  },
  getApplicationsByUserId(userId) {
    const apps = this.getApplications();
    return apps.filter(a => a.userId === userId);
  },
  hasActiveApplication(userId, petId) {
    const apps = this.getApplications();
    return apps.some(a => a.userId === userId && a.petId === petId && (a.status === 'Menunggu' || a.status === 'Dalam Proses'));
  },
  saveApplication(app) {
    const apps = this.getApplications();
    const idx = apps.findIndex(a => a.id === app.id);
    if (idx >= 0) {
      apps[idx] = app;
    } else {
      apps.unshift(app);
    }
    localStorage.setItem(STORAGE_KEYS.APPLICATIONS, JSON.stringify(apps));
    return app;
  },
  updateApplicationStatus(appId, newStatus, adminNotes = '') {
    const app = this.getApplicationById(appId);
    if (!app) return null;

    app.status = newStatus;
    if (adminNotes) {
      app.adminNotes = adminNotes;
    }
    this.saveApplication(app);

    // Business Logic:
    // If approved -> update corresponding pet to "Sudah Diadopsi"
    if (newStatus === 'Disetujui') {
      this.updatePetStatus(app.petId, 'Sudah Diadopsi');
    } else if (newStatus === 'Ditolak') {
      // Check if there are other approved/in-progress applications for this pet
      const allApps = this.getApplications();
      const hasOtherActive = allApps.some(a => a.petId === app.petId && a.id !== app.id && (a.status === 'Disetujui' || a.status === 'Dalam Proses'));
      if (!hasOtherActive) {
        this.updatePetStatus(app.petId, 'Tersedia');
      }
    } else if (newStatus === 'Dalam Proses') {
      const pet = this.getPetById(app.petId);
      if (pet && pet.status === 'Tersedia') {
        this.updatePetStatus(app.petId, 'Dalam Proses');
      }
    }

    return app;
  },

  // Users
  getUsers() {
    initDB();
    return JSON.parse(localStorage.getItem(STORAGE_KEYS.USERS)) || [];
  },
  getUserByEmail(email) {
    const users = this.getUsers();
    return users.find(u => u.email.toLowerCase() === email.toLowerCase()) || null;
  },
  getUserById(id) {
    const users = this.getUsers();
    return users.find(u => u.id === id) || null;
  },
  saveUser(user) {
    const users = this.getUsers();
    const idx = users.findIndex(u => u.id === user.id);
    if (idx >= 0) {
      users[idx] = user;
    } else {
      users.unshift(user);
    }
    localStorage.setItem(STORAGE_KEYS.USERS, JSON.stringify(users));
    return user;
  },

  // Current User Session
  getCurrentUser() {
    const data = localStorage.getItem(STORAGE_KEYS.CURRENT_USER);
    return data ? JSON.parse(data) : null;
  },
  setCurrentUser(user) {
    if (user) {
      localStorage.setItem(STORAGE_KEYS.CURRENT_USER, JSON.stringify(user));
    } else {
      localStorage.removeItem(STORAGE_KEYS.CURRENT_USER);
    }
  },
  logout() {
    localStorage.removeItem(STORAGE_KEYS.CURRENT_USER);
  },

  // Favorites
  getFavorites() {
    initDB();
    return JSON.parse(localStorage.getItem(STORAGE_KEYS.FAVORITES)) || [];
  },
  isFavorite(petId) {
    const favs = this.getFavorites();
    return favs.includes(petId);
  },
  toggleFavorite(petId) {
    let favs = this.getFavorites();
    if (favs.includes(petId)) {
      favs = favs.filter(id => id !== petId);
    } else {
      favs.push(petId);
    }
    localStorage.setItem(STORAGE_KEYS.FAVORITES, JSON.stringify(favs));
    return favs.includes(petId);
  },

  // Reset database for test / demonstration
  resetDemoData() {
    localStorage.setItem(STORAGE_KEYS.PETS, JSON.stringify(DEFAULT_PETS));
    localStorage.setItem(STORAGE_KEYS.USERS, JSON.stringify(DEFAULT_USERS));
    localStorage.setItem(STORAGE_KEYS.APPLICATIONS, JSON.stringify(DEFAULT_APPLICATIONS));
    localStorage.setItem(STORAGE_KEYS.FAVORITES, JSON.stringify(DEFAULT_FAVORITES));
  }
};

// Initialize right away
initDB();
