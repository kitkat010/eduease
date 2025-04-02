<?php
session_start();

// Ensure the user is an admin
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.html");
    exit();
}

// Validate session_id
if (!isset($_GET['session_id']) || !is_numeric($_GET['session_id'])) {
    die("Invalid request.");
}

$session_id = intval($_GET['session_id']);
$teacher_id = isset($_GET['teacher_id']) ? intval($_GET['teacher_id']) : 0;

$conn = new mysqli("localhost", "root", "", "teacher_assistant_db");

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Fetch session name
$session_name = "";
$stmt = $conn->prepare("SELECT session_name FROM chat_logs WHERE session_id = ? LIMIT 1");
$stmt->bind_param("i", $session_id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $session_name = $row['session_name'] ?? "Unknown Session";
}
$stmt->close();

// Fetch chat logs
$sql = "SELECT user_id, session_id, message_type, message, timestamp 
        FROM chat_logs 
        WHERE session_id = ? 
        ORDER BY timestamp ASC";

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
    <title>Chat History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-4">
    <h2 class="mb-4">Chat History for Session: <?= htmlspecialchars($session_name) ?></h2>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>User ID</th>
                <th>Session ID</th>
                <th>Message Type</th>
                <th>Message</th>
                <th>Timestamp</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['user_id']) ?></td>
                    <td><?= htmlspecialchars($row['session_id']) ?></td>
                    <td><?= htmlspecialchars($row['message_type']) ?></td>
                    <td><?= nl2br(htmlspecialchars($row['message'])) ?></td>
                    <td><?= htmlspecialchars($row['timestamp']) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <a href="session_list.php?teacher_id=<?= $_GET['teacher_id'] ?? $teacher_id ?>" class="btn btn-secondary">Back to Sessions</a>

</div>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
