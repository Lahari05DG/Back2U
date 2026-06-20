<?php
/**
 * api_chat.php — JSON API for real-time AJAX chat on item pages.
 *
 * GET  ?item_id=X&after=Y  → fetch messages with id > Y for item X
 * POST item_id, message    → insert a new message for item X
 *
 * All responses are JSON with Content-Type: application/json.
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
$method = $_SERVER['REQUEST_METHOD'];

// ── GET: Fetch messages ─────────────────────────────────────────────────
if ($method === 'GET') {

    $itemId = isset($_GET['item_id']) ? (int) $_GET['item_id'] : 0;
    $after  = isset($_GET['after'])   ? (int) $_GET['after']   : 0;

    if ($itemId <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing or invalid item_id.']);
        exit();
    }

    $stmt = $conn->prepare(
        "SELECT m.id, m.sender_id, u.name AS sender_name, m.message, m.created_at
         FROM messages m
         JOIN users u ON u.id = m.sender_id
         WHERE m.item_id = ? AND m.id > ?
         ORDER BY m.id ASC"
    );
    $stmt->bind_param('ii', $itemId, $after);
    $stmt->execute();
    $result = $stmt->get_result();

    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = [
            'id'          => (int) $row['id'],
            'sender_name' => $row['sender_name'],
            'message'     => $row['message'],
            'created_at'  => $row['created_at'],
            'is_mine'     => ((int) $row['sender_id'] === $userId),
        ];
    }
    $stmt->close();

    echo json_encode(['success' => true, 'messages' => $messages]);
    exit();
}

// ── POST: Send a message ────────────────────────────────────────────────
if ($method === 'POST') {

    $itemId  = isset($_POST['item_id']) ? (int) $_POST['item_id'] : 0;
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';

    if ($itemId <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing or invalid item_id.']);
        exit();
    }

    if ($message === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Message cannot be empty.']);
        exit();
    }

    // Verify the item exists
    $checkStmt = $conn->prepare("SELECT id FROM items WHERE id = ?");
    $checkStmt->bind_param('i', $itemId);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows === 0) {
        $checkStmt->close();
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Item not found.']);
        exit();
    }
    $checkStmt->close();

    // Insert the message
    $stmt = $conn->prepare(
        "INSERT INTO messages (item_id, sender_id, message, created_at) VALUES (?, ?, ?, NOW())"
    );
    $stmt->bind_param('iis', $itemId, $userId, $message);

    if ($stmt->execute()) {
        $newId = (int) $stmt->insert_id;
        $stmt->close();
        echo json_encode(['success' => true, 'id' => $newId]);
    } else {
        $stmt->close();
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to send message.']);
    }
    exit();
}

// ── Unsupported method ──────────────────────────────────────────────────
http_response_code(405);
echo json_encode(['success' => false, 'error' => 'Method not allowed.']);
exit();
