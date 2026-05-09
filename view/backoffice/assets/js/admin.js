function viewFullProfilePHP(userId) {
    
    document.getElementById('view-bio').innerText = "Chargement...";
    document.getElementById('view-linkedin').style.display = 'none';
    document.getElementById('no-linkedin').style.display = 'inline';
    
    
    document.getElementById('user-view-modal-php').style.display = 'flex';

    
    fetch('get_user_profile.php?id=' + userId)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
                return;
            }
            
            document.getElementById('view-fullname').innerText = (data.first_name || '') + ' ' + (data.last_name || '');
            document.getElementById('view-email').innerText = data.email || 'N/A';
            document.getElementById('view-phone').innerText = data.phone || 'N/A';
            document.getElementById('view-city').innerText = data.city || 'N/A';
            document.getElementById('view-dob').innerText = data.date_of_birth || 'N/A';
            document.getElementById('view-role').innerText = data.role || 'N/A';
            document.getElementById('view-status').innerText = data.status || 'N/A';
            document.getElementById('view-created-at').innerText = data.created_at || 'N/A';
            document.getElementById('view-last-login').innerText = data.last_login || 'Jamais';

            
            document.getElementById('view-bio').innerText = data.bio || 'Aucune biographie disponible.';
            document.getElementById('view-profession').innerText = data.profession || 'Non spécifiée';
            document.getElementById('view-skills').innerText = data.competences || 'Aucune compétence listée.';
            
            if (data.linkedin) {
                document.getElementById('view-linkedin').href = data.linkedin;
                document.getElementById('view-linkedin').style.display = 'inline';
                document.getElementById('no-linkedin').style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Erreur AJAX:', error);
            document.getElementById('view-bio').innerText = "Erreur lors du chargement.";
        });
}

function openAddModalPHP() {
    document.getElementById('php-id').value = '';
    document.getElementById('php-first_name').value = '';
    document.getElementById('php-last_name').value = '';
    document.getElementById('php-email').value = '';
    document.getElementById('php-username').value = '';
    document.getElementById('php-phone').value = '';
    document.getElementById('php-city').value = '';
    document.getElementById('php-date_of_birth').value = '';
    document.getElementById('php-password').value = '';
    document.getElementById('php-role').value = 'Entrepreneur';
    document.getElementById('php-status').value = 'Actif';
    document.getElementById('php-bio').value = '';
    document.getElementById('php-linkedin').value = '';
    document.getElementById('php-pays').value = 'Tunisie';
    document.getElementById('php-competences').value = '';
    document.getElementById('modal-title-php').innerText = "Ajouter un utilisateur";
    document.getElementById('user-modal-php').style.display = 'flex';
    
    clearAdminErrors();
}

function editUserPHP(user) {
    document.getElementById('php-id').value = user.id;
    document.getElementById('php-first_name').value = user.first_name;
    document.getElementById('php-last_name').value = user.last_name;
    document.getElementById('php-email').value = user.email;
    document.getElementById('php-username').value = user.username || '';
    document.getElementById('php-phone').value = user.phone || '';
    document.getElementById('php-city').value = user.city || '';
    document.getElementById('php-date_of_birth').value = user.date_of_birth || '';
    document.getElementById('php-password').value = ''; 
    document.getElementById('php-role').value = user.role;
    document.getElementById('php-status').value = user.status;

    
    fetch('get_user_profile.php?id=' + user.id)
        .then(response => response.json())
        .then(data => {
            if (!data.error) {
                document.getElementById('php-bio').value = data.bio || '';
                document.getElementById('php-linkedin').value = data.linkedin || '';
                document.getElementById('php-pays').value = data.pays || 'Tunisie';
                document.getElementById('php-competences').value = data.competences || '';
            }
        });

    document.getElementById('modal-title-php').innerText = "Modifier l'utilisateur";
    document.getElementById('user-modal-php').style.display = 'flex';
    
    clearAdminErrors();
}

function confirmDeletePHP(id, name) {
    document.getElementById('php-delete-id').value = id;
    document.getElementById('delete-name-php').innerText = name;
    document.getElementById('delete-modal-php').style.display = 'flex';
}

