const foods = [
    {
        id: 1, name: "Rawon", origin: "Jawa Timur",
        description: "Sup daging sapi kaya rempah dengan bumbu kluwek yang memberinya warna hitam khas dan rasa gurih yang mendalam.",
        tasteAtlasRating: 4.62, worldRank: "#8 (2025)",
        image: "https://www.tasteatlas.com/images/dishes/aadf8e1a901242fb864992cf56531a6e.jpg?mw=1300"
    },
    {
        id: 2, name: "Pempek", origin: "Palembang, Sumatra Selatan",
        description: "Kue ikan kenyal yang digoreng garing, disajikan dengan kuah cuka (cuko) yang pedas, manis, dan asam.",
        tasteAtlasRating: 4.54, worldRank: "#23 (2025)",
        image: "https://www.tasteatlas.com/Images/Dishes/1d28edb5b603496e86c20e9855495b3d.jpg?mw=1300"
    },
    {
        id: 3, name: "Nasi Goreng Ayam", origin: "Seluruh Indonesia",
        description: "Nasi yang digoreng dengan bumbu khas, potongan ayam, telur, dan sayuran, seringkali disajikan dengan kerupuk.",
        tasteAtlasRating: 4.52, worldRank: "#33 (2025)",
        image: "https://www.tasteatlas.com/images/dishes/8267f641538448af93221623a7fc6d3d.jpg?mw=1300"
    },
    {
        id: 4, name: "Gulai", origin: "Pulau Sumatra",
        description: "Hidangan kaya santan seperti kari dengan daging atau sayuran, memiliki cita rasa pedas dan gurih dari rempah-rempah.",
        tasteAtlasRating: 4.51, worldRank: "#44 (2025)",
        image: "https://www.tasteatlas.com/Images/Dishes/f21c3cf6047f436ba8bf2a2589f8c39e.jpg?mw=1300"
    },
    {
        id: 5, name: "Rendang", origin: "Sumatra Barat",
        description: "Daging sapi yang dimasak perlahan dalam santan dan bumbu rempah hingga kering, menghasilkan rasa yang kompleks.",
        tasteAtlasRating: 4.47, worldRank: "#67 (2025)",
        image: "https://www.tasteatlas.com/Images/Dishes/6c6899bba0494ed38532126b79deb639.jpg?mw=1300"
    }
];

let userReviews = JSON.parse(localStorage.getItem('userReviews')) || [];

function initializeFoodData() {
    foods.forEach(food => {
        const reviewsForFood = userReviews.filter(review => review.foodId === food.id);
        food.userRatings = reviewsForFood;
    });
}

document.addEventListener('DOMContentLoaded', function() {
    initializeFoodData();
    renderFoodCards();
    populateFoodSelect();
    setupEventListeners();
    loadInitialState();
    updateRatingTable();

    if (userReviews.length === 0) {
        addSampleReviews();
    }
});


// === FUNGSI-FUNGSI RENDER TAMPILAN ===

function renderFoodCards() {
    const container = document.getElementById('food-cards-container');
    if (!container) return;
    
    let content = '<h2>Jelajahi Kekayaan Rasa Indonesia</h2>';
    foods.forEach(food => {
        content += `
            <article class="card fade-in" id="food-${food.id}">
                <img src="${food.image}" alt="${food.name}">
                <div class="card__content">
                    <h2>${food.name}</h2>
                    <p class="card__meta">Asal: <strong>${food.origin}</strong></p>
                    <p>${food.description}</p>
                    <p class="card__meta">Rating TasteAtlas: ${food.tasteAtlasRating}/5 | Peringkat Dunia: ${food.worldRank}</p>
                </div>
            </article>
        `;
    });
    container.innerHTML = content;
}

function populateFoodSelect() {
    const makananSelect = document.getElementById('makanan');
    if (!makananSelect) return;

    foods.forEach(food => {
        const option = document.createElement('option');
        option.value = food.id;
        option.textContent = food.name;
        makananSelect.appendChild(option);
    });
}

function updateRatingTable() {
    const tableBody = document.querySelector('.table__body');
    if (!tableBody) return;
    
    tableBody.innerHTML = '';
    
    // Hitung rating rata-rata untuk setiap makanan
    foods.forEach(food => {
        food.averageUserRating = calculateAverageRating(food.userRatings);
        food.totalReviews = food.userRatings.length;
    });
    
    // Urutkan berdasarkan rating pengguna (descending)
    const sortedFoods = [...foods].sort((a, b) => (b.averageUserRating || 0) - (a.averageUserRating || 0));
    
    sortedFoods.forEach((food, index) => {
        const row = document.createElement('tr');
        row.className = 'table__row fade-in';
        
        const userRatingDisplay = food.averageUserRating 
            ? `${food.averageUserRating.toFixed(2)}/5 (${food.totalReviews} ulasan)` 
            : 'Belum ada rating';
        
        row.innerHTML = `
            <td class="table__cell" data-label="No">${index + 1}</td>
            <td class="table__cell" data-label="Nama Makanan">${food.name}</td>
            <td class="table__cell" data-label="Asal Daerah">${food.origin}</td>
            <td class="table__cell" data-label="Rating">${userRatingDisplay}</td>
            <td class="table__cell" data-label="Peringkat Dunia">${food.worldRank}</td>
        `;
        tableBody.appendChild(row);
    });
}

// === FUNGSI-FUNGSI EVENT HANDLER ===

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

function handleFormSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const userReview = {
        id: Date.now(),
        name: formData.get('nama'),
        email: formData.get('email'),
        foodId: parseInt(formData.get('makanan')),
        rating: parseInt(formData.get('rating')),
        review: formData.get('ulasan') || '',
        date: new Date().toISOString()
    };
    
    if (!userReview.name || !userReview.email || !userReview.foodId || !userReview.rating) {
        showAlert('Harap isi semua kolom yang wajib diisi!', 'error');
        return;
    }
    
    const selectedFood = foods.find(food => food.id === userReview.foodId);
    if (!selectedFood) {
        showAlert('Makanan tidak valid!', 'error');
        return;
    }

    selectedFood.userRatings.push(userReview);
    userReviews.push(userReview);
    localStorage.setItem('userReviews', JSON.stringify(userReviews));
    
    updateRatingTable();
    e.target.reset();
    showAlert(`Terima kasih ${userReview.name}! Ulasan Anda untuk ${selectedFood.name} telah disimpan.`, 'success');

    document.getElementById('rating-table')?.scrollIntoView({ behavior: 'smooth' });
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
    // tema
    const theme = localStorage.getItem("theme");
    if (theme === "dark-mode") {
        document.body.classList.add("dark-mode");
        document.querySelector('.toggler-mode').innerText = "🌞";
    }

    // nama pengguna
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

function calculateAverageRating(ratings) {
    if (!ratings || ratings.length === 0) return 0;
    const sum = ratings.reduce((total, item) => total + item.rating, 0);
    return sum / ratings.length;
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

function addSampleReviews() {
    const sampleReviews = [
        { id: 1, name: "Ahmad", foodId: 1, rating: 5, review: "Rawonnya autentik!" },
        { id: 2, name: "Sari", foodId: 2, rating: 4, review: "Cukonya pas." },
        { id: 3, name: "Budi", foodId: 3, rating: 5, review: "Nasi goreng favorit!" }
    ];
    
    sampleReviews.forEach(review => {
        const food = foods.find(f => f.id === review.foodId);
        if (food) food.userRatings.push(review);
        userReviews.push(review);
    });
    
    localStorage.setItem('userReviews', JSON.stringify(userReviews));
    updateRatingTable();
}