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
$sql = "
    SELECT u.user_id, u.username, u.email, u.status, 
           COALESCE(td.profile_picture, 'default.png') AS profile_picture, 
           td.first_name, td.middle_name, td.last_name, td.acad_rank, td.department, td.teacher_code_id
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
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="css/api_key.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" type="text/css" href="css/admin_dashboard.css">
</head>
<body>

<div class="sidebar">
    <h2>Admin</h2>
    <a href="admin_dashboard.php" class="active">Dashboard</a>
    <a href="api_key_management.php">API Keys</a>
    <a href="login_reports.php">Login Reports</a>
    <a href="database_cleanup.php">Database Cleanup</a>
    <a href="ai_logs.php">AI Logs & Reports</a>
</div>

<div class="main-content">
    <header>
        <h1>Dashboard</h1>
        <div class="header-buttons">
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createAccountModal">Add Account</button>
            <a href="view_deactivated.php" class="btn red">Deactivated Accounts</a>
        </div>
    </header>

    <h2>Users</h2>
    <div class="users-list" id="userList">
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="user-card" id="user-<?= $row['user_id'] ?>">
                <img src="uploads/<?= htmlspecialchars($row['profile_picture']) ?>" 
                     onerror="this.onerror=null; this.src='uploads/default.png';"
                     alt="Profile Picture" class="user-img">
                <div class="user-info">
                    <strong><?= htmlspecialchars($row['last_name']) ?>, <?= htmlspecialchars($row['first_name']) ?></strong>
                    <p><?= htmlspecialchars($row['acad_rank']) ?> - <?= htmlspecialchars($row['department']) ?></p>
                </div>
                <div class="user-actions">
                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editAccountModal"
                        onclick="populateEditModal(<?= $row['user_id'] ?>, '<?= $row['username'] ?>', '<?= $row['email'] ?>', '<?= $row['first_name'] ?>', '<?= $row['middle_name'] ?>', '<?= $row['last_name'] ?>', '<?= $row['acad_rank'] ?>', '<?= $row['department'] ?>', '<?= $row['teacher_code_id'] ?>')">
                        Edit
                    </button>
                    <button class="btn btn-danger btn-sm" onclick="deactivateUser(<?= $row['user_id'] ?>)">Deactivate</button>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<!-- Create Account Modal -->
<div class="modal fade" id="createAccountModal" tabindex="-1" aria-labelledby="createAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
            <form id="createAccountForm" action="create_account.php" method="POST" enctype="multipart/form-data">
                <input type="text" name="username" class="form-control mb-2" placeholder="Username" required>
                <input type="email" name="email" class="form-control mb-2" placeholder="Email" required>
                <input type="password" name="password" class="form-control mb-2" placeholder="Password" required>
                <input type="text" name="first_name" class="form-control mb-2" placeholder="First Name" required>
                <input type="text" name="middle_name" class="form-control mb-2" placeholder="Middle Name">
                <input type="text" name="last_name" class="form-control mb-2" placeholder="Last Name" required>
                <input type="text" name="acad_rank" class="form-control mb-2" placeholder="Academic Rank" required>
                <input type="text" name="department" class="form-control mb-2" placeholder="Department" required>
                <input type="text" name="teacher_code_id" class="form-control mb-2" placeholder="Teacher Code ID" required>
                <input type="file" name="profile_picture" class="form-control mb-2">
                <button type="submit" class="btn btn-success">Create Account</button>
            </form>

            </div>
        </div>
    </div>
</div>

<!-- Edit Account Modal -->
<div class="modal fade" id="editAccountModal" tabindex="-1" aria-labelledby="editAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editAccountForm" action="edit_teacher.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="edit_user_id" id="edit_user_id">
                    <input type="hidden" name="profile_picture_old" id="profile_picture_old"> <!-- Hidden old profile pic -->

                    <label>Username</label>
                    <input type="text" name="edit_username" id="edit_username" class="form-control mb-2" required>

                    <label>Email</label>
                    <input type="email" name="edit_email" id="edit_email" class="form-control mb-2" required>

                    <label>First Name</label>
                    <input type="text" name="edit_first_name" id="edit_first_name" class="form-control mb-2" required>

                    <label>Middle Name</label>
                    <input type="text" name="edit_middle_name" id="edit_middle_name" class="form-control mb-2">

                    <label>Last Name</label>
                    <input type="text" name="edit_last_name" id="edit_last_name" class="form-control mb-2" required>

                    <label>Academic Rank</label>
                    <input type="text" name="edit_acad_rank" id="edit_acad_rank" class="form-control mb-2" required>

                    <label>Department</label>
                    <input type="text" name="edit_department" id="edit_department" class="form-control mb-2" required>

                    <label>Teacher Code ID</label>
                    <input type="text" name="edit_teacher_code_id" id="edit_teacher_code_id" class="form-control mb-2" required>

                    <label>New Password (Leave blank to keep current)</label>
                    <input type="password" name="edit_password" id="edit_password" class="form-control mb-2">

                    <!-- Profile Picture -->
                    <label>Profile Picture</label>
                    <div class="mb-2">
                        <img id="current_profile_picture" src="uploads/default.png" alt="Profile Picture" class="img-thumbnail" style="width: 100px; height: 100px;">
                    </div>
                    <input type="file" name="profile_picture" class="form-control mb-2" accept=".jpg,.jpeg,.png">

                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>



