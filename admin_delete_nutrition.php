<?php
/**
 * admin_delete_nutrition.php — Delete a nutrition record securely.
 */
include 'config.php';
if (!isset($_SESSION['admin_logged_in'])) { header("Location: admin_login.php"); exit; }
$id = intval($_GET['id'] ?? 0);
if ($id > 0) {
    $stmt = $conn->prepare("DELETE FROM nutrition_info WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}
header("Location: admin_nutrition.php?msg=deleted");
exit;
