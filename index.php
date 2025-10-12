<?php
session_start();
require_once 'koneksi.php';

$foods_for_js = $pdo->query("SELECT id, name FROM foods ORDER BY name ASC")->fetchAll();

$initial_ratings_query = "
    SELECT 
        f.name, f.origin, f.world_rank,
        COALESCE(AVG(r.rating), 0) AS average_user_rating, 
        COUNT(r.id) AS total_reviews
    FROM foods f
    LEFT JOIN reviews r ON f.id = r.food_id
    GROUP BY f.id
    ORDER BY average_user_rating DESC, total_reviews DESC;
";
$initial_ratings = $pdo->query($initial_ratings_query)->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Rating Makanan Khas Indonesia</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <div class="header__controls">
                <button class="btn-hamburger" aria-label="Toggle Navigation">☰</button>
                <div>
                    <button class="toggler-mode" aria-label="Toggle Dark Mode">🌚</button>
                    <span class="user-display">Guest</span>

                    <?php if (isset($_SESSION['username'])): ?>
                        
                        <a href="admin_foods.php" class="btn btn-primary">Kelola Halaman</a>
                        
                        <a href="logout.php" class="btn btn-secondary">Logout</a>

                    <?php endif; ?>
                </div>
            </div>
            
            <h1 class="header__title">Rating Makanan Khas Indonesia</h1>
            <p class="header__tagline">Beri rating pada makanan tradisional favoritmu!</p>
            
            <form class="user-form" id="user-form">
                <input type="text" id="username-input" class="input" placeholder="Masukkan nama Anda..." required>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </form>
        </header>

        <nav class="nav">
            <ul class="nav__list">
                <li><a href="#food-cards-container" class="nav__link">Daftar Makanan</a></li>
                <li><a href="#form-rating" class="nav__link">✍️ Beri Rating</a></li>
                <li><a href="#rating-table" class="nav__link">📊 Peringkat</a></li>
            </ul>
        </nav>

        <main>
            <section id="food-cards-container">
                <?php
                $foods = $pdo->query("SELECT id, name, origin, description, tasteatlas_rating, world_rank, image FROM foods ORDER BY id DESC")->fetchAll();
                ?>
                <h2>Jelajahi Kekayaan Rasa Indonesia</h2>
                <?php if (!$foods): ?>
                    <p>Belum ada data makanan.</p>
                <?php else: ?>
                    <?php foreach ($foods as $food): ?>
                        <article class="card fade-in" id="food-<?= $food['id'] ?>">
                            <img src="<?= htmlspecialchars($food['image']) ?>" alt="<?= htmlspecialchars($food['name']) ?>">
                            <div class="card__content">
                                <h2><?= htmlspecialchars($food['name']) ?></h2>
                                <p class="card__meta">Asal: <strong><?= htmlspecialchars($food['origin']) ?></strong></p>
                                <p><?= nl2br(htmlspecialchars($food['description'])) ?></p>
                                <p class="card__meta">Rating TasteAtlas: <?= htmlspecialchars($food['tasteatlas_rating']) ?>/5 | Peringkat Dunia: <?= htmlspecialchars($food['world_rank']) ?></p>
                                <?php if (isset($_SESSION['username'])): ?>
                                    <p><a href="admin_foods.php?edit=<?= $food['id'] ?>" class="btn btn-primary">Edit</a></p>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>

            <section id="form-rating" class="card-container">
                <h2>Beri Ulasanmu!</h2>
                <p>Bagikan pendapatmu tentang makanan khas Indonesia.</p>
                <form class="form" id="rating-form">
                    <div class="form-group">
                        <label for="nama">Nama Anda</label>
                        <input type="text" id="nama" name="nama" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email Anda</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="makanan">Pilih Makanan</label>
                        <select id="makanan" name="makanan" required>
                            <option value="">-- Pilih salah satu --</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Rating Anda</label>
                        <div class="radio-group">
                            <input type="radio" id="rating1" name="rating" value="1" required><label for="rating1">1</label>
                            <input type="radio" id="rating2" name="rating" value="2"><label for="rating2">2</label>
                            <input type="radio" id="rating3" name="rating" value="3"><label for="rating3">3</label>
                            <input type="radio" id="rating4" name="rating" value="4"><label for="rating4">4</label>
                            <input type="radio" id="rating5" name="rating" value="5"><label for="rating5">5</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="ulasan">Ulasan (Opsional)</label>
                        <textarea id="ulasan" name="ulasan" rows="4"></textarea>
                    </div>
                    <div class="form-buttons">
                        <button type="submit" class="btn btn-primary">Kirim Rating</button>
                        <button type="reset" class="btn btn-secondary">Reset</button>
                    </div>
                </form>
            </section>

            <section id="rating-table" class="card-container">
                <h2>Peringkat Makanan Berdasarkan Ulasan Pengguna</h2>
                <div class="table-wrapper">
                    <table class="table">
                        <thead class="table__head">
                            <tr>
                                <th class="table__header">No</th>
                                <th class="table__header">Nama Makanan</th>
                                <th class="table__header">Asal Daerah</th>
                                <th class="table__header">Rating Pengguna</th>
                                <th class="table__header">Peringkat Dunia (TasteAtlas)</th>
                            </tr>
                        </thead>
                        <tbody class="table__body">
                            <?php if (empty($initial_ratings)): ?>
                                <tr><td colspan="5">Belum ada ulasan dari pengguna.</td></tr>
                            <?php else: ?>
                                <?php foreach ($initial_ratings as $index => $item): ?>
                                    <tr class="table__row">
                                        <td class="table__cell" data-label="No"><?= $index + 1 ?></td>
                                        <td class="table__cell" data-label="Nama Makanan"><?= htmlspecialchars($item['name']) ?></td>
                                        <td class="table__cell" data-label="Asal Daerah"><?= htmlspecialchars($item['origin']) ?></td>
                                        <td class="table__cell" data-label="Rating">
                                            <?= $item['total_reviews'] > 0 ? number_format($item['average_user_rating'], 2) . '/5 (' . $item['total_reviews'] . ' ulasan)' : 'Belum ada rating' ?>
                                        </td>
                                        <td class="table__cell" data-label="Peringkat Dunia"><?= htmlspecialchars($item['world_rank']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>

        <footer class="footer">
            <p>Referensi: <a href="https://www.tasteatlas.com/indonesia" target="_blank" class="footer__link">TasteAtlas - Indonesian Food</a></p>
            <p>&copy; 2025 - Situs Rating Makanan Indonesia</p>
        </footer>
    </div>
    
    <script>
        const foodsFromServer = <?= json_encode($foods_for_js) ?>;
    </script>
    <script src="script.js"></script>
</body>
</html>