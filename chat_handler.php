<?php
session_start();
$conn = new mysqli("localhost", "root", "", "teacher_assistant_db");

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION["user_id"])) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION["user_id"];

// Fetch chat sessions
$sessions_sql = "SELECT session_id, session_name, created_at FROM chat_sessions WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sessions_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$sessions_result = $stmt->get_result();
$sessions = $sessions_result->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Chatbot</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body { display: flex; height: 100vh; }
        .sidebar { width: 220px; background: #2c3e50; color: white; padding: 15px; }
        .sidebar a { color: white; display: block; padding: 10px; text-decoration: none; }
        .sidebar a:hover { background: #34495e; }
        .session-sidebar { width: 280px; background: #ecf0f1; padding: 15px; border-right: 1px solid #bdc3c7; }
        .chat-container { flex-grow: 1; display: flex; flex-direction: column; }
        .chat-header { padding: 15px; border-bottom: 2px solid #bdc3c7; display: flex; justify-content: space-between; align-items: center; background: #f8f9fa; }
        .chat-messages { flex-grow: 1; overflow-y: auto; padding: 15px; background: #ffffff; }
        .chat-input { padding: 15px; border-top: 2px solid #bdc3c7; background: #f8f9fa; }
    </style>
</head>
<body>
    <!-- Main Sidebar -->
    <div class="sidebar">
        <h4 class="text-center">Menu</h4>
        <a href="chatbot.php">Chatbot</a>
        <a href="file_upload.php">File Upload</a>
    </div>
    
    <!-- Sessions Sidebar -->
    <div class="session-sidebar">
        <button id="newChatBtn" class="btn btn-primary w-100 mb-3">+ New Chat</button>
        <h5>Previous Chats</h5>
        <ul class="list-group" id="sessionList">
            <?php foreach ($sessions as $session): ?>
                <li class="list-group-item session-item d-flex justify-content-between align-items-center" data-id="<?= $session['session_id'] ?>">
                    <span class="session-name"> <?= htmlspecialchars($session['session_name']) ?> </span>
                    <button class="btn btn-sm btn-secondary renameBtn">Rename</button>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    
    <!-- Chat Container -->
    <div class="chat-container">
        <div class="chat-header">
            <h4>Chatbot</h4>
            <img src="profile.jpg" alt="Profile" width="40" height="40" class="rounded-circle">
        </div>
        <div class="chat-messages" id="chatWindow">
            <p><strong>Bot:</strong> Hello! How can I assist you today?</p>
        </div>
        <div class="chat-input d-flex">
            <input type="text" id="chatInput" class="form-control me-2" placeholder="Type a message...">
            <button class="btn btn-primary" id="sendBtn">Send</button>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#newChatBtn').click(function() {
                $.post('chat_handler.php', { action: 'create' }, function(response) {
                    location.reload();
                });
            });

            $('.renameBtn').click(function() {
                let listItem = $(this).closest('.session-item');
                let sessionId = listItem.data('id');
                let newName = prompt("Enter new session name:", listItem.find('.session-name').text());
                if (newName) {
                    $.post('chat_handler.php', { action: 'rename', session_id: sessionId, new_name: newName }, function(response) {
                        location.reload();
                    });
                }
            });

            $('.session-item').click(function() {
                let sessionId = $(this).data('id');
                loadChat(sessionId);
            });

            function loadChat(sessionId) {
                $.post('chat_handler.php', { action: 'load', session_id: sessionId }, function(response) {
                    $('#chatWindow').html(response);
                });
            }

            $('#sendBtn').click(function() {
                let message = $('#chatInput').val();
                let sessionId = $('.session-item.active').data('id');
                if (message.trim() !== "" && sessionId) {
                    $.post('chat_handler.php', { action: 'send', session_id: sessionId, message: message }, function(response) {
                        $('#chatWindow').append(response);
                        $('#chatInput').val("");
                    });
                }
            });
        });
    </script>
</body>
</html>
