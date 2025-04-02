<?php
$conn = new mysqli("localhost", "root", "", "teacher_assistant_db");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $service_name = $_POST['service_name'];
    $api_key = $_POST['api_key'];

    if (empty($service_name) || empty($api_key)) {
        echo json_encode(["success" => false, "message" => "All fields are required."]);
        exit;
    }

    // Insert API key into database
    $stmt = $conn->prepare("INSERT INTO api_keys (service_name, api_key, status, created_at) VALUES (?, ?, 'active', NOW())");
    $stmt->bind_param("ss", $service_name, $api_key);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "API Key added successfully!"]);
    } else {
        echo json_encode(["success" => false, "message" => "Database error."]);
    }

    $stmt->close();
    $conn->close();
}
?>
