<?php
session_start();
header("Content-Type: application/json");

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    echo json_encode(["success" => false, "message" => "Unauthorized access."]);
    exit();
}

$conn = new mysqli("localhost", "root", "", "teacher_assistant");
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed."]);
    exit();
}

// Encrypt API Key Function
function encryptApiKey($key) {
    $secret_key = "your_secret_key"; // Replace with a secure key
    return base64_encode(openssl_encrypt($key, "AES-128-CTR", $secret_key, 0, "1234567891011121"));
}

// Add API Key
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["service_name"], $_POST["api_key"])) {
    $service_name = trim($_POST["service_name"]);
    $api_key = encryptApiKey(trim($_POST["api_key"]));

    $stmt = $conn->prepare("INSERT INTO api_keys (service_name, api_key, created_at) VALUES (?, ?, NOW())");
    $stmt->bind_param("ss", $service_name, $api_key);
    $stmt->execute();

    echo json_encode(["success" => true, "message" => "API Key added successfully."]);
    exit();
}

// Delete API Key
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"]) && $_POST["action"] === "delete") {
    $api_key_id = intval($_POST["api_key_id"]);
    
    $stmt = $conn->prepare("DELETE FROM api_keys WHERE api_key_id = ?");
    $stmt->bind_param("i", $api_key_id);
    $stmt->execute();

    echo json_encode(["success" => true, "message" => "API Key deleted."]);
    exit();
}
?>
