<?php
include('../includes/db.php');
session_start();

$error = '';

if (isset($_POST['login'])) {
    $name = $_POST['name'];
    $reg_last2 = $_POST['reg_last2'];

    $query = "SELECT * FROM users WHERE name = '$name'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        $regno = $user['regno'];
        $last2 = substr($regno, -2);

        if ($last2 === $reg_last2) {
            // Save user info to session
            $_SESSION['user'] = [
                'name' => $user['name'],
                'regno' => $user['regno'],
                'email' => $user['email'],
                'role' => $user['role']
            ];

            // Redirect to shared dashboard
            header('Location: ../student_dashboard.php');
            exit();
        } else {
            $error = "Incorrect registration number digits.";
        }
    } else {
        $error = "User not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | Library Management System</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<div class="login-container">
    <div class="login-left">
        <h2>Ready to Explore? 📚</h2>
        <p>Login to access your library portal.</p>

        <form method="POST" action="">
            <input type="text" name="name" placeholder="Enter your name" required>
            <input type="text" name="reg_last2" placeholder="Last 2 digits of Reg No" maxlength="2" required>
            <button type="submit" name="login">Login</button>

            <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
        </form>

        <p class="register-link">
            Don't have an account? 
            <a href="register.php">Register Now</a>
        </p>
    </div>

    <div class="login-right">
        <img src="../assets/library-bg.jpg" alt="Library" />
        <div class="overlay-text">
            <h3>Library Management System</h3>
            <p>“A room without books is like a body without a soul.”</p>
        </div>
    </div>
</div>

</body>
</html>
