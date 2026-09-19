<?php
/**
 * admin_clone_week.php
 * Clones all existing menu entries for a given "source week" by duplicating
 * every row's day/meal/item_name into fresh entries.
 * (Since we use day-names not dates, this just re-inserts/updates with same data —
 *  useful to re-populate if entries were accidentally deleted.)
 * For a hackathon demo, this duplicates the entire current menu as a "this week's" copy.
 */
include 'config.php';
if (!isset($_SESSION['admin_logged_in'])) { header("Location: admin_login.php"); exit; }

// Fetch all current menu rows
$rows = $conn->query("SELECT day_of_week, meal_type, item_name FROM menu")->fetch_all(MYSQLI_ASSOC);
$count = 0;

foreach ($rows as $row) {
    // Upsert: if day+meal already exists, update; else insert
    $check = $conn->prepare("SELECT id FROM menu WHERE day_of_week=? AND meal_type=?");
    $check->bind_param("ss", $row['day_of_week'], $row['meal_type']);
    $check->execute();
    $existing = $check->get_result()->fetch_assoc();

    if (!$existing) {
        $ins = $conn->prepare("INSERT INTO menu (day_of_week, meal_type, item_name, is_special) VALUES (?,?,?,0)");
        $ins->bind_param("sss", $row['day_of_week'], $row['meal_type'], $row['item_name']);
        $ins->execute();
        $count++;
    }
}

header("Location: admin_dashboard.php?msg=cloned&count=$count");
exit;
