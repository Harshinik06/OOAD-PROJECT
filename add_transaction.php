<?php
include 'config.php';

$data = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $data['user_id'];
    $amount = $data['amount'];
    $description = $data['description'];
    $type = $data['type'];

    try {
        $stmt = $pdo->prepare("INSERT INTO transactions (user_id, amount, description, type) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $amount, $description, $type]);

        // Update user balance
        if ($type === 'Add Funds') {
            $stmt = $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
        } else {
            $stmt = $pdo->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
        }
        $stmt->execute([$amount, $user_id]);

        echo json_encode(['status' => 'success', 'message' => 'Transaction added successfully']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>
