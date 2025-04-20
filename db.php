<?php
$host = "localhost";
$user = "";
$pass = "";
$db = "library";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
