<?php
session_start();

// Ensure the user is an admin
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.html");
    exit();
}

if (!isset($_GET['session_id']) || !isset($_GET['teacher_id'])) {
    die("Invalid request.");
}

$session_id = intval($_GET['session_id']);
$teacher_id = intval($_GET['teacher_id']);

$conn = new mysqli("localhost", "root", "", "teacher_assistant_db");

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Delete all chat logs for the session
$sql = "DELETE FROM chat_logs WHERE session_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $session_id);

if ($stmt->execute()) {
    // Redirect back to session list
    header("Location: session_list.php?teacher_id=" . $teacher_id);
    exit();
} else {
    echo "Error deleting session.";
}

$stmt->close();
$conn->close();
?>
