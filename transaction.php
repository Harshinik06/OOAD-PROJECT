<?php
header("Content-Type: application/json");

try {
    // Database connection
    $pdo = new PDO("mysql:host=localhost;dbname=digital_wallet", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Retrieve and decode JSON input
    $input = json_decode(file_get_contents("php://input"), true);

    // Validate input
    $userId = $input['userId'] ?? null;
    $amount = $input['amount'] ?? null;
    $type = $input['type'] ?? null;
    $card_number = $input['card_number'] ?? null;

    if (!$userId || !$amount || !$type || (($type === "credit-card" || $type === "debit-card") && (!$card_number || strlen($card_number) !== 16))) {
        echo json_encode(["status" => "error", "message" => "Invalid transaction details."]);
        exit;
    }

    // Fetch user balance
    $stmt = $pdo->prepare("SELECT balance FROM users WHERE id = :userId");
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(["status" => "error", "message" => "User not found."]);
        exit;
    }

    $currentBalance = (float)$user['balance'];

    // Check for sufficient balance
    if ($amount > $currentBalance) {
        echo json_encode(["status" => "error", "message" => "Insufficient balance."]);
        exit;
    }

    // Deduct amount from balance
    $newBalance = $currentBalance - $amount;
    $stmt = $pdo->prepare("UPDATE users SET balance = :newBalance WHERE id = :userId");
    $stmt->bindParam(':newBalance', $newBalance, PDO::PARAM_STR);
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmt->execute();

    // Insert transaction record
    $stmt = $pdo->prepare("INSERT INTO transactions (user_id, amount, type, card_number) VALUES (:userId, :amount, :type, :card_number)");
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmt->bindParam(':amount', $amount, PDO::PARAM_STR);
    $stmt->bindParam(':type', $type, PDO::PARAM_STR);
    $stmt->bindParam(':card_number', $card_number, PDO::PARAM_STR);
    $stmt->execute();

    // Respond with success
    echo json_encode(["status" => "success", "new_balance" => $newBalance]);
} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    echo json_encode(["status" => "error", "message" => "A server error occurred."]);
    exit;
} catch (Exception $e) {
    error_log("General Error: " . $e->getMessage());
    echo json_encode(["status" => "error", "message" => "A server error occurred."]);
    exit;
}
?>
