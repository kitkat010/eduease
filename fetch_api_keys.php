<?php
$conn = new mysqli("localhost", "root", "", "teacher_assistant_db");

$sql = "SELECT * FROM api_keys";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['service_name']) . "</td>";
        echo "<td><input type='password' class='form-control' value='" . htmlspecialchars($row['api_key']) . "' disabled></td>";
        echo "<td>" . htmlspecialchars($row['status']) . "</td>";
        echo "<td><button class='btn btn-danger btn-sm delete-btn' data-api-key-id='" . $row['api_key_id'] . "'>Delete</button></td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='4'>No API keys found</td></tr>";
}
$conn->close();
?>
