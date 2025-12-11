<?php
include 'config.php';

$data = json_decode(file_get_contents('php://input'), true);

$user_id = $data['user_id'];

try {
    $stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo json_encode(['status' => 'success', 'balance' => $user['balance']]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'User not found.']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
