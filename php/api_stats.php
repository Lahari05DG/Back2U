<?php
/**
 * api_stats.php — JSON API for dashboard statistics.
 *
 * Returns item counts for the authenticated user:
 *   total_items        — all items owned by the user
 *   claimed_items      — distinct claims filed against the user's items
 *   resolved_items     — user's items with status = 'resolved'
 *   active_discussions — user's items with status = 'discussion'
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

// ── Total items ─────────────────────────────────────────────────────────
$stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM items WHERE user_id = ?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$totalItems = (int) $stmt->get_result()->fetch_assoc()['cnt'];
$stmt->close();

// ── Claimed items (distinct claims on user's items) ─────────────────────
$stmt = $conn->prepare(
    "SELECT COUNT(DISTINCT c.id) AS cnt
     FROM claims c
     JOIN items i ON i.id = c.item_id
     WHERE i.user_id = ?"
);
$stmt->bind_param('i', $userId);
$stmt->execute();
$claimedItems = (int) $stmt->get_result()->fetch_assoc()['cnt'];
$stmt->close();

// ── Resolved items ──────────────────────────────────────────────────────
$stmt = $conn->prepare(
    "SELECT COUNT(*) AS cnt FROM items WHERE user_id = ? AND status = 'resolved'"
);
$stmt->bind_param('i', $userId);
$stmt->execute();
$resolvedItems = (int) $stmt->get_result()->fetch_assoc()['cnt'];
$stmt->close();

// ── Active discussions ──────────────────────────────────────────────────
$stmt = $conn->prepare(
    "SELECT COUNT(*) AS cnt FROM items WHERE user_id = ? AND status = 'discussion'"
);
$stmt->bind_param('i', $userId);
$stmt->execute();
$activeDiscussions = (int) $stmt->get_result()->fetch_assoc()['cnt'];
$stmt->close();

// ── Response ────────────────────────────────────────────────────────────
echo json_encode([
    'success'            => true,
    'total_items'        => $totalItems,
    'claimed_items'      => $claimedItems,
    'resolved_items'     => $resolvedItems,
    'active_discussions' => $activeDiscussions,
]);
exit();
