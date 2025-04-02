<?php
session_start();

// Ensure the user is an admin
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.html");
    exit();
}

if (!isset($_GET['user_id'])) {
    die("Invalid request. Debug: user_id is missing in URL.");
}

$user_id = intval($_GET['user_id']);
if ($user_id <= 0) {
    die("Invalid request. Debug: user_id is invalid (" . htmlspecialchars($_GET['user_id']) . ").");
}

$conn = new mysqli("localhost", "root", "", "teacher_assistant_db");

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Fetch teacher name
$teacher_sql = "SELECT td.first_name, td.last_name 
                FROM teacher_details td
                JOIN users u ON td.user_id = u.user_id
                WHERE u.user_id = ? AND u.role = 'teacher'";

$stmt = $conn->prepare($teacher_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$teacher_result = $stmt->get_result();
$teacher = $teacher_result->fetch_assoc();

if (!$teacher) {
    die("Teacher not found. Debug: user_id = " . htmlspecialchars($user_id));
}

// Fetch AI logs data (sessions per day)
$sessions_sql = "SELECT DATE(timestamp) AS log_date, COUNT(*) AS session_count 
                 FROM ai_logs 
                 WHERE user_id = ? 
                 GROUP BY log_date 
                 ORDER BY log_date DESC";
$stmt = $conn->prepare($sessions_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$sessions_result = $stmt->get_result();

$dates = [];
$session_counts = [];
while ($row = $sessions_result->fetch_assoc()) {
    $dates[] = $row['log_date'];
    $session_counts[] = $row['session_count'];
}

// Fetch actions and response times
$actions_sql = "SELECT action_type, AVG(response_time) AS avg_response_time, COUNT(*) AS action_count
                FROM ai_logs
                WHERE user_id = ?
                GROUP BY action_type
                ORDER BY action_count DESC";
$stmt = $conn->prepare($actions_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$actions_result = $stmt->get_result();

$actions = [];
$action_counts = [];
$response_times = [];
while ($row = $actions_result->fetch_assoc()) {
    $actions[] = $row['action_type'];
    $action_counts[] = $row['action_count'];
    $response_times[] = $row['avg_response_time'];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>AI Logs Dashboard</title>
    <link rel="stylesheet" href="css/admin_style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        canvas {
            max-width: 100%;
            height: 300px !important;
        }
    </style>
</head>
<body>
    
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-2 d-none d-md-block bg-light sidebar">
                <div class="position-sticky">
                    <h2 class="text-center mt-3">Admin</h2>
                    <ul class="nav flex-column">
                        <li class="nav-item"><a class="nav-link" href="admin_dashboard.php">Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="api_key_management.php">API Key</a></li>
                        <li class="nav-item"><a class="nav-link" href="login_reports.php">Login Reports</a></li>
                        <li class="nav-item"><a class="nav-link" href="database_cleanup.php">Database Cleanup</a></li>
                        <li class="nav-item"><a class="nav-link active" href="ai_logs.php">AI Logs & Reports</a></li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-10 ms-sm-auto px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">AI Logs Dashboard</h1>
                </div>
                <h2>AI Logs for <?= htmlspecialchars($teacher['last_name']) ?>, <?= htmlspecialchars($teacher['first_name']) ?></h2>

                <div class="card p-3 mb-4">
                    <h4>Sessions Per Day</h4>
                    <canvas id="sessionsChart"></canvas>
                </div>
                
                <div class="card p-3">
                    <h4>Actions & Response Times</h4>
                    <canvas id="actionsChart"></canvas>
                </div>
                
                <a href="ai_logs.php" class="btn btn-secondary mt-3">Back to Teacher List</a>
            </main>
        </div>
    </div>

    <script>
        new Chart(document.getElementById('sessionsChart').getContext('2d'), { type: 'line', data: {
            labels: <?= json_encode($dates) ?>,
            datasets: [{
                label: 'Sessions Per Day',
                data: <?= json_encode($session_counts) ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        }});

        new Chart(document.getElementById('actionsChart').getContext('2d'), { type: 'pie', data: {
            labels: <?= json_encode($actions) ?>,
            datasets: [{
                label: 'Action Count',
                data: <?= json_encode($action_counts) ?>,
                backgroundColor: ['rgba(255, 99, 132, 0.6)', 'rgba(75, 192, 192, 0.6)', 'rgba(255, 206, 86, 0.6)'],
                borderColor: ['rgba(255, 99, 132, 1)', 'rgba(75, 192, 192, 1)', 'rgba(255, 206, 86, 1)'],
                borderWidth: 1
            }]
        }});
    </script>
</body>
</html>
