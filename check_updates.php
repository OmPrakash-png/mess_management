<?php
/**
 * check_updates.php
 * Polling endpoint for real-time notification simulation.
 * Called by JS every 30s — returns the latest menu updated_at timestamp
 * and the most recent notification message (if any in last 5 minutes).
 */
header('Content-Type: application/json');
include 'config.php';

// Latest menu modification timestamp
$menu_ts = $conn->query("SELECT MAX(updated_at) AS ts FROM menu")->fetch_assoc()['ts'] ?? null;

// Latest notification from last 10 minutes
$notif_row = $conn->query(
    "SELECT message, created_at FROM notifications_log 
     WHERE created_at >= DATE_SUB(NOW(), INTERVAL 10 MINUTE)
     ORDER BY created_at DESC LIMIT 1"
)->fetch_assoc();

echo json_encode([
    'latest_update'   => $menu_ts,
    'notification'    => $notif_row ? $notif_row['message']    : null,
    'notif_time'      => $notif_row ? $notif_row['created_at'] : null,
    'server_time'     => date('Y-m-d H:i:s'),
]);
