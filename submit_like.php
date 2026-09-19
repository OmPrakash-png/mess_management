<?php
/**
 * submit_like.php
 * Records a "like" for a food item.
 * Uses session_token to prevent the same browser from liking twice.
 */
include 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$food_name = trim($_POST['food_name']     ?? '');
$meal_type = trim($_POST['meal_type']     ?? 'Any');
$token     = trim($_POST['session_token'] ?? '');

if (empty($food_name) || empty($token)) {
    echo json_encode(['success' => false, 'message' => 'Missing data']);
    exit;
}

// Check duplicate vote
$check = $conn->prepare(
    "SELECT id FROM food_like_votes WHERE food_name=? AND meal_type=? AND session_token=?"
);
$check->bind_param("sss", $food_name, $meal_type, $token);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'already_liked']);
    exit;
}

// Record the vote
$ins = $conn->prepare(
    "INSERT INTO food_like_votes (food_name, meal_type, session_token) VALUES (?,?,?)"
);
$ins->bind_param("sss", $food_name, $meal_type, $token);
$ins->execute();

// Upsert likes count in food_likes table
$conn->prepare(
    "INSERT INTO food_likes (food_name, meal_type, likes_count, total_rating, rating_count)
     VALUES (?, ?, 1, 0, 0)
     ON DUPLICATE KEY UPDATE likes_count = likes_count + 1"
)->bind_param("ss", $food_name, $meal_type);
// Use proper execute
$upsert = $conn->prepare(
    "INSERT INTO food_likes (food_name, meal_type, likes_count, total_rating, rating_count)
     VALUES (?, ?, 1, 0, 0)
     ON DUPLICATE KEY UPDATE likes_count = likes_count + 1"
);
$upsert->bind_param("ss", $food_name, $meal_type);
$upsert->execute();

// Return new likes count
$row = $conn->prepare("SELECT likes_count FROM food_likes WHERE food_name=? AND meal_type=?");
$row->bind_param("ss", $food_name, $meal_type);
$row->execute();
$r = $row->get_result()->fetch_assoc();

echo json_encode([
    'success'     => true,
    'likes_count' => (int)($r['likes_count'] ?? 1),
    'food_name'   => $food_name,
]);
