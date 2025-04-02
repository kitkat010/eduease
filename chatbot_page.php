<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "teacher") {
    header("Location: login.html");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Chatbot Page</title>
</head>
<body>
    <h1>Welcome, <?= $_SESSION["username"] ?>!</h1>
    <p>This is the Teacher Chatbot Page.</p>
    <a href="logout.php">Logout</a>
</body>
</html>
