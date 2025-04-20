<?php
include('includes/db.php');
session_start();

$user_id = $_SESSION['user']['id'];

$result = mysqli_query($conn, "
  SELECT b.title, bb.borrowed_date, bb.due_date, bb.returned_date, bb.fine
  FROM borrowed_books bb
  JOIN books b ON bb.book_id = b.id
  WHERE bb.user_id = $user_id
");

echo "<h2>My Borrowed Books</h2><div class='book-list'>";

while ($row = mysqli_fetch_assoc($result)) {
    echo "<p><strong>{$row['title']}</strong><br>";
    echo "Borrowed: {$row['borrowed_date']}<br>";
    echo "Due: {$row['due_date']}<br>";
    echo "Returned: " . ($row['returned_date'] ?? "Not yet") . "<br>";
    echo "Fine: ₹" . $row['fine'] . "</p>";
}

echo "</div>";
