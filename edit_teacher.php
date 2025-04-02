<?php
session_start();

// Check if the admin is logged in
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    die(json_encode(["status" => "error", "message" => "Unauthorized access."]));
}

$conn = new mysqli("localhost", "root", "", "teacher_assistant_db");

if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Database connection failed."]));
}

// Fetch teacher data (for modal)
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['id'])) {
    $user_id = $_GET['id'];

    $sql = "SELECT u.user_id, u.username, u.email, u.role, u.status, 
                   td.first_name, td.last_name, td.middle_name, 
                   td.acad_rank, td.department, td.teacher_code_id, td.profile_picture 
            FROM users u 
            LEFT JOIN teacher_details td ON u.user_id = td.user_id 
            WHERE u.user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        echo json_encode(["status" => "success", "data" => $user]);
    } else {
        echo json_encode(["status" => "error", "message" => "User not found."]);
    }
    exit();
}

// Handle form submission (for updating user data)
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = $_POST['edit_user_id'] ?? null;
    $username = trim($_POST['edit_username'] ?? '');
    $email = trim($_POST['edit_email'] ?? '');
    $password = trim($_POST['edit_password'] ?? '');
    $first_name = trim($_POST['edit_first_name'] ?? '');
    $last_name = trim($_POST['edit_last_name'] ?? '');
    $middle_name = trim($_POST['edit_middle_name'] ?? '');
    $acad_rank = trim($_POST['edit_acad_rank'] ?? '');
    $department = trim($_POST['edit_department'] ?? '');
    $teacher_code_id = trim($_POST['edit_teacher_code_id'] ?? '');
    $profile_picture = $_POST['profile_picture_old']; // Keep the old one by default

if (!empty($_FILES['profile_picture']['name'])) { // If a new file is uploaded
    $target_dir = "uploads/";
    $file_ext = strtolower(pathinfo($_FILES["profile_picture"]["name"], PATHINFO_EXTENSION));
    $allowed_exts = ["jpg", "jpeg", "png"];

    if (!in_array($file_ext, $allowed_exts)) {
        echo json_encode(["status" => "error", "message" => "Invalid image type. Only JPG, JPEG, and PNG are allowed."]);
        exit();
    }

    $new_file_name = "TCH_" . $teacher_code_id . "." . $file_ext;
    $target_file = $target_dir . $new_file_name;

    // Delete old profile picture only if it is NOT the default one
    if (!empty($profile_picture) && $profile_picture !== "default.png" && file_exists($target_dir . $profile_picture)) {
        unlink($target_dir . $profile_picture);
    }

    if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
        $profile_picture = $new_file_name; // Assign new picture name
    } else {
        echo json_encode(["status" => "error", "message" => "Error uploading the profile picture."]);
        exit();
    }



        $profile_picture = $new_file_name;
    }

    // Update users table (Handle password update conditionally)
    if (!empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $sql = "UPDATE users SET username=?, email=?, password=? WHERE user_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $username, $email, $hashedPassword, $user_id);
    } else {
        $sql = "UPDATE users SET username=?, email=? WHERE user_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $username, $email, $user_id);
    }

    if (!$stmt->execute()) {
        echo json_encode(["status" => "error", "message" => "Failed to update user: " . $stmt->error]);
        exit();
    }

    // Update teacher_details table
    $sql = "UPDATE teacher_details SET first_name=?, last_name=?, middle_name=?, acad_rank=?, department=?, teacher_code_id=?, profile_picture=? WHERE user_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssi", $first_name, $last_name, $middle_name, $acad_rank, $department, $teacher_code_id, $profile_picture, $user_id);

    if (!$stmt->execute()) {
        echo json_encode(["status" => "error", "message" => "Failed to update teacher details: " . $stmt->error]);
        exit();
    }

    echo "<script>
        alert('User updated successfully!');
        window.location.href = 'admin_dashboard.php'; // Redirect back to the dashboard
        </script>";
    exit();

}


$conn->close();
?>
