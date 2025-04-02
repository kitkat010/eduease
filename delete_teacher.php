<?php
session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit();
}

$conn = new mysqli("localhost", "root", "", "teacher_assistant_db");

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit();
}

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    // Delete user from `teacher_details`
    $stmt = $conn->prepare("DELETE FROM teacher_details WHERE user_id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();

    // Delete user from `users`
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id=?");
    $stmt->bind_param("i", $user_id);
    
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "User deleted successfully."]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to delete user."]);
    }

    $stmt->close();
}

$conn->close();
?>
