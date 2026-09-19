<?php
/**
 * admin_save_menu.php
 * Save or update a menu item. When is_special is set, logs a notification.
 */
include 'config.php';
if (!isset($_SESSION['admin_logged_in'])) { header("Location: admin_login.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header("Location: admin_dashboard.php"); exit; }

// Valid sets for server-side validation
$valid_days  = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
$valid_meals = ['Breakfast','Lunch','Snacks','Dinner'];

$day     = trim($_POST['day_of_week'] ?? '');
$meal    = trim($_POST['meal_type']   ?? '');
$item    = trim($_POST['item_name']   ?? '');
$special = isset($_POST['is_special']) ? 1 : 0;

if (!in_array($day, $valid_days) || !in_array($meal, $valid_meals) || empty($item)) {
    header("Location: admin_dashboard.php?msg=error");
    exit;
}

if (!empty($_POST['id'])) {
    // Update existing entry by ID
    $id = intval($_POST['id']);
    $stmt = $conn->prepare("UPDATE menu SET day_of_week=?, meal_type=?, item_name=?, is_special=? WHERE id=?");
    $stmt->bind_param("sssii", $day, $meal, $item, $special, $id);
    $stmt->execute();
} else {
    // Upsert: if day+meal exists, update it; otherwise insert
    $check = $conn->prepare("SELECT id FROM menu WHERE day_of_week=? AND meal_type=?");
    $check->bind_param("ss", $day, $meal);
    $check->execute();
    $existing = $check->get_result()->fetch_assoc();

    if ($existing) {
        $stmt = $conn->prepare("UPDATE menu SET item_name=?, is_special=? WHERE id=?");
        $stmt->bind_param("sii", $item, $special, $existing['id']);
        $stmt->execute();
    } else {
        $stmt = $conn->prepare("INSERT INTO menu (day_of_week, meal_type, item_name, is_special) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $day, $meal, $item, $special);
        $stmt->execute();
    }
}

// If this is a special/substitution, log a notification for student polling
if ($special) {
    $notif_msg = "📢 Special update: {$day} {$meal} → {$item}";
    $nstmt = $conn->prepare("INSERT INTO notifications_log (message) VALUES (?)");
    $nstmt->bind_param("s", $notif_msg);
    $nstmt->execute();
}

header("Location: admin_dashboard.php?msg=saved");
exit;
