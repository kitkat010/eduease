<?php
session_start();

// Ensure admin is logged in
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.html");
    exit();
}

$conn = new mysqli("localhost", "root", "", "teacher_assistant_db");

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Get all teachers
$sql = "SELECT u.user_id, u.username, t.first_name, t.last_name, t.department 
        FROM users u
        LEFT JOIN teacher_details t ON u.user_id = t.user_id
        WHERE u.role = 'teacher'";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>AI Logs - Teacher List</title>
    <link rel="stylesheet" href="css/api_key.css">  <!-- Uses the same CSS as login reports -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <h2>Admin</h2>
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="api_key_management.php">API Key</a>
        <a href="login_reports.php">Login Reports</a>
        <a href="database_cleanup.php">Database Cleanup</a>
        <a href="ai_logs.php" class="active">AI Logs & Reports</a>
    </div>

    <div class="main-content">
        <header>
            <h1>AI Logs - Teacher List</h1>
        </header>

        <!-- AI Logs Table -->
        <div class="table-responsive mt-3">
            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Username</th>
                        <th>Teacher Name</th>
                        <th>Department</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><?= htmlspecialchars($row['username']); ?></td>
                            <td><?= htmlspecialchars($row['last_name'] . ', ' . $row['first_name']); ?></td>
                            <td><?= htmlspecialchars($row['department']); ?></td>
                            <td>
                                <a href="ai_logs_dashboard.php?user_id=<?= $row['user_id']; ?>" class="btn btn-primary">
                                    View Logs
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

<?php $conn->close(); ?>
