<?php
header('Content-Type: application/json');
require_once 'koneksi.php';

try {
    $query = "
        SELECT 
            f.id, 
            f.name, 
            f.origin, 
            f.world_rank,
            COALESCE(AVG(r.rating), 0) AS average_user_rating, 
            COUNT(r.id) AS total_reviews
        FROM 
            foods f
        LEFT JOIN 
            reviews r ON f.id = r.food_id
        GROUP BY 
            f.id
        ORDER BY 
            average_user_rating DESC, total_reviews DESC;
    ";

    $stmt = $pdo->query($query);
    $results = $stmt->fetchAll();

    echo json_encode($results);

} catch (PDOException $e) {
    error_log($e->getMessage());
    echo json_encode([]);
}
?>