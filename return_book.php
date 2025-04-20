<?php
session_start();
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['student', 'teacher'])) {
    header('Location: pages/login.php');
    exit();
}

include('./includes/db.php');
$name = $_SESSION['user']['name'];

// Get user details
$userQuery = "SELECT * FROM users WHERE name = ?";
$stmt = mysqli_prepare($conn, $userQuery);
mysqli_stmt_bind_param($stmt, "s", $name);
mysqli_stmt_execute($stmt);
$userResult = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($userResult);
$userId = $user['id'];

// Get borrowed books
$borrowedQuery = "SELECT b.title, bb.book_id, bb.borrowed_date, bb.due_date, bb.returned_date 
                  FROM borrowed_books bb 
                  JOIN books b ON bb.book_id = b.id 
                  WHERE bb.user_id = ? AND bb.returned_date IS NULL";
$stmt2 = mysqli_prepare($conn, $borrowedQuery);
mysqli_stmt_bind_param($stmt2, "i", $userId);
mysqli_stmt_execute($stmt2);
$borrowedResult = mysqli_stmt_get_result($stmt2);

// Handle book return logic
$successMessage = '';
$warningMessage = '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['book_id'])) {
    $book_id = $_POST['book_id'];
    $today = date('Y-m-d');

    // Check borrowed books for the user
    $query = "SELECT due_date FROM borrowed_books WHERE user_id = ? AND book_id = ? AND returned_date IS NULL";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $userId, $book_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $due_date);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    if ($due_date) {
        $fine = 0;
        if ($today > $due_date) {
            $daysLate = (strtotime($today) - strtotime($due_date)) / (60 * 60 * 24);
            $fine = $daysLate * 5;
            $warningMessage = "⚠️ Delayed return! You will be fined ₹" . $fine . ".";
        }

        // Update the returned date
        $update = "UPDATE borrowed_books SET returned_date = ? WHERE user_id = ? AND book_id = ? AND returned_date IS NULL";
        $stmt2 = mysqli_prepare($conn, $update);
        mysqli_stmt_bind_param($stmt2, "sii", $today, $userId, $book_id);
        mysqli_stmt_execute($stmt2);
        mysqli_stmt_close($stmt2);

        $successMessage = "✅ Book successfully returned.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Return Book | Student</title>
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

    .info-box {
      background-color: #fff;
      padding: 20px;
      border-radius: 12px;
      margin-bottom: 30px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .info-box p {
      margin: 8px 0;
      font-size: 16px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      background-color: #fff;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
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

    tr:last-child td {
      border-bottom: none;
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

    .fine {
      font-weight: bold;
      color: darkred;
    }

    .no-fine {
      color: green;
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

    .warning {
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
  <h2>📄 Return Book</h2>

  <?php if ($successMessage): ?>
    <div class="message success"><?= $successMessage ?></div>
  <?php endif; ?>
  <?php if ($warningMessage): ?>
    <div class="message warning"><?= $warningMessage ?></div>
  <?php endif; ?>

  <h3>Borrowed Books</h3>
  <table>
    <tr>
      <th>Book Title</th>
      <th>Borrowed Date</th>
      <th>Due Date</th>
      <th>Returned Date</th>
      <th>Action</th>
    </tr>
    <?php while ($row = mysqli_fetch_assoc($borrowedResult)) : 
      $borrowDate = new DateTime($row['borrowed_date']);
      $dueDate = new DateTime($row['due_date']);
      $returnedDate = $row['returned_date'] ? new DateTime($row['returned_date']) : null;
    ?>
      <tr>
        <td><?= htmlspecialchars($row['title']) ?></td>
        <td><?= $borrowDate->format('d M Y') ?></td>
        <td><?= $dueDate->format('d M Y') ?></td>
        <td><?= $returnedDate ? $returnedDate->format('d M Y') : '-' ?></td>
        <td>
          <?php if (!$returnedDate): ?>
            <form method="POST">
              <input type="hidden" name="book_id" value="<?= $row['book_id'] ?>">
              <button type="submit">Return Book</button>
            </form>
          <?php else: ?>
            <span>Already Returned</span>
          <?php endif; ?>
        </td>
      </tr>
    <?php endwhile; ?>
  </table>
</div>

</body>
</html>
