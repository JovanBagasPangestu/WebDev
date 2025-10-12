<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}
require_once 'koneksi.php';

// Handle create / update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST['delete_id'])) {
    $name = trim($_POST['name'] ?? '');
    $origin = trim($_POST['origin'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $rating = $_POST['tasteAtlasRating'] !== '' ? (float)$_POST['tasteAtlasRating'] : null;
    $world = trim($_POST['worldRank'] ?? '');
    $image = trim($_POST['image'] ?? '');
    $id = $_POST['id'] ?? null;

    if ($id) {
        $stmt = $pdo->prepare("UPDATE foods SET name=?, origin=?, description=?, tasteatlas_rating=?, world_rank=?, image=? WHERE id=?");
        $stmt->execute([$name, $origin, $desc, $rating, $world, $image, $id]);
        $msg = "Data diperbarui.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO foods (name, origin, description, tasteatlas_rating, world_rank, image) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $origin, $desc, $rating, $world, $image]);
        $msg = "Data ditambahkan.";
    }
    header("Location: admin_foods.php?msg=" . urlencode($msg));
    exit;
}

// Handle delete via POST 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = (int)$_POST['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM foods WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: admin_foods.php?msg=" . urlencode("Data dihapus."));
    exit;
}

// Ambil semua makanan
$foods = $pdo->query("SELECT * FROM foods ORDER BY id DESC")->fetchAll();
$edit = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM foods WHERE id = ?");
    $stmt->execute([$id]);
    $edit = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Kelola Makanan - Admin</title>
<link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">
    <h2>Kelola Makanan</h2>
    <?php if (!empty($_GET['msg'])): ?>
        <p class="alert alert--info"><?= htmlspecialchars($_GET['msg']) ?></p>
    <?php endif; ?>

    <form method="POST" class="form">
        <input type="hidden" name="id" value="<?= htmlspecialchars($edit['id'] ?? '') ?>">
        <div class="form-group">
            <label>Nama</label>
            <input type="text" name="name" required value="<?= htmlspecialchars($edit['name'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Asal</label>
            <input type="text" name="origin" value="<?= htmlspecialchars($edit['origin'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Rating TasteAtlas</label>
            <input type="number" step="0.01" name="tasteAtlasRating" value="<?= htmlspecialchars($edit['tasteatlas_rating'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Peringkat Dunia</label>
            <input type="text" name="worldRank" value="<?= htmlspecialchars($edit['world_rank'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>URL Gambar</label>
            <input type="url" name="image" value="<?= htmlspecialchars($edit['image'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Deskripsi</label>
            <textarea name="description"><?= htmlspecialchars($edit['description'] ?? '') ?></textarea>
        </div>
        <div class="form-buttons">
            <button type="submit" class="btn btn-primary"><?= $edit ? 'Simpan Perubahan' : 'Tambah Makanan' ?></button>
            <?php if ($edit): ?><a href="admin_foods.php" class="btn btn-secondary">Batal</a><?php endif; ?>
        </div>
    </form>

    <h3>Daftar Makanan</h3>
    <table class="table">
        <thead>
            <tr><th>No</th><th>Nama</th><th>Asal</th><th>Rating TA</th><th>Aksi</th></tr>
        </thead>
        <tbody>
            <?php foreach ($foods as $i => $f): ?>
            <tr>
                <td><?= $i+1 ?></td>
                <td><?= htmlspecialchars($f['name']) ?></td>
                <td><?= htmlspecialchars($f['origin']) ?></td>
                <td><?= htmlspecialchars($f['tasteatlas_rating']) ?></td>
                <td>
                    <a href="admin_foods.php?edit=<?= (int)$f['id'] ?>" class="btn btn-primary">Edit</a>

                    <form method="POST" style="display:inline" onsubmit="return confirm('Hapus data ini?')">
                        <input type="hidden" name="delete_id" value="<?= (int)$f['id'] ?>">
                        <button type="submit" class="btn btn-secondary">Hapus</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="admin-actions" style="margin-top: 30px;">
        <a href="dashboard.php" class="btn btn-secondary">Kembali ke Dashboard</a>
        
        <a href="index.php" class="btn btn-primary">Lihat Halaman Publik</a>
    </div>

</div>
</body>
</html>