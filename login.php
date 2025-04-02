<?php
session_start();
header("Content-Type: application/json");

// Database connection
$conn = new mysqli("localhost", "root", "", "teacher_assistant_db");

if ($conn->connect_error) {
    echo json_encode(["success" => false, "error" => "Database connection failed"]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    if (empty($username) || empty($password)) {
        echo json_encode(["success" => false, "error" => "Username and password are required"]);
        exit();
    }

    // Prepare query to fetch user details
    $stmt = $conn->prepare("SELECT user_id, username, password, role, status FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $stmt->close(); // Close statement after fetching data

        // Check if the account is active
        if ($row["status"] !== "active") {
            echo json_encode(["success" => false, "error" => "Account is inactive."]);
            exit();
        }

        // Verify password
        if (password_verify($password, $row["password"])) {
            // Set session variables
            $_SESSION["user_id"] = $row["user_id"];
            $_SESSION["username"] = $row["username"];
            $_SESSION["role"] = $row["role"];

            // Insert login record into login_logs
            $log_stmt = $conn->prepare("INSERT INTO login_logs (user_id) VALUES (?)");
            $log_stmt->bind_param("i", $row["user_id"]);

            if ($log_stmt->execute()) {
                error_log("Login log inserted successfully for user_id: " . $row["user_id"]);
            } else {
                error_log("Error inserting login log: " . $log_stmt->error);
                echo json_encode(["success" => false, "error" => "Failed to log login"]);
                exit();
            }
            $log_stmt->close();

            // Redirect based on role
            $redirect_page = ($row["role"] === "admin") ? "admin_dashboard.php" : "chatbot_page.php";
            echo json_encode(["success" => true, "redirect" => $redirect_page]);
        } else {
            echo json_encode(["success" => false, "error" => "Incorrect password"]);
        }
    } else {
        echo json_encode(["success" => false, "error" => "User not found"]);
    }
}

// Close the database connection
$conn->close();
?>
