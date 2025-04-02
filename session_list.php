<?php
session_start();

// Ensure the user is an admin
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.html");
    exit();
}

if (!isset($_GET['teacher_id'])) {
    die("Invalid request.");
}

$teacher_id = intval($_GET['teacher_id']);

$conn = new mysqli("localhost", "root", "", "teacher_assistant_db");

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Fetch teacher details from users table
$teacher_sql = "SELECT first_name, last_name FROM teacher_details WHERE user_id = ?";
$stmt = $conn->prepare($teacher_sql);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$teacher_result = $stmt->get_result();
$teacher = $teacher_result->fetch_assoc();

if (!$teacher) {
    die("Teacher not found.");
}

// Fetch unique session list for the teacher
$sql = "SELECT DISTINCT session_id, session_name FROM chat_logs WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-4">
    <h2>Chat Sessions of <?= htmlspecialchars($teacher['last_name']) ?>, <?= htmlspecialchars($teacher['first_name']) ?></h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Session ID</th>
                <th>Session Name</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['session_id']) ?></td>
                    <td><?= htmlspecialchars($row['session_name']) ?></td>
                    <td>
                        <a href="chat_history.php?session_id=<?= $row['session_id'] ?>&teacher_id=<?= $teacher_id ?>" class="btn btn-primary">View Chats</a>
                        <a href="delete_session.php?session_id=<?= $row['session_id'] ?>&teacher_id=<?= $teacher_id ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this session?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <a href="database_cleanup.php" class="btn btn-secondary">Back to Teachers</a>
</div>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
