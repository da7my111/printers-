<?php
require_once '../includes/auth.php';

// Make sure user is logged in
if (!isLoggedIn()) {
    die(json_encode(['success' => false, 'message' => 'You must be logged in']));
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(['success' => false, 'message' => 'Invalid request method']));
}

// Get printer ID from URL query string
$printer_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Validate printer ID
if ($printer_id <= 0) {
    die(json_encode(['success' => false, 'message' => 'Invalid printer ID']));
}

// Begin transaction
$conn->begin_transaction();

try {
    // Check if printer exists
    $check = $conn->prepare("SELECT id FROM printers WHERE id = ?");
    $check->bind_param("i", $printer_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows === 0) {
        die(json_encode(['success' => false, 'message' => 'Printer not found']));
    }
    $check->close();

    // Delete printer
    $stmt = $conn->prepare("DELETE FROM printers WHERE id = ?");
    $stmt->bind_param("i", $printer_id);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        throw new Exception('No rows were deleted');
    }

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Printer deleted successfully']);
} catch (Exception $e) {
    $conn->rollback();
    die(json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]));
} finally {
    if (isset($stmt)) $stmt->close();
    $conn->close();
}
?>
