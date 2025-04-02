<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    die(json_encode(['error' => 'Unauthorized']));
}

$user_id = $_SESSION['user_id'];
$conn = new mysqli("localhost", "root", "", "teacher_assistant_db");

if ($conn->connect_error) {
    die(json_encode(['error' => 'Database connection failed']));
}

$action = $_GET['action'] ?? ''; // Determine action type

if ($action === 'create') {
    // Create a new chat session
    $session_name = "New Chat " . date("Y-m-d H:i:s");
    $stmt = $conn->prepare("INSERT INTO chat_sessions (user_id, session_name) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $session_name);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'session_id' => $stmt->insert_id, 'session_name' => $session_name]);
    } else {
        echo json_encode(['error' => 'Failed to create session']);
    }
    $stmt->close();
}

if ($action === 'fetch') {
    // Fetch all chat sessions for the user
    $stmt = $conn->prepare("SELECT session_id, session_name, created_at FROM chat_sessions WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $sessions = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode($sessions);
    $stmt->close();
}

$conn->close();
