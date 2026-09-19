<?php
/**
 * admin_save_nutrition.php
 * Add or update a nutrition record. Admin only.
 */
include 'config.php';
if (!isset($_SESSION['admin_logged_in'])) { header("Location: admin_login.php"); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header("Location: admin_nutrition.php"); exit; }

$id           = intval($_POST['id'] ?? 0);
$food_name    = trim($_POST['food_name']     ?? '');
$meal_type    = trim($_POST['meal_type']     ?? 'Any');
$calories     = intval($_POST['calories']    ?? 0);
$protein      = floatval($_POST['protein']   ?? 0);
$carbs        = floatval($_POST['carbohydrates'] ?? 0);
$fat          = floatval($_POST['fat']       ?? 0);
$fiber        = floatval($_POST['fiber']     ?? 0);
$serving      = trim($_POST['serving_size']  ?? '1 serving');
$description  = trim($_POST['description']   ?? '');

if (empty($food_name)) {
    header("Location: admin_nutrition.php?msg=error");
    exit;
}

if ($id > 0) {
    $stmt = $conn->prepare(
        "UPDATE nutrition_info SET food_name=?, meal_type=?, calories=?, protein=?,
         carbohydrates=?, fat=?, fiber=?, serving_size=?, description=? WHERE id=?"
    );
    $stmt->bind_param("ssiiddddsi", $food_name, $meal_type, $calories, $protein,
                      $carbs, $fat, $fiber, $serving, $description, $id);
} else {
    $stmt = $conn->prepare(
        "INSERT INTO nutrition_info (food_name, meal_type, calories, protein, carbohydrates, fat, fiber, serving_size, description)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("ssiidddss", $food_name, $meal_type, $calories, $protein,
                      $carbs, $fat, $fiber, $serving, $description);
}
$stmt->execute();

header("Location: admin_nutrition.php?msg=saved");
exit;
