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
$borrowedQuery = "SELECT b.title, bb.borrowed_date, bb.due_date, bb.returned_date 
                  FROM borrowed_books bb 
                  JOIN books b ON bb.book_id = b.id 
                  WHERE bb.user_id = ?";
$stmt2 = mysqli_prepare($conn, $borrowedQuery);
mysqli_stmt_bind_param($stmt2, "i", $userId);
mysqli_stmt_execute($stmt2);
$borrowedResult = mysqli_stmt_get_result($stmt2);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>View Details | Student</title>
  <link rel="stylesheet" href="css/style.css">
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background-color: #f5f5dc;
      display: flex;
    }

    .sidebar {
      position: fixed;
      left: 0;
      top: 0;
      width: 220px;
      height: 100vh;
      background-color: #5c4033;
      color: #fff;
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

    .fine {
      font-weight: bold;
      color: darkred;
    }

    .no-fine {
      color: green;
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
  <h2>📄 Student Details</h2>
  <div class="info-box">
    <p><strong>Name:</strong> <?= htmlspecialchars($user['name']) ?></p>
    <p><strong>Registration Number:</strong> <?= htmlspecialchars($user['regno']) ?></p>
    <p><strong>Email ID:</strong> <?= htmlspecialchars($user['email']) ?></p>
  </div>

  <h2>📚 Borrowed Book History</h2>
  <table>
    <tr>
      <th>Book Title</th>
      <th>Borrowed Date</th>
      <th>Due Date</th>
      <th>Returned Date</th>
      <th>Status</th>
      <th>Fine</th>
    </tr>
    <?php while ($row = mysqli_fetch_assoc($borrowedResult)) : 
      $borrowDate = new DateTime($row['borrowed_date']);
      $dueDate = new DateTime($row['due_date']);
      $returnedDate = $row['returned_date'] ? new DateTime($row['returned_date']) : null;

      $status = "Not Returned";
      $fine = 0;

      if ($returnedDate) {
        if ($returnedDate > $dueDate) {
          $status = "Returned Late";
          $daysLate = $dueDate->diff($returnedDate)->days;
          $fine = $daysLate * 5;
        } else {
          $status = "Returned On Time";
        }
      } else {
        $now = new DateTime();
        if ($now > $dueDate) {
          $status = "Overdue";
          $fine = $dueDate->diff($now)->days * 5;
        }
      }
    ?>
      <tr>
        <td><?= htmlspecialchars($row['title']) ?></td>
        <td><?= $borrowDate->format('d M Y') ?></td>
        <td><?= $dueDate->format('d M Y') ?></td>
        <td><?= $returnedDate ? $returnedDate->format('d M Y') : '-' ?></td>
        <td><?= $status ?></td>
        <td class="<?= $fine > 0 ? 'fine' : 'no-fine' ?>">
          <?= $fine > 0 ? "₹$fine" : "No Fine" ?>
        </td>
      </tr>
    <?php endwhile; ?>
  </table>
</div>

</body>
</html>
