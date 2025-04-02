<!DOCTYPE html>
<html lang="en">
<head>
    <title>API Key Management</title>
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
        <a href="api_key_management.php" class="active">API Key</a>
        <a href="login_reports.php">Login Reports</a>
        <a href="database_cleanup.php">Database Cleanup</a>
        <a href="ai_logs.php">AI Logs & Reports</a>
    </div>

    <div class="main-content">
        <header>
            <h1>API Key Management</h1>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#apiModal">Add API Key</button>
        </header>

        <!-- API Keys Table -->
        <div class="table-responsive mt-3">
            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Service Name</th>
                        <th>API Key</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="apiKeysTable">
                    <!-- API keys will be loaded here dynamically -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add API Key Modal -->
    <!-- Add API Key Modal -->
<div class="modal fade" id="apiModal" tabindex="-1" aria-labelledby="modalTitle" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add API Key</h5>
                <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal" aria-label="Close" style="width: 20px; height: 20px;"></button>
            </div>
            <div class="modal-body">
                <form id="addApiForm">
                    <div class="mb-3">
                        <label class="form-label">Service Name:</label>
                        <input type="text" class="form-control" name="service_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">API Key:</label>
                        <input type="password" class="form-control" name="api_key" required>
                    </div>
                    <button type="submit" class="btn btn-success w-100">Save</button>
                </form>
            </div>
        </div>
    </div>
</div>


    <script>
        // Fetch API Keys
        function loadApiKeys() {
            $.get("fetch_api_keys.php", function(data) {
                $("#apiKeysTable").html(data);
            });
        }
        loadApiKeys();

        // Handle form submission
        $("#addApiForm").submit(function(e) {
            e.preventDefault();
            $.post("add_api_key.php", $(this).serialize(), function(response) {
                alert(response.message);
                if (response.success) {
                    $("#apiModal").modal("hide"); // Close modal
                    loadApiKeys(); // Refresh table
                    $("#addApiForm")[0].reset(); // Reset form
                }
            }, "json");
        });

        function loadApiKeys() {
        $.get("fetch_api_keys.php", function(data) {
            $("#apiKeysTable").html(data);
        });
    }

    loadApiKeys(); // Load API keys on page load

    // Delete API Key
    $(document).on("click", ".delete-btn", function () {
    let apiKeyId = $(this).attr("data-api-key-id"); // Fix: Ensure correct attribute is used

    if (!apiKeyId) {
        alert("Error: API Key ID is missing!");
        return;
    }

    if (!confirm("Are you sure you want to delete this API key?")) return;

    $.ajax({
        url: "delete_api_key.php",
        type: "POST",
        data: { api_key_id: apiKeyId },
        dataType: "json",
        success: function (response) {
            alert(response.message);
            if (response.success) {
                location.reload();
            }
        },
        error: function (xhr, status, error) {
            console.error("AJAX Error:", status, error);
            alert("Failed to delete API key. Check console for details.");
        }
    });
});
    </script>

</body>
</html>
