<?php
header("Content-Type: application/json");

try {
    $pdo = new PDO("mysql:host=localhost;dbname=digital_wallet", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $input = json_decode(file_get_contents("php://input"), true);
    $userId = $input['userId'] ?? null;

    if (!$userId) {
        echo json_encode(["status" => "error", "message" => "User ID is required."]);
        exit;
    }

    $stmt = $pdo->prepare("SELECT balance FROM users WHERE id = :userId");
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        echo json_encode(["status" => "success", "balance" => $user['balance']]);
    } else {
        echo json_encode(["status" => "error", "message" => "User not found."]);
    }
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
