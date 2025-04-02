<?php
session_start();

// Ensure the user is an admin
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.html");
    exit();
}

$conn = new mysqli("localhost", "root", "", "teacher_assistant_db");

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Fetch only active teachers
$sql = "SELECT u.user_id, u.username, td.first_name, td.last_name 
        FROM users u 
        LEFT JOIN teacher_details td ON u.user_id = td.user_id 
        WHERE u.role = 'teacher' AND u.status = 'active'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Cleanup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" type="text/css" href="css/admin_dashboard.css">
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <h2>Admin</h2>
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="api_key_management.php">API Keys</a>
    <a href="login_reports.php">Login Reports</a>
    <a href="database_cleanup.php" class="active">Database Cleanup</a>
    <a href="ai_logs.php">AI Logs & Reports</a>
</div>

<!-- Main Content -->
<div class="main-content">
    <header>
        <h1>Database Cleanup</h1>
    </header>

    <div class="container mt-4">
        <h2>Select a Teacher</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Teacher Name</th>
                    <th>Username</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['last_name']) ?>, <?= htmlspecialchars($row['first_name']) ?></td>
                        <td><?= htmlspecialchars($row['username']) ?></td>
                        <td>
                            <a href="session_list.php?teacher_id=<?= $row['user_id'] ?>" class="btn btn-primary">View Chat History</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>

<?php $conn->close(); ?>
