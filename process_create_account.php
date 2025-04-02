<?php
session_start();
header("Content-Type: application/json");

// Check if the admin is logged in
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    echo json_encode(["success" => false, "message" => "Unauthorized access."]);
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "teacher_assistant_db");
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $first_name = trim($_POST["first_name"]);
    $last_name = trim($_POST["last_name"]);
    $middle_name = trim($_POST["middle_name"]);
    $acad_rank = trim($_POST["acad_rank"]);
    $department = trim($_POST["department"]);
    $teacher_code_id = trim($_POST["teacher_code_id"]);

    // Check for required fields
    if (empty($username) || empty($email) || empty($password) || empty($first_name) || empty($last_name) || empty($teacher_code_id)) {
        echo json_encode(["success" => false, "message" => "All required fields must be filled."]);
        exit();
    }

    // Check if username or email already exists
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        echo json_encode(["success" => false, "message" => "Username or email already exists."]);
        exit();
    }
    $stmt->close();

    // Check if teacher_code_id already exists
    $stmt = $conn->prepare("SELECT teacher_code_id FROM teacher_details WHERE teacher_code_id = ?");
    $stmt->bind_param("s", $teacher_code_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        echo json_encode(["success" => false, "message" => "Teacher Code ID already exists."]);
        exit();
    }
    $stmt->close();

    // Default profile picture
    $profile_picture = "default.jpg"; // Make sure this image exists in 'uploads/'

    // **File Upload Handling**
    if (isset($_FILES["profile_picture"]) && $_FILES["profile_picture"]["error"] == 0) {
        $upload_dir = $_SERVER['DOCUMENT_ROOT'] . "/eduease/uploads/"; // Ensure correct path

        // Create the uploads directory if it doesn't exist
        if (!file_exists($upload_dir) && !mkdir($upload_dir, 0777, true)) {
            echo json_encode(["success" => false, "message" => "Failed to create uploads directory."]);
            exit();
        }

        $file_ext = strtolower(pathinfo($_FILES["profile_picture"]["name"], PATHINFO_EXTENSION));
        $allowed_exts = ["jpg", "jpeg", "png"];

        if (!in_array($file_ext, $allowed_exts)) {
            echo json_encode(["success" => false, "message" => "Invalid image type. Only JPG, JPEG, and PNG are allowed."]);
            exit();
        }

        // Generate a unique filename to prevent overwriting
        $new_file_name = "TCH_" . $teacher_code_id . "_" . uniqid() . "." . $file_ext;
        $target_file = $upload_dir . $new_file_name;

        // Move the uploaded file
        if (!move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
            echo json_encode(["success" => false, "message" => "Error uploading the profile picture."]);
            exit();
        }

        // Store only the file name in the database, not the full path
        $profile_picture = $new_file_name;
    }

    // Hash the password before storing
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert into users table with role = 'teacher'
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, status) VALUES (?, ?, ?, 'teacher', 'active')");
    $stmt->bind_param("sss", $username, $email, $hashed_password);

    if (!$stmt->execute()) {
        echo json_encode(["success" => false, "message" => "Error creating user account. SQL Error: " . $stmt->error]);
        exit();
    }

    $user_id = $stmt->insert_id;

    // Insert into teacher_details table
    $stmt = $conn->prepare("INSERT INTO teacher_details (user_id, first_name, last_name, middle_name, acad_rank, department, teacher_code_id, profile_picture) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssssss", $user_id, $first_name, $last_name, $middle_name, $acad_rank, $department, $teacher_code_id, $profile_picture);

    if (!$stmt->execute()) {
        $conn->query("DELETE FROM users WHERE user_id = $user_id");
        echo json_encode(["success" => false, "message" => "Error inserting teacher details. User was removed. SQL Error: " . $stmt->error]);
        exit();
    }

    echo json_encode(["success" => true, "message" => "Teacher account created successfully!", "profile_picture" => $profile_picture]);

    $stmt->close();
    $conn->close();
}
?>
