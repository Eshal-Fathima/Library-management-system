<?php
include('includes/db.php');
session_start();

$user_id = $_SESSION['user']['id'];

$result = mysqli_query($conn, "SELECT * FROM books");

echo "<h2>Books Available</h2><div class='book-list'>";

while ($book = mysqli_fetch_assoc($result)) {
    echo "<p>{$book['title']} by {$book['author']} - " .
         ($book['available'] ? "Available" : "Not Available");

    if ($book['available']) {
        echo " <form method='POST' style='display:inline;'>
                <input type='hidden' name='book_id' value='{$book['id']}'>
                <button type='submit' name='borrow'>Borrow</button>
              </form>";
    }

    echo "</p>";
}

echo "</div>";

if (isset($_POST['borrow'])) {
    $book_id = $_POST['book_id'];
    $borrowed_date = date('Y-m-d');
    $due_date = date('Y-m-d', strtotime('+7 days'));

    // Add borrow record
    $insert = "INSERT INTO borrowed_books (user_id, book_id, borrowed_date, due_date)
               VALUES ($user_id, $book_id, '$borrowed_date', '$due_date')";
    mysqli_query($conn, $insert);

    // Mark book as unavailable
    mysqli_query($conn, "UPDATE books SET available = 0 WHERE id = $book_id");

    header("Location: books.php");
}
?>
