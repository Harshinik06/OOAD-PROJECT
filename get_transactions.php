<?php
// Include config file
require_once "config.php";
header("Content-Type: application/json");

if($_SERVER["REQUEST_METHOD"] == "GET"){
    $user_id = $_GET['user_id'];
    
    $sql = "SELECT amount, description, type, transaction_date FROM transactions WHERE user_id = ? ORDER BY transaction_date DESC";
    
    if($stmt = mysqli_prepare($conn, $sql)){
        mysqli_stmt_bind_param($stmt, "i", $param_user_id);
        $param_user_id = $user_id;
        
        if(mysqli_stmt_execute($stmt)){
            $result = mysqli_stmt_get_result($stmt);
            
            $transactions = [];
            while($row = mysqli_fetch_assoc($result)){
                $transactions[] = $row;
            }
            
            echo json_encode(["success" => true, "transactions" => $transactions]);
        } else {
            echo json_encode(["success" => false, "message" => "Error fetching transactions"]);
        }
    }
    
    mysqli_stmt_close($stmt);
}
?>