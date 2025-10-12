document.addEventListener('DOMContentLoaded', function() {
    populateFoodSelect();
    setupEventListeners();
    loadInitialState();
});

function populateFoodSelect() {
    const makananSelect = document.getElementById('makanan');
    if (!makananSelect) return;

    foodsFromServer.forEach(food => {
        const option = document.createElement('option');
        option.value = food.id;
        option.textContent = food.name;
        makananSelect.appendChild(option);
    });
}

async function updateRatingTable() {
    const tableBody = document.querySelector('.table__body');
    if (!tableBody) return;

    try {
        const response = await fetch('rating.php');
        if (!response.ok) {
            throw new Error('Gagal mengambil data peringkat.');
        }
        const sortedFoods = await response.json();

        tableBody.innerHTML = '';

        if (sortedFoods.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="5">Belum ada ulasan dari pengguna.</td></tr>';
            return;
        }
        
        sortedFoods.forEach((food, index) => {
            const row = document.createElement('tr');
            row.className = 'table__row fade-in';
            
            const userRatingDisplay = food.total_reviews > 0 
                ? `${parseFloat(food.average_user_rating).toFixed(2)}/5 (${food.total_reviews} ulasan)` 
                : 'Belum ada rating';
            
            row.innerHTML = `
                <td class="table__cell" data-label="No">${index + 1}</td>
                <td class="table__cell" data-label="Nama Makanan">${food.name}</td>
                <td class="table__cell" data-label="Asal Daerah">${food.origin}</td>
                <td class="table__cell" data-label="Rating">${userRatingDisplay}</td>
                <td class="table__cell" data-label="Peringkat Dunia">${food.world_rank}</td>
            `;
            tableBody.appendChild(row);
        });

    } catch (error) {
        console.error("Error updating table:", error);
        showAlert('Tidak dapat memuat data peringkat terbaru.', 'error');
    }
}

function setupEventListeners() {
    document.getElementById('rating-form')?.addEventListener('submit', handleFormSubmit);
    document.getElementById('user-form')?.addEventListener('submit', handleUsernameSubmit);
    document.querySelector('.toggler-mode')?.addEventListener('click', toggleTheme);
    document.querySelector('.btn-hamburger')?.addEventListener('click', toggleNav);
    
    document.querySelectorAll('.nav__link').forEach(link => {
        link.addEventListener('click', () => {
            document.querySelector('.nav')?.classList.remove('show');
        });
    });
}

async function handleFormSubmit(e) {
    e.preventDefault();
    const form = e.target;
    const submitButton = form.querySelector('button[type="submit"]');
    
    const formData = new FormData(form);
    const userReview = {
        guest_name: formData.get('nama'),
        email: formData.get('email'),
        food_id: parseInt(formData.get('makanan')),
        rating: parseInt(formData.get('rating')),
        comment: formData.get('ulasan') || ''
    };
    
    if (!userReview.guest_name || !userReview.email || !userReview.food_id || !userReview.rating) {
        showAlert('Harap isi semua kolom yang wajib diisi!', 'error');
        return;
    }

    submitButton.disabled = true;
    submitButton.textContent = 'Mengirim...';

    try {
        const response = await fetch('submit_review.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(userReview)
        });

        const result = await response.json();

        if (result.success) {
            showAlert(result.message, 'success');
            form.reset();
            await updateRatingTable();
            document.getElementById('rating-table')?.scrollIntoView({ behavior: 'smooth' });
        } else {
            showAlert(result.message || 'Gagal mengirim ulasan.', 'error');
        }

    } catch (error) {
        console.error("Error submitting review:", error);
        showAlert('Terjadi kesalahan koneksi. Coba lagi nanti.', 'error');
    } finally {
        submitButton.disabled = false;
        submitButton.textContent = 'Kirim Rating';
    }
}

function handleUsernameSubmit(e) {
    e.preventDefault();
    const input = document.getElementById('username-input');
    const username = input.value.trim();
    if (username) {
        localStorage.setItem("username", username);
        updateUserDisplay(username);
        showAlert(`Halo ${username}! Selamat datang.`, 'success');
        input.value = "";
    }
}

function loadInitialState() {
    const theme = localStorage.getItem("theme");
    if (theme === "dark-mode") {
        document.body.classList.add("dark-mode");
        document.querySelector('.toggler-mode').innerText = "🌞";
    }
    const username = localStorage.getItem("username");
    if (username) {
        updateUserDisplay(username);
    }
}

function updateUserDisplay(name) {
    const userDisplay = document.querySelector('.user-display');
    if (userDisplay) {
        userDisplay.textContent = `Hi, ${name}!`;
    }
}

function toggleTheme() {
    document.body.classList.toggle("dark-mode");
    const isDark = document.body.classList.contains("dark-mode");
    this.innerText = isDark ? "🌞" : "🌚";
    localStorage.setItem("theme", isDark ? "dark-mode" : "light-mode");
}

function toggleNav() {
    document.querySelector('.nav')?.classList.toggle('show');
}

function showAlert(message, type = 'info') {
    const existingAlert = document.querySelector('.alert');
    if (existingAlert) existingAlert.remove();
    const alert = document.createElement('div');
    alert.className = `alert alert--${type}`;
    alert.textContent = message;
    const colors = { success: '#28a745', error: '#dc3545', info: '#17a2b8' };
    alert.style.cssText = `
        position: fixed; top: 20px; right: 20px; padding: 15px 20px;
        border-radius: 8px; color: white; font-weight: 600; z-index: 1001;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15); animation: fadeIn 0.3s ease-out;
        background-color: ${colors[type] || colors.info};
    `;
    document.body.appendChild(alert);
    setTimeout(() => alert.remove(), 5000);
}