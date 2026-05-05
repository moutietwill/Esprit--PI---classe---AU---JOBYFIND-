// Extracted JavaScript from evenements.html and admin.html

// DOM Elements
const modalOverlays = document.querySelectorAll('.modal-overlay');
const modals = document.querySelectorAll('.modal');
const eventCards = document.querySelectorAll('.event-card');
const filterChips = document.querySelectorAll('.filter-chip');
const searchInput = document.querySelector('.search-bar input');
const searchBtn = document.querySelector('.search-bar .search-btn');
const sortSelect = document.querySelector('.sort-select');
const paginationBtns = document.querySelectorAll('.page-btn');
const toastContainer = document.getElementById('toast-container') || createToastContainer();

// Create toast container if it doesn't exist
function createToastContainer() {
  const container = document.createElement('div');
  container.id = 'toast-container';
  document.body.appendChild(container);
  return container;
}

// Modal Functions
function openModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.classList.add('open');
    document.body.style.overflow = 'hidden';
  }
}

function closeModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.classList.remove('open');
    document.body.style.overflow = '';
  }
}

// Close modal when clicking overlay
modalOverlays.forEach(overlay => {
  overlay.addEventListener('click', (e) => {
    if (e.target === overlay) {
      overlay.classList.remove('open');
      document.body.style.overflow = '';
    }
  });
});

// Close modal with close button
document.addEventListener('click', (e) => {
  if (e.target.classList.contains('modal-close')) {
    const modal = e.target.closest('.modal-overlay');
    if (modal) {
      modal.classList.remove('open');
      document.body.style.overflow = '';
    }
  }
});

// Event Card Click Handler
eventCards.forEach(card => {
  card.addEventListener('click', () => {
    openModal('detail-modal');
  });
});

// Filter Chips
filterChips.forEach(chip => {
  chip.addEventListener('click', () => {
    // Remove active class from all chips
    filterChips.forEach(c => c.classList.remove('active'));
    // Add active class to clicked chip
    chip.classList.add('active');

    const category = chip.textContent.toLowerCase().trim();
    filterEvents(category);
  });
});

// Search Functionality
let searchTimeout;
searchInput?.addEventListener('input', (e) => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    const query = e.target.value.toLowerCase().trim();
    searchEvents(query);
  }, 300);
});

searchBtn?.addEventListener('click', () => {
  const query = searchInput.value.toLowerCase().trim();
  searchEvents(query);
});

// Sort Functionality
sortSelect?.addEventListener('change', (e) => {
  const sortBy = e.target.value;
  sortEvents(sortBy);
});

// Pagination
paginationBtns.forEach(btn => {
  btn.addEventListener('click', () => {
    // Remove active class from all buttons
    paginationBtns.forEach(b => b.classList.remove('active'));
    // Add active class to clicked button
    btn.classList.add('active');

    const page = parseInt(btn.textContent);
    goToPage(page);
  });
});

// Event Filtering Functions
function filterEvents(category) {
  const events = document.querySelectorAll('.event-card');

  events.forEach(event => {
    const eventCategory = event.dataset.category?.toLowerCase() || '';
    if (category === 'all' || eventCategory.includes(category)) {
      event.style.display = 'flex';
    } else {
      event.style.display = 'none';
    }
  });

  updateEmptyState();
}

function searchEvents(query) {
  const events = document.querySelectorAll('.event-card');

  events.forEach(event => {
    const title = event.querySelector('.event-card-title')?.textContent.toLowerCase() || '';
    const desc = event.querySelector('.event-card-desc')?.textContent.toLowerCase() || '';
    const organizer = event.querySelector('.event-organizer')?.textContent.toLowerCase() || '';

    if (title.includes(query) || desc.includes(query) || organizer.includes(query)) {
      event.style.display = 'flex';
    } else {
      event.style.display = 'none';
    }
  });

  updateEmptyState();
}

function sortEvents(sortBy) {
  const eventsGrid = document.querySelector('.events-grid');
  const events = Array.from(document.querySelectorAll('.event-card'));

  events.sort((a, b) => {
    switch (sortBy) {
      case 'date':
        const dateA = new Date(a.dataset.date);
        const dateB = new Date(b.dataset.date);
        return dateA - dateB;
      case 'name':
        const nameA = a.querySelector('.event-card-title').textContent.toLowerCase();
        const nameB = b.querySelector('.event-card-title').textContent.toLowerCase();
        return nameA.localeCompare(nameB);
      case 'capacity':
        const capA = parseInt(a.dataset.capacity || 0);
        const capB = parseInt(b.dataset.capacity || 0);
        return capB - capA; // Higher capacity first
      default:
        return 0;
    }
  });

  // Re-append sorted events
  events.forEach(event => {
    eventsGrid.appendChild(event);
  });
}

function goToPage(page) {
  // Implement pagination logic here
  console.log('Going to page:', page);
}

function updateEmptyState() {
  const events = document.querySelectorAll('.event-card[style*="display: flex"], .event-card:not([style*="display"])');
  const emptyState = document.querySelector('.empty-state');

  if (events.length === 0 && emptyState) {
    emptyState.style.display = 'block';
  } else if (emptyState) {
    emptyState.style.display = 'none';
  }
}