<script>
function populateEditModal(userId, username, email, firstName, middleName, lastName, acadRank, department, teacherCodeId) {
    document.getElementById("edit_user_id").value = userId;
    document.getElementById("edit_username").value = username;
    document.getElementById("edit_email").value = email;
    document.getElementById("edit_first_name").value = firstName;
    document.getElementById("edit_middle_name").value = middleName;
    document.getElementById("edit_last_name").value = lastName;
    document.getElementById("edit_acad_rank").value = acadRank;
    document.getElementById("edit_department").value = department;
    document.getElementById("edit_teacher_code_id").value = teacherCodeId;
}

document.getElementById("createAccountForm").addEventListener("submit", function(event) {
    console.log("Form is submitting..."); // Debugging message
});

document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".editBtn").forEach(button => {
        button.addEventListener("click", function () {
            var userId = this.getAttribute("data-id");

            // Fetch user data via AJAX
            fetch("fetch_user.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "user_id=" + userId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById("edit_user_id").value = data.user_id;
                    document.getElementById("edit_username").value = data.username;
                    document.getElementById("edit_email").value = data.email;
                    document.getElementById("edit_first_name").value = data.first_name;
                    document.getElementById("edit_middle_name").value = data.middle_name;
                    document.getElementById("edit_last_name").value = data.last_name;
                    document.getElementById("edit_acad_rank").value = data.acad_rank;
                    document.getElementById("edit_department").value = data.department;
                    document.getElementById("edit_teacher_code_id").value = data.teacher_code_id;

                    // Open modal
                    var editModal = new bootstrap.Modal(document.getElementById("editAccountModal"));
                    editModal.show();
                } else {
                    alert("User not found!");
                }
            })
            .catch(error => {
                console.error("Error:", error);
                alert("Failed to fetch user data.");
            });
        });
    });
});

document.getElementById("editForm").addEventListener("submit", function (e) {
    e.preventDefault();
    let formData = new FormData(this);
    let userId = document.getElementById("editUserId").value;

    fetch("edit_teacher.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === "success") {
            alert("User updated successfully!");

            // Update the table row dynamically
            let row = document.querySelector(`#user-${userId}`);
            if (row) {
                row.querySelector(".username").innerText = formData.get("username");
                row.querySelector(".email").innerText = formData.get("email");
                row.querySelector(".role").innerText = formData.get("role");
                row.querySelector(".status").innerText = formData.get("status");
            }

            // Close the modal
            let modal = bootstrap.Modal.getInstance(document.getElementById('editAccountModal'));
            modal.hide();
        } else {
            alert("Error: " + data.message);
        }
    })
    .catch(error => console.error("Error updating user:", error));
});

function deactivateUser(userId) {
    if (!confirm("Are you sure you want to deactivate this user?")) {
        return;
    }

    // Debug: Check if userId is correct
    console.log("Deactivating user ID:", userId);

    fetch("deactivate_teacher.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "id=" + encodeURIComponent(userId)
    })
    .then(response => response.json()) // Parse JSON response
    .then(data => {
        console.log("Server Response:", data); // Debug server response
        if (data.success) {
            alert("User deactivated successfully!");

            // Remove user from the active list dynamically
            let userRow = document.getElementById("user-" + userId);
            if (userRow) {
                userRow.remove();
            }
        } else {
            alert("Error: " + data.message);
        }
    })
    .catch(error => {
        console.error("Fetch Error:", error);
        alert("Failed to deactivate user.");
    });
}


</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</body>
</html>

<?php $conn->close(); ?>
