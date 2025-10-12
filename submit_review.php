<?php
header('Content-Type: application/json');
require_once 'koneksi.php';

$json_data = file_get_contents('php://input');
$data = json_decode($json_data);

if (
    !isset($data->guest_name) || !isset($data->email) ||
    !isset($data->food_id) || !isset($data->rating)
) {
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap.']);
    exit;
}

$guest_name = trim($data->guest_name);
$email = trim($data->email);
$food_id = (int)$data->food_id;
$rating = (int)$data->rating;
$comment = isset($data->comment) ? trim($data->comment) : '';

if (empty($guest_name) || !filter_var($email, FILTER_VALIDATE_EMAIL) || $food_id <= 0 || $rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Data yang dikirim tidak valid.']);
    exit;
}

try {
    $stmt = $pdo->prepare(
        "INSERT INTO reviews (food_id, guest_name, email, rating, comment) VALUES (?, ?, ?, ?, ?)"
    );
    
    $stmt->execute([$food_id, $guest_name, $email, $rating, $comment]);
    
    echo json_encode(['success' => true, 'message' => 'Terima kasih, ulasan Anda telah disimpan!']);

} catch (PDOException $e) {
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan pada server.']);
}
?>