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

// Fetch deactivated users
$sql = "SELECT user_id, username, email, role FROM users WHERE status = 'inactive'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" type="text/css" href="css/admin_dashboard.css">
    <title>Deactivated Accounts</title>
</head>
<body>
    <div class="sidebar">
        <h2>Admin</h2>
        <a href="admin_dashboard.php">Dashboard</a>
        
    </div>

    <div class="main-content">
        <header>
            <h1>Deactivated Accounts</h1>
        </header>

        <h2>Inactive Users</h2>
        <div class="users-list">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="user-card">
                    <div class="user-info">
                        <strong><?= htmlspecialchars($row['username']) ?></strong>
                        <p><?= htmlspecialchars($row['email']) ?> (<?= htmlspecialchars($row['role']) ?>)</p>
                    </div>
                    <div class="user-actions">
                        <button class="activate" onclick="activateUser(<?= $row['user_id'] ?>)">✔ Activate</button>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <script>
        function activateUser(userId) {
            if (confirm("Are you sure you want to activate this user?")) {
                window.location.href = "activate_teacher.php?id=" + userId;
            }
        }
    </script>
</body>
</html>

<?php $conn->close(); ?>
