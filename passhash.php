<?php
$password = "password123"; // The password you want to hash
$hashed_password = password_hash($password, PASSWORD_BCRYPT);

echo "Hashed Password: " . $hashed_password;
?>