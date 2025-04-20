<?php
// Optional: session_start(); if you’re doing backend validation
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register - Library System</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
  <div class="login-container">
    
    <!-- Left: Register Form -->
    <div class="login-left">
      <h2>Create Account</h2>
      <p>Join our library world</p>

      <form action="process_register.php" method="POST">
  <input type="text" name="name" placeholder="Full Name" required>
  <input type="text" name="regno" placeholder="Registration Number" required>
  <input type="email" name="email" placeholder="College Email ID" required>
  <input type="password" name="password" placeholder="Password" required>

  <!-- Role Dropdown -->
  <div class="role-select">
  <label for="role">I am a:</label>
  <select name="role" id="role" required>
    <option value="" disabled selected>Select your role</option>
    <option value="student">📘 Student</option>
    <option value="teacher">📗 Teacher</option>
  </select>
</div>


  <button type="submit">Register</button>
</form>

      <div class="register-link">
        Already have an account? <a href="login.php">Login here</a>
      </div>
    </div>

    <!-- Right: Quote or Welcome Message -->
    <div class="login-right">
      <div class="overlay-text">
        <h3>Welcome to the Library</h3>
        <p>"A room without books is like a body without a soul." – Cicero</p>
      </div>
    </div>

  </div>
</body>
</html>
