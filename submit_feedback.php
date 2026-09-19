<?php
/**
 * submit_feedback.php
 * Handles student feedback form submission with basic server-side validation.
 */
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

// Sanitize & validate
$name    = trim($_POST['student_name'] ?? '');
$day     = trim($_POST['day_of_week']  ?? '');
$meal    = trim($_POST['meal_type']    ?? '');
$rating  = intval($_POST['rating']     ?? 0);
$comment = trim($_POST['comment']      ?? '');

$valid_days  = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
$valid_meals = ['Breakfast','Lunch','Snacks','Dinner'];

// Validate required fields
if (!in_array($day, $valid_days) || !in_array($meal, $valid_meals) || $rating < 1 || $rating > 5) {
    header("Location: index.php?feedback=error");
    exit;
}

$name = empty($name) ? 'Anonymous' : htmlspecialchars($name, ENT_QUOTES, 'UTF-8');

$stmt = $conn->prepare(
    "INSERT INTO feedback (meal_type, day_of_week, student_name, rating, comment) VALUES (?, ?, ?, ?, ?)"
);
$stmt->bind_param("sssis", $meal, $day, $name, $rating, $comment);
$stmt->execute();

header("Location: index.php?feedback=success");
exit;
