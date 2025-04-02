<?php
session_start();

header("Content-Type: application/json"); // Ensure JSON response

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    echo json_encode(["success" => false, "message" => "Unauthorized access"]);
    exit();
}

$conn = new mysqli("localhost", "root", "", "teacher_assistant_db");

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit();
}

// Check if `id` is received via POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = $_POST['id'] ?? null;

    if (!$user_id) {
        echo json_encode(["success" => false, "message" => "No user ID received"]);
        exit();
    }

    // Debugging: Output received ID
    error_log("Received ID: " . $user_id);

    // Prepare and execute SQL query
    $stmt = $conn->prepare("UPDATE users SET status='inactive' WHERE user_id=?");
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "User deactivated successfully."]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to update database"]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
