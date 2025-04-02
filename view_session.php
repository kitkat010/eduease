<?php
session_start();

// Ensure the user is an admin
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.html");
    exit();
}

if (!isset($_GET['session_id'])) {
    die("Invalid request.");
}

$session_id = intval($_GET['session_id']);

$conn = new mysqli("localhost", "root", "", "teacher_assistant_db");

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Fetch session details
$session_sql = "SELECT session_name FROM chat_logs WHERE session_id = ? LIMIT 1";
$stmt = $conn->prepare($session_sql);
$stmt->bind_param("i", $session_id);
$stmt->execute();
$session_result = $stmt->get_result();
$session = $session_result->fetch_assoc();

if (!$session) {
    die("Session not found.");
}

// Fetch chat messages in the session
$sql = "SELECT sender, message, timestamp FROM chat_logs WHERE session_id = ? ORDER BY timestamp ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $session_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Session</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-4">
    <h2>Chat Session: <?= htmlspecialchars($session['session_name']) ?></h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Sender</th>
                <th>Message</th>
                <th>Timestamp</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['sender']) ?></td>
                    <td><?= htmlspecialchars($row['message']) ?></td>
                    <td><?= htmlspecialchars($row['timestamp']) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <a href="chat_history.php?teacher_id=<?= $_SESSION['user_id'] ?>" class="btn btn-secondary">Back to Sessions</a>
</div>

</body>
</html>

<?php $conn->close(); ?>