function confirmReactivatePHP(id, name) {
    document.getElementById('php-reactivate-id').value = id;
    document.getElementById('reactivate-name-php').innerText = name;
    document.getElementById('reactivate-modal-php').style.display = 'flex';
}

function closeModalPHP(id) {
    document.getElementById(id).style.display = 'none';
}

function filterTableCustom() {
    let input = document.getElementById('search-input').value.toLowerCase();
    let table = document.getElementById('table-body');
    let tr = table.getElementsByTagName('tr');
    for (let i = 0; i < tr.length; i++) {
        let tdName = tr[i].getElementsByTagName('td')[1];
        let tdEmail = tr[i].getElementsByTagName('td')[2];
        if (tdName || tdEmail) {
            let nameValue = tdName.textContent || tdName.innerText;
            let emailValue = tdEmail.textContent || tdEmail.innerText;
            if (nameValue.toLowerCase().indexOf(input) > -1 || emailValue.toLowerCase().indexOf(input) > -1) {
                tr[i].style.display = "";
            } else {
                tr[i].style.display = "none";
            }
        }       
    }
}

function clearAdminErrors() {
    const errorIds = [
        'error-php-first_name', 
        'error-php-last_name', 
        'error-php-email', 
        'error-php-username',
        'error-php-phone', 
        'error-php-city', 
        'error-php-date_of_birth',
        'error-php-password'
    ];
    errorIds.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.textContent = "";
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const adminForm = document.querySelector('#user-modal-php form');
    if (adminForm) {
        adminForm.addEventListener('submit', (e) => {
            let isValid = true;
            clearAdminErrors();
            
            
            const nameRegex = /^[A-Za-zÀ-ÿ\s'-]+$/;
            const phoneRegex = /^[\d\s+]{8,15}$/;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            // 1. Prénom
            const firstName = document.getElementById('php-first_name');
            const errFirstName = document.getElementById('error-php-first_name');
            if (!firstName.value.trim()) {
                if(errFirstName) errFirstName.textContent = "Le prénom est requis.";
                isValid = false;
            } else if (!nameRegex.test(firstName.value.trim())) {
                if(errFirstName) errFirstName.textContent = "Le prénom ne doit contenir que des lettres.";
                isValid = false;
            } else if (firstName.value.trim().length < 2) {
                if(errFirstName) errFirstName.textContent = "Le prénom doit faire au moins 2 caractères.";
                isValid = false;
            }

            // 2. Nom
            const lastName = document.getElementById('php-last_name');
            const errLastName = document.getElementById('error-php-last_name');
            if (!lastName.value.trim()) {
                if(errLastName) errLastName.textContent = "Le nom est requis.";
                isValid = false;
            } else if (!nameRegex.test(lastName.value.trim())) {
                if(errLastName) errLastName.textContent = "Le nom ne doit contenir que des lettres.";
                isValid = false;
            } else if (lastName.value.trim().length < 2) {
                if(errLastName) errLastName.textContent = "Le nom doit faire au moins 2 caractères.";
                isValid = false;
            }

            // 3. Username
            const username = document.getElementById('php-username');
            const errUsername = document.getElementById('error-php-username');
            if (!username.value.trim()) {
                if(errUsername) errUsername.textContent = "Le nom d'utilisateur est requis.";
                isValid = false;
            } else if (username.value.trim().length < 3) {
                if(errUsername) errUsername.textContent = "Le nom d'utilisateur doit faire au moins 3 caractères.";
                isValid = false;
            }

            // 4. Email
            const email = document.getElementById('php-email');
            const errEmail = document.getElementById('error-php-email');
            if (!email.value.trim()) {
                if(errEmail) errEmail.textContent = "L'adresse e-mail est requise.";
                isValid = false;
            } else if (!emailRegex.test(email.value.trim())) {
                if(errEmail) errEmail.textContent = "Format d'e-mail invalide.";
                isValid = false;
            }

            // 5. Téléphone (Optional but validated if present)
            const phone = document.getElementById('php-phone');
            const errPhone = document.getElementById('error-php-phone');
            if (phone.value.trim() && !phoneRegex.test(phone.value.trim())) {
                if(errPhone) errPhone.textContent = "Le téléphone doit contenir entre 8 et 15 chiffres.";
                isValid = false;
            }

            // 6. City (Optional but validated if present)
            const city = document.getElementById('php-city');
            const errCity = document.getElementById('error-php-city');
            if (city.value.trim() && city.value.trim().length < 2) {
                if(errCity) errCity.textContent = "La ville doit faire au moins 2 caractères.";
                isValid = false;
            }

            // 7. Date of birth (Optional but cannot be in future)
            const dob = document.getElementById('php-date_of_birth');
            const errDob = document.getElementById('error-php-date_of_birth');
            if (dob.value) {
                const selectedDate = new Date(dob.value);
                const today = new Date();
                if (selectedDate > today) {
                    if(errDob) errDob.textContent = "La date de naissance ne peut pas être dans le futur.";
                    isValid = false;
                }
            }

            // 8. Password (Required on Add, Optional on Edit)
            const isAdd = document.getElementById('php-id').value === '';
            const pwd = document.getElementById('php-password');
            const errPwd = document.getElementById('error-php-password');
            if (isAdd && !pwd.value) {
                if(errPwd) errPwd.textContent = "Le mot de passe est requis pour un nouvel utilisateur.";
                isValid = false;
            } else if (pwd.value && pwd.value.length < 8) {
                if(errPwd) errPwd.textContent = "Le mot de passe doit faire au moins 8 caractères.";
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
            }
        });
    }
});

