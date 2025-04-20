<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'teacher') {
    header('Location: pages/login.php');
    exit();
}

include('./includes/db.php');

$query = "
    SELECT 
        u.name AS student_name,
        u.regno,
        b.title AS book_title,
        bb.borrowed_date,
        bb.due_date,
        bb.returned_date
    FROM users u
    JOIN borrowed_books bb ON u.id = bb.user_id
    JOIN books b ON bb.book_id = b.id
    ORDER BY u.name, bb.borrowed_date DESC
";

$result = mysqli_query($conn, $query);

$students = [];

while ($row = mysqli_fetch_assoc($result)) {
    $studentKey = $row['regno'];

    if (!isset($students[$studentKey])) {
        $students[$studentKey] = [
            'name' => $row['student_name'],
            'regno' => $row['regno'],
            'borrowed_books' => [],
        ];
    }

    $students[$studentKey]['borrowed_books'][] = [
        'title' => $row['book_title'],
        'borrowed_date' => $row['borrowed_date'],
        'due_date' => $row['due_date'],
        'returned_date' => $row['returned_date'],
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Student Details | Library</title>
  <link rel="stylesheet" href="css/style.css">
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background-color: #f4f4f4;
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

    .student-block {
      background: #fff;
      border-radius: 10px;
      padding: 20px;
      margin-bottom: 30px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }

    .student-block h3 {
      margin-top: 0;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }

    table th, table td {
      padding: 10px;
      border: 1px solid #ccc;
      text-align: left;
    }

    table th {
      background-color: #eee;
    }

    @media (max-width: 768px) {
      .sidebar {
        position: static;
        width: 100%;
        height: auto;
      }

      .main-content {
        margin-left: 0;
      }
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
  <a href="student_details.php">Student Details</a>
  <a href="pages/logout.php">Logout</a>
</div>

<div class="main-content">
  <h2>📋 Students in Library & Borrowing History</h2>

  <?php foreach ($students as $student): ?>
    <div class="student-block">
      <h3><?= htmlspecialchars($student['name']) ?> (<?= htmlspecialchars($student['regno']) ?>)</h3>
      <p><strong>Total Books Borrowed:</strong> <?= count($student['borrowed_books']) ?></p>
      <table>
        <thead>
          <tr>
            <th>Book Title</th>
            <th>Borrowed Date</th>
            <th>Due Date</th>
            <th>Returned Date</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($student['borrowed_books'] as $book): ?>
          <tr>
            <td><?= htmlspecialchars($book['title']) ?></td>
            <td><?= htmlspecialchars($book['borrowed_date']) ?></td>
            <td><?= htmlspecialchars($book['due_date']) ?></td>
            <td>
              <?= $book['returned_date'] ? htmlspecialchars($book['returned_date']) : '<span style="color:red">Not Returned</span>' ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endforeach; ?>
</div>

</body>
</html>
