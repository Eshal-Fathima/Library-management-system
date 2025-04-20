<?php
session_start();
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['student', 'teacher'])) {
    header('Location: pages/login.php');
    exit();
}

include('./includes/db.php');
$name = $_SESSION['user']['name'];

$userQuery = "SELECT * FROM users WHERE name = ?";
$stmt = mysqli_prepare($conn, $userQuery);
mysqli_stmt_bind_param($stmt, "s", $name);
mysqli_stmt_execute($stmt);
$userResult = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($userResult);
$userId = $user['id'];

// Handle search functionality
$searchTerm = '';
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['search'])) {
    $searchTerm = mysqli_real_escape_string($conn, $_GET['search']);
}

// Modify the book query to include the search term
$bookQuery = "SELECT b.id, b.title, b.genre, 
              (SELECT COUNT(*) FROM borrowed_books bb WHERE bb.book_id = b.id AND bb.user_id = ? AND bb.returned_date IS NULL) AS already_borrowed 
              FROM books b
              WHERE b.title LIKE ? OR b.genre LIKE ?";
$stmt = mysqli_prepare($conn, $bookQuery);
$searchParam = "%$searchTerm%";
mysqli_stmt_bind_param($stmt, "iss", $userId, $searchParam, $searchParam);
mysqli_stmt_execute($stmt);
$bookResult = mysqli_stmt_get_result($stmt);

// Count borrowed books
$countQuery = "SELECT COUNT(*) FROM borrowed_books WHERE user_id = ? AND returned_date IS NULL";
$stmt2 = mysqli_prepare($conn, $countQuery);
mysqli_stmt_bind_param($stmt2, "i", $userId);
mysqli_stmt_execute($stmt2);
mysqli_stmt_bind_result($stmt2, $borrowedCount);
mysqli_stmt_fetch($stmt2);
mysqli_stmt_close($stmt2);

// Borrow logic
$successMessage = '';
$errorMessage = '';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['book_id']) && $borrowedCount < 2) {
    $bookId = $_POST['book_id'];
    $borrowedDate = $_POST['borrowed_date'];
    $dueDate = date('Y-m-d', strtotime($borrowedDate . ' +7 days'));

    $insertQuery = "INSERT INTO borrowed_books (user_id, book_id, borrowed_date, due_date) VALUES (?, ?, ?, ?)";
    $stmt3 = mysqli_prepare($conn, $insertQuery);
    mysqli_stmt_bind_param($stmt3, "iiss", $userId, $bookId, $borrowedDate, $dueDate);
    if (mysqli_stmt_execute($stmt3)) {
        $successMessage = "✅ Book borrowed successfully! Redirecting...";
        header("refresh:5;url=books_borrow.php");
    } else {
        $errorMessage = "❌ Could not borrow book. Try again.";
    }
    mysqli_stmt_close($stmt3);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Borrow Book | Student</title>
  <link rel="stylesheet" href="css/style.css">
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background-color: #f5f5dc;
      display: flex;
      color: #333;
    }

    .sidebar {
      position: fixed;
      left: 0;
      top: 0;
      width: 220px;
      height: 100vh;
      background-color: #5c4033;
      color: white;
      padding: 30px 20px;
      box-sizing: border-box;
    }

    .sidebar h3 {
      margin-top: 0;
      font-size: 20px;
      margin-bottom: 20px;
    }

    .sidebar a {
      display: block;
      color: #fff;
      text-decoration: none;
      margin: 12px 0;
      padding: 8px;
      border-radius: 5px;
      transition: background-color 0.2s ease;
    }

    .sidebar a:hover {
      background-color: #3e2c20;
    }

    .main-content {
      margin-left: 240px;
      padding: 40px;
      flex-grow: 1;
    }

    h2 {
      margin-top: 0;
    }

    .info-box, table {
      background-color: #fff;
      padding: 20px;
      border-radius: 12px;
      margin-bottom: 30px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    th, td {
      padding: 12px 16px;
      text-align: left;
      border-bottom: 1px solid #ddd;
    }

    th {
      background-color: #6b4f3b;
      color: white;
    }

    button {
      background-color: #6b4f3b;
      color: white;
      border: none;
      padding: 8px 14px;
      border-radius: 6px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    button:hover {
      background-color: #543c2d;
    }

    .borrowed {
      background-color: rgba(0, 0, 0, 0.6);
      color: white;
      pointer-events: none;
      font-style: italic;
    }

    .message {
      padding: 12px;
      margin-bottom: 15px;
      border-radius: 5px;
      font-weight: bold;
    }

    .success {
      background-color: #d4edda;
      color: #155724;
    }

    .error {
      background-color: #f8d7da;
      color: #721c24;
    }

  </style>
</head>
<body>

<div class="sidebar">
  <h3>📘 Library Menu</h3>
  <a href="student_dashboard.php">Dashboard</a>
  <a href="view_details.php">View Details</a>
  <a href="books_borrow.php">Book Details</a>
  <a href="return_book.php">Return Book</a>
  <a href="pages/logout.php">Logout</a>
</div>

<div class="main-content">
  <h2>📚 Borrow Book</h2>

  <?php if ($successMessage): ?>
    <div class="message success"><?= $successMessage ?></div>
  <?php endif; ?>
  <?php if ($errorMessage): ?>
    <div class="message error"><?= $errorMessage ?></div>
  <?php endif; ?>

  <!-- Search Form -->
  <form method="GET" action="books_borrow.php">
    <input type="text" name="search" placeholder="Search for books..." value="<?= htmlspecialchars($searchTerm) ?>">
    <button type="submit">Search</button>
  </form>

  <h3>Available Books</h3>
  <table>
    <tr>
      <th>Title</th>
      <th>Genre</th>
      <th>Borrow Date</th>
      <th>Return Date</th>
      <th>Action</th>
    </tr>
    <?php while ($row = mysqli_fetch_assoc($bookResult)) : 
      $today = date('Y-m-d');
      $due = date('Y-m-d', strtotime($today . ' +7 days'));
      $alreadyBorrowed = $row['already_borrowed'] > 0;
    ?>
      <tr class="<?= $alreadyBorrowed ? 'borrowed' : '' ?>">
        <td><?= htmlspecialchars($row['title']) ?></td>
        <td><?= htmlspecialchars($row['genre']) ?></td>
        <td><?= date('d M Y') ?></td>
        <td><?= date('d M Y', strtotime('+7 days')) ?></td>
        <td>
          <?php if ($alreadyBorrowed || $borrowedCount >= 2): ?>
            <span>Not Available</span>
          <?php else: ?>
            <form method="POST">
              <input type="hidden" name="book_id" value="<?= $row['id'] ?>">
              <input type="hidden" name="borrowed_date" value="<?= $today ?>">
              <button type="submit">Borrow</button>
            </form>
          <?php endif; ?>
        </td>
      </tr>
    <?php endwhile; ?>
  </table>
</div>

</body>
</html>