function showToast(message, type) {
    const container = document.getElementById('toast-container');
    if (!container) return;
    
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    
    const iconClass = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    
    toast.innerHTML = `<i class="fa ${iconClass}"></i> <span>${message}</span>`;
    
    container.appendChild(toast);
    
    // Auto remove after 4 seconds
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(40px)';
        toast.style.transition = 'all 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}
function initCharts(activeStats, entrepreneurStats, existingCharts = {}) {
    console.log("Initializing charts with data:", { activeStats, entrepreneurStats });
    
    try {
        const revenueCtx = document.getElementById('revenueChart');
        const entrepreneurCtx = document.getElementById('entrepreneurChart');
        
        if (!revenueCtx || !entrepreneurCtx) {
            console.error("Canvas elements not found");
            return existingCharts;
        }

        // Destroy existing charts if they exist to avoid overlaps
        if (existingCharts.revenue) existingCharts.revenue.destroy();
        if (existingCharts.entrepreneur) existingCharts.entrepreneur.destroy();

        const labels = activeStats && activeStats.length > 0 
            ? activeStats.map(d => formatMonth(d.month)) 
            : ["Pas de données"];
        const activeCounts = activeStats && activeStats.length > 0 
            ? activeStats.map(d => d.count) 
            : [0];
        
        // Helper to format months
        function formatMonth(m) {
            if (!m) return 'N/A';
            const [year, month] = m.split('-');
            const date = new Date(year, month - 1);
            return date.toLocaleDateString('fr-FR', { month: 'short', year: 'numeric' });
        }

        const revenueChart = new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Nombre de personnes actives',
                    data: activeCounts,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#10b981',
                    pointRadius: 6,
                    pointHoverRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: true, position: 'bottom' },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, precision: 0 }
                    }
                }
            }
        });

        const entLabels = entrepreneurStats && entrepreneurStats.length > 0 
            ? entrepreneurStats.map(d => formatMonth(d.month)) 
            : ["Pas de données"];
        const entCounts = entrepreneurStats && entrepreneurStats.length > 0 
            ? entrepreneurStats.map(d => d.count) 
            : [0];

        const entrepreneurChart = new Chart(entrepreneurCtx, {
            type: 'line',
            data: {
                labels: entLabels,
                datasets: [{
                    label: 'Entrepreneurs Actifs',
                    data: entCounts,
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#2563eb',
                    pointRadius: 6,
                    pointHoverRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: true, position: 'bottom' }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, precision: 0 }
                    }
                }
            }
        });

        return { revenue: revenueChart, entrepreneur: entrepreneurChart };

    } catch (error) {
        console.error("Error initializing charts:", error);
        return existingCharts;
    }
}
