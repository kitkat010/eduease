<?php
session_start(); 

// Ensure admin is logged in
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "teacher_assistant_db");

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    // Activate user by setting status to active
    $sql = "UPDATE users SET status='active' WHERE user_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    
    if ($stmt->execute()) {
        $_SESSION["message"] = "User activated successfully.";
    } else {
        $_SESSION["message"] = "Failed to activate user.";
    }

    $stmt->close();
}

$conn->close();
header("Location: view_deactivated.php"); // Redirect back to the deactivated list
exit();
?>
