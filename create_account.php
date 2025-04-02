<?php
session_start();
header("Content-Type: text/html; charset=UTF-8"); // Set HTML response
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';

// Database connection
$conn = new mysqli("localhost", "root", "", "teacher_assistant_db");
if ($conn->connect_error) {
    echo "<script>
        Swal.fire('Error', 'Database connection failed!', 'error');
    </script>";
    exit();
}

// Required fields validation
$required_fields = ["username", "email", "password", "first_name", "middle_name", "last_name", "acad_rank", "department", "teacher_code_id"];
foreach ($required_fields as $field) {
    if (empty(trim($_POST[$field] ?? ""))) {
        echo "<script>
            Swal.fire('Error', 'Missing required field: $field', 'error');
        </script>";
        exit();
    }
}

// Sanitize and validate inputs
$username = trim($_POST["username"]);
$email = trim($_POST["email"]);
$password = password_hash($_POST["password"], PASSWORD_BCRYPT);
$first_name = trim($_POST["first_name"]);
$middle_name = trim($_POST["middle_name"]);
$last_name = trim($_POST["last_name"]);
$acad_rank = trim($_POST["acad_rank"]);
$department = trim($_POST["department"]);
$teacher_code_id = trim($_POST["teacher_code_id"]);

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "<script>
        Swal.fire('Error', 'Invalid email format!', 'error');
    </script>";
    exit();
}

// Check if username or email already exists
$checkUser = $conn->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
$checkUser->bind_param("ss", $username, $email);
$checkUser->execute();
$checkUser->store_result();
if ($checkUser->num_rows > 0) {
    echo "<script>
        Swal.fire('Error', 'Username or email already exists!', 'error');
    </script>";
    exit();
}
$checkUser->close();

// Profile Picture Upload
$targetDir = "uploads/";
$profile_picture = "default.png"; // Default profile picture

if (!empty($_FILES["profile_picture"]["name"])) {
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $fileName = time() . "_" . basename($_FILES["profile_picture"]["name"]); // Unique file name
    $targetFilePath = $targetDir . $fileName;
    $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

    // Allowed file types
    $allowedTypes = ["jpg", "jpeg", "png", "gif"];
    if (in_array($fileType, $allowedTypes)) {
        if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $targetFilePath)) {
            $profile_picture = $fileName; // Save file name in DB
        } else {
            echo "<script>
                Swal.fire('Error', 'Error uploading profile picture!', 'error');
            </script>";
            exit();
        }
    } else {
        echo "<script>
            Swal.fire('Error', 'Invalid file type! Only JPG, JPEG, PNG, and GIF are allowed.', 'error');
        </script>";
        exit();
    }
}

// Insert user data into users table
$sql = "INSERT INTO users (username, email, password, role, status) 
        VALUES (?, ?, ?, 'teacher', 'active')";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $username, $email, $password);

if ($stmt->execute()) {
    $user_id = $stmt->insert_id;

    // Insert teacher details
    $sqlDetails = "INSERT INTO teacher_details (user_id, first_name, middle_name, last_name, acad_rank, department, teacher_code_id, profile_picture) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmtDetails = $conn->prepare($sqlDetails);
    $stmtDetails->bind_param("isssssss", $user_id, $first_name, $middle_name, $last_name, $acad_rank, $department, $teacher_code_id, $profile_picture);

    if ($stmtDetails->execute()) {
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Account Created!',
                text: 'New user has been added successfully!',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                window.location.href = 'admin_dashboard.php';
            });
        </script>";
        exit();
    } else {
        echo "<script>
            Swal.fire('Error', 'Error inserting teacher details!', 'error');
        </script>";
    }
} else {
    echo "<script>
        Swal.fire('Error', 'Error creating account!', 'error');
    </script>";
}

// Close connection
$conn->close();
?>
