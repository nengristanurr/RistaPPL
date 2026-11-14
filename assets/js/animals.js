// PawMate - Animals Catalog Filter & Search Handler

document.addEventListener('DOMContentLoaded', () => {
  const searchInput = document.getElementById('filter-search');
  const speciesSelect = document.getElementById('filter-species');
  const genderSelect = document.getElementById('filter-gender');
  const ageSelect = document.getElementById('filter-age');
  const statusSelect = document.getElementById('filter-status');
  const resetBtn = document.getElementById('btn-reset-filters');
  const tagButtons = document.querySelectorAll('.filter-tag-btn');

  // Read URL search params
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.has('q') && searchInput) {
    searchInput.value = urlParams.get('q');
  }
  if (urlParams.has('species') && speciesSelect) {
    speciesSelect.value = urlParams.get('species');
  }
  if (urlParams.has('gender') && genderSelect) {
    genderSelect.value = urlParams.get('gender');
  }
  if (urlParams.has('status') && statusSelect) {
    statusSelect.value = urlParams.get('status');
  }

  // Filter Event Listeners
  if (searchInput) searchInput.addEventListener('input', applyFilters);
  if (speciesSelect) speciesSelect.addEventListener('change', applyFilters);
  if (genderSelect) genderSelect.addEventListener('change', applyFilters);
  if (ageSelect) ageSelect.addEventListener('change', applyFilters);
  if (statusSelect) statusSelect.addEventListener('change', applyFilters);

  if (resetBtn) {
    resetBtn.addEventListener('click', resetAllFilters);
  }

  // Tag quick buttons
  tagButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      const cat = btn.getAttribute('data-category');
      if (cat === 'all') {
        resetAllFilters();
      } else if (cat === 'Anjing' || cat === 'Kucing') {
        if (speciesSelect) speciesSelect.value = cat;
        applyFilters();
      } else if (cat === 'Tersedia') {
        if (statusSelect) statusSelect.value = 'Tersedia';
        applyFilters();
      }
    });
  });

  // Initial render
  applyFilters();
});

function applyFilters() {
  const searchInput = document.getElementById('filter-search');
  const speciesSelect = document.getElementById('filter-species');
  const genderSelect = document.getElementById('filter-gender');
  const ageSelect = document.getElementById('filter-age');
  const statusSelect = document.getElementById('filter-status');

  const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
  const speciesVal = speciesSelect ? speciesSelect.value : 'all';
  const genderVal = genderSelect ? genderSelect.value : 'all';
  const ageVal = ageSelect ? ageSelect.value : 'all';
  const statusVal = statusSelect ? statusSelect.value : 'all';

  const allPets = DB.getPets();

  const filtered = allPets.filter(pet => {
    // Search query match
    if (query) {
      const nameMatch = pet.name.toLowerCase().includes(query);
      const breedMatch = (pet.breed || '').toLowerCase().includes(query);
      const descMatch = (pet.description || '').toLowerCase().includes(query);
      const locMatch = (pet.location || '').toLowerCase().includes(query);
      if (!nameMatch && !breedMatch && !descMatch && !locMatch) return false;
    }

    // Species
    if (speciesVal !== 'all' && pet.species !== speciesVal) {
      return false;
    }

    // Gender
    if (genderVal !== 'all' && pet.gender !== genderVal) {
      return false;
    }

    // Availability Status
    if (statusVal !== 'all' && pet.status !== statusVal) {
      return false;
    }

    // Age parsing
    if (ageVal !== 'all') {
      const ageStr = pet.age.toLowerCase();
      const isMonths = ageStr.includes('bulan');
      let years = 0;
      if (isMonths) {
        years = parseFloat(ageStr) / 12;
      } else {
        years = parseFloat(ageStr) || 1;
      }

      if (ageVal === 'kitten-puppy' && years >= 1) return false;
      if (ageVal === 'adult' && (years < 1 || years > 3)) return false;
      if (ageVal === 'senior' && years <= 3) return false;
    }

    return true;
  });

  renderCatalog(filtered);
}

function renderCatalog(pets) {
  const container = document.getElementById('animals-catalog-container');
  const emptyState = document.getElementById('animals-empty-state');
  const countText = document.getElementById('results-count-text');

  if (!container) return;

  if (countText) {
    countText.innerHTML = `Menampilkan <span style="color:var(--primary); font-weight:800;">${pets.length}</span> hewan menggemaskan`;
  }

  if (pets.length === 0) {
    container.innerHTML = '';
    if (emptyState) emptyState.style.display = 'block';
  } else {
    if (emptyState) emptyState.style.display = 'none';
    container.innerHTML = pets.map(pet => createPetCardHTML(pet)).join('');
  }
}

function resetAllFilters() {
  const searchInput = document.getElementById('filter-search');
  const speciesSelect = document.getElementById('filter-species');
  const genderSelect = document.getElementById('filter-gender');
  const ageSelect = document.getElementById('filter-age');
  const statusSelect = document.getElementById('filter-status');

  if (searchInput) searchInput.value = '';
  if (speciesSelect) speciesSelect.value = 'all';
  if (genderSelect) genderSelect.value = 'all';
  if (ageSelect) ageSelect.value = 'all';
  if (statusSelect) statusSelect.value = 'all';

  // Clear query params in URL without refresh
  if (window.history.pushState) {
    const newurl = window.location.protocol + "//" + window.location.host + window.location.pathname;
    window.history.pushState({ path: newurl }, '', newurl);
  }

  applyFilters();
  showToast('Filter Direset', 'Semua kriteria filter telah dikembalikan ke awal.', 'info');
}
