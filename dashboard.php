<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];
$message = isset($_GET['message']) ? htmlspecialchars($_GET['message']) : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Selamat datang, <?= htmlspecialchars($username) ?>!</h2>
        <?php if (!empty($message)): ?>
            <p class="alert alert--info"><?= $message ?></p>
        <?php endif; ?>
        
        <div class="admin-actions" style="margin: 30px 0; display: flex; gap: 15px;">
            <a href="index.php" class="btn btn-secondary">Lihat Halaman Publik</a>
            <a href="admin_foods.php" class="btn btn-primary">Kelola Makanan</a>
        </div>

        <h3>Daftar Makanan</h3>
        <?php
        require_once 'koneksi.php';
        $foods = $pdo->query("SELECT id, name, origin FROM foods ORDER BY id DESC")->fetchAll();
        if (!$foods):
            echo '<p>Belum ada data.</p>';
        else: ?>
            <table class="table">
                <thead><tr><th>No</th><th>Nama</th><th>Asal</th><th>Aksi</th></tr></thead>
                <tbody>
                <?php foreach ($foods as $i => $f): ?>
                    <tr>
                        <td><?= $i+1 ?></td>
                        <td><?= htmlspecialchars($f['name']) ?></td>
                        <td><?= htmlspecialchars($f['origin']) ?></td>
                        <td>
                            <a href="admin_foods.php?edit=<?= $f['id'] ?>" class="btn btn-primary">Edit</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>