// Toast Notifications
function showToast(message, type = 'success') {
  const toast = document.createElement('div');
  toast.className = `toast ${type}`;
  toast.innerHTML = `
    <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
    <span>${message}</span>
  `;

  toastContainer.appendChild(toast);

  // Remove toast after 3 seconds
  setTimeout(() => {
    toast.remove();
  }, 3000);
}

// Form Validation
function validateForm(formId) {
  const form = document.getElementById(formId);
  if (!form) return false;

  const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
  let isValid = true;

  inputs.forEach(input => {
    if (!input.value.trim()) {
      input.classList.add('error');
      isValid = false;
    } else {
      input.classList.remove('error');
    }
  });

  return isValid;
}

// Registration Modal
function openRegistrationModal(eventId) {
  // Populate event summary
  const eventCard = document.querySelector(`[data-event-id="${eventId}"]`);
  if (eventCard) {
    const title = eventCard.querySelector('.event-card-title').textContent;
    const date = eventCard.querySelector('.event-card-date .month').textContent + ' ' +
                 eventCard.querySelector('.event-card-date .day').textContent + ', ' +
                 eventCard.querySelector('.event-card-date .year').textContent;
    const location = eventCard.querySelector('.event-card-meta span:nth-child(2)')?.textContent || '';

    const summary = document.querySelector('.event-summary');
    if (summary) {
      summary.querySelector('.es-title').textContent = title;
      summary.querySelector('.es-meta').innerHTML = `
        <span><i class="fas fa-calendar"></i> ${date}</span>
        <span><i class="fas fa-map-marker-alt"></i> ${location}</span>
      `;
    }
  }

  openModal('register-modal');
}

// Handle Registration Form
document.addEventListener('submit', (e) => {
  if (e.target.id === 'register-form') {
    e.preventDefault();

    if (validateForm('register-form')) {
      // Simulate registration
      showToast('Registration successful! Check your email for confirmation.', 'success');
      closeModal('register-modal');
      e.target.reset();
    } else {
      showToast('Please fill in all required fields.', 'error');
    }
  }
});

// Admin Functions
function openEditModal(type, id) {
  // Populate form based on type (event/user) and id
  const modal = document.getElementById(`${type}-edit-modal`);
  if (modal) {
    // Populate form fields here
    openModal(`${type}-edit-modal`);
  }
}

function deleteItem(type, id) {
  if (confirm(`Are you sure you want to delete this ${type}?`)) {
    // Simulate deletion
    showToast(`${type.charAt(0).toUpperCase() + type.slice(1)} deleted successfully.`, 'success');
    // Remove row from table
    const row = document.querySelector(`[data-${type}-id="${id}"]`);
    if (row) {
      row.remove();
    }
  }
}

// Table Actions
document.addEventListener('click', (e) => {
  if (e.target.classList.contains('action-btn')) {
    const action = e.target.dataset.action;
    const type = e.target.dataset.type;
    const id = e.target.dataset.id;

    switch (action) {
      case 'edit':
        openEditModal(type, id);
        break;
      case 'delete':
        deleteItem(type, id);
        break;
      case 'view':
        // Handle view action
        break;
    }
  }
});

// Bulk Actions
function toggleAllCheckboxes(checkbox) {
  const checkboxes = document.querySelectorAll('tbody input[type="checkbox"]');
  checkboxes.forEach(cb => cb.checked = checkbox.checked);
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
  // Initialize any components that need it
  updateEmptyState();

  // Add loading states to buttons
  document.addEventListener('click', (e) => {
    if (e.target.classList.contains('btn-primary') || e.target.classList.contains('btn')) {
      e.target.classList.add('loading');
      setTimeout(() => {
        e.target.classList.remove('loading');
      }, 1000);
    }
  });
});

// Utility Functions
function debounce(func, wait) {
  let timeout;
  return function executedFunction(...args) {
    const later = => {
      clearTimeout(timeout);
      func(...args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
}

function formatDate(date) {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
}

function formatTime(time) {
  return new Date(`1970-01-01T${time}`).toLocaleTimeString('en-US', {
    hour: 'numeric',
    minute: '2-digit',
    hour12: true
  });
}

// Export functions for global use
window.EventApp = {
  openModal,
  closeModal,
  showToast,
  openRegistrationModal,
  openEditModal,
  deleteItem,
  toggleAllCheckboxes
};
    });

    // 3. Applique le style actif au bouton cliqué
    element.style.background = "#fff";
    element.style.color = "#2d79ff";
    element.style.boxShadow = "0 2px 5px rgba(0,0,0,0.1)";

    // 4. Met à jour la valeur cachée pour le formulaire
    document.getElementById('selected-role').value = role;
    console.log("Rôle sélectionné : " + role);
}

// Fonction pour afficher/masquer le mot de passe
function togglePass() {
    const passwordField = document.getElementById('login-pass');
    const eyeIcon = document.getElementById('eye-icon');

    if (passwordField.type === "password") {
        passwordField.type = "text";
        eyeIcon.classList.remove('fa-eye');
        eyeIcon.classList.add('fa-eye-slash');
    } else {
        passwordField.type = "password";
        eyeIcon.classList.remove('fa-eye-slash');
        eyeIcon.classList.add('fa-eye');
    }
}

// Fonction de connexion
function handleLogin() {
    const email = document.getElementById('login-email').value;
    const role = document.getElementById('selected-role').value;
    
    if(email === "") {
        alert("Veuillez entrer votre email");
    } else {
        alert("Tentative de connexion en tant que " + role + " avec l'adresse : " + email);
    }
}
