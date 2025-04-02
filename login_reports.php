<?php
session_start();
$conn = new mysqli("localhost", "root", "", "teacher_assistant_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT 
            login_logs.id AS log_id, 
            users.username, 
            teacher_details.department, 
            login_logs.login_time 
        FROM login_logs
        INNER JOIN users ON login_logs.user_id = users.user_id
        INNER JOIN teacher_details ON users.user_id = teacher_details.user_id
        WHERE users.role = 'teacher'
        ORDER BY login_logs.login_time DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Teacher Login Reports</title>
    <link rel="stylesheet" href="css/api_key.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <h2>Admin</h2>
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="api_key_management.php">API Key</a>
        <a href="login_reports.php" class="active">Login Reports</a>
        <a href="database_cleanup.php">Database Cleanup</a>
        <a href="ai_logs.php">AI Logs & Reports</a>
    </div>

    <div class="main-content">
        <header>
            <h1>Teacher Login Reports</h1>
        </header>

        <!-- Login Logs Table -->
        <div class="table-responsive mt-3">
            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Log ID</th>
                        <th>Username</th>
                        <th>Department</th>
                        <th>Login Date & Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><?= htmlspecialchars($row['log_id']); ?></td>
                            <td><?= htmlspecialchars($row['username']); ?></td>
                            <td><?= htmlspecialchars($row['department']); ?></td>
                            <td><?= htmlspecialchars($row['login_time']); ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
