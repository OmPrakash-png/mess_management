<?php
/**
 * submit_preference.php
 * Records a student's Veg/Non-Veg food preference vote.
 * Uses a session_token (passed from JS localStorage) to prevent duplicate entries.
 */
include 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$preference = trim($_POST['preference'] ?? '');
$token      = trim($_POST['session_token'] ?? '');

$valid_prefs = ['veg', 'non-veg'];

if (!in_array($preference, $valid_prefs) || empty($token)) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

// Check if this token has already voted
$check = $conn->prepare("SELECT id FROM food_preferences WHERE session_token = ?");
$check->bind_param("s", $token);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'already_voted']);
    exit;
}

// Record the vote
$stmt = $conn->prepare("INSERT INTO food_preferences (preference, session_token) VALUES (?, ?)");
$stmt->bind_param("ss", $preference, $token);
$stmt->execute();

// Return fresh counts
$counts = $conn->query("SELECT preference, COUNT(*) AS cnt FROM food_preferences GROUP BY preference")->fetch_all(MYSQLI_ASSOC);
$result = ['veg' => 0, 'non-veg' => 0];
foreach ($counts as $c) { $result[$c['preference']] = (int)$c['cnt']; }
$total = $result['veg'] + $result['non-veg'];

echo json_encode([
    'success'    => true,
    'veg'        => $result['veg'],
    'non_veg'    => $result['non-veg'],
    'total'      => $total,
    'veg_pct'    => $total > 0 ? round($result['veg'] / $total * 100) : 0,
    'nonveg_pct' => $total > 0 ? round($result['non-veg'] / $total * 100) : 0,
]);
