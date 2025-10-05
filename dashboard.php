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
        <?php include 'index.php'; ?>
    </div>
</body>
</html>