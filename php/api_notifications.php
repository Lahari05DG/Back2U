<?php
/**
 * api_notifications.php — JSON API for notification badge counts.
 *
 * Returns:
 *   new_claims   — claims on the user's unresolved items
 *   new_messages — messages (not from the user) on items the user is involved with
 *   total        — sum of the above
 */

header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── Auth check ──────────────────────────────────────────────────────────
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized. Please log in.']);
    exit();
}

require_once __DIR__ . '/db.php';

$userId = (int) $_SESSION['user_id'];

// ── New claims: all claims on user's unresolved items ───────────────────
$stmt = $conn->prepare(
    "SELECT COUNT(*) AS cnt
     FROM claims c
     JOIN items i ON i.id = c.item_id
     WHERE i.user_id = ? AND i.status != 'resolved'"
);
$stmt->bind_param('i', $userId);
$stmt->execute();
$newClaims = (int) $stmt->get_result()->fetch_assoc()['cnt'];
$stmt->close();

// ── New messages: messages (not sent by user) on items user is involved with
//    "Involved" = user owns the item OR user has filed a claim on the item
$stmt = $conn->prepare(
    "SELECT COUNT(*) AS cnt
     FROM messages m
     WHERE m.sender_id != ?
       AND (
           m.item_id IN (SELECT id FROM items WHERE user_id = ?)
           OR
           m.item_id IN (SELECT item_id FROM claims WHERE claimer_id = ?)
       )"
);
$stmt->bind_param('iii', $userId, $userId, $userId);
$stmt->execute();
$newMessages = (int) $stmt->get_result()->fetch_assoc()['cnt'];
$stmt->close();

// ── Response ────────────────────────────────────────────────────────────
$total = $newClaims + $newMessages;

echo json_encode([
    'success'      => true,
    'new_claims'   => $newClaims,
    'new_messages' => $newMessages,
    'total'        => $total,
]);
exit();
