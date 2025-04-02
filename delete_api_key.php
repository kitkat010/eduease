<?php
$conn = new mysqli("localhost", "root", "", "teacher_assistant_db");

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $api_key_id = $_POST["api_key_id"] ?? null;

    if (!$api_key_id) {
        echo json_encode(["success" => false, "message" => "No API key ID provided"]);
        error_log("Error: No API key ID received in delete_api_key.php");
        exit;
    }

    error_log("Deleting API Key ID: " . $api_key_id);

    $stmt = $conn->prepare("DELETE FROM api_keys WHERE api_key_id = ?");
    $stmt->bind_param("i", $api_key_id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "API key deleted successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to delete API key"]);
        error_log("Error: Failed to delete API Key ID " . $api_key_id);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
    error_log("Error: Invalid request method in delete_api_key.php");
}

$conn->close();
?>