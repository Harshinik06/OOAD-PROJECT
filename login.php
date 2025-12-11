<?php
header("Content-Type: application/json");

try {
    $pdo = new PDO("mysql:host=localhost;dbname=digital_wallet", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $input = json_decode(file_get_contents("php://input"), true);
    $username = $input['username'] ?? '';
    $password = $input['password'] ?? '';

    if (!$username || !$password) {
        echo json_encode(["status" => "error", "message" => "Username and password are required."]);
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $password === $user['password']) {
        echo json_encode([
            "status" => "success",
            "user_id" => $user['id'],
            "balance" => $user['balance']
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid username or password."]);
    }
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
