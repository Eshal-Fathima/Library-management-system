<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: pages/login.php");
    exit;
}
$user = $_SESSION['user'];
?>
<link rel="stylesheet" href="../css/style.css">
<a href="mybooks.php">My Borrowed Books</a>
<h2>Welcome, <?php echo $user['name']; ?>!</h2>
<a href="books.php">View Books</a>
<a href="logout.php">Logout</a>
