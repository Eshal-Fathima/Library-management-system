<?php
$name = $_POST['name'];
$regno = $_POST['regno'];
$email = $_POST['email'];
$password = $_POST['password'];
$role = $_POST['role']; // New line

// Hash password (optional but recommended)
$password = password_hash($password, PASSWORD_DEFAULT);

// Connect to MySQL
$conn = new mysqli("localhost", "root", "", "library");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Insert user with role
$sql = "INSERT INTO users (name, regno, email, password, role) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssss", $name, $regno, $email, $password, $role);

if ($stmt->execute()) {
    header("Location: login.php");
    exit();
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
