<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: pages/login.php');
    exit();
}

include('./includes/db.php');

$name = $_SESSION['user']['name'];
$role = $_SESSION['user']['role']; // added this so we can use $role

$allBooksQuery = "SELECT * FROM books";
$allBooks = mysqli_query($conn, $allBooksQuery);

$topBooksQuery = "SELECT * FROM books ORDER BY rating DESC LIMIT 10";
$topBooks = mysqli_query($conn, $topBooksQuery);

// Get borrowed books by current user
$borrowedBooksQuery = "SELECT book_id FROM borrowed_books WHERE returned_date IS NULL";
$borrowedBooksResult = mysqli_query($conn, $borrowedBooksQuery);
$borrowedBookIds = [];
while ($row = mysqli_fetch_assoc($borrowedBooksResult)) {
    $borrowedBookIds[] = $row['book_id'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Student Dashboard | Library</title>
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

    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .search-filter {
      display: flex;
      gap: 15px;
      margin: 30px 0;
    }

    input[type="text"], select {
      padding: 8px 12px;
      border-radius: 6px;
      border: 1px solid #aaa;
      font-size: 14px;
    }

    .section-title {
      margin-top: 30px;
      font-size: 22px;
    }

    .book-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
      gap: 20px;
    }

    .top-rated {
      display: flex;
      overflow-x: auto;
      gap: 20px;
      padding-bottom: 20px;
    }

    .book-card {
      position: relative;
      overflow: hidden;
      border-radius: 12px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
      cursor: pointer;
      min-width: 180px;
    }

    .book-card img {
      width: 100%;
      height: 260px;
      object-fit: cover;
      border-radius: 12px;
    }

    .overlay {
      position: absolute;
      top: 0;
      left: 0;
      height: 100%;
      width: 100%;
      background-color: rgba(0, 0, 0, 0.85);
      color: #fff;
      opacity: 0;
      transition: opacity 0.3s ease;
      padding: 20px;
      border-radius: 12px;
    }

    .book-card:hover .overlay {
      opacity: 1;
    }

    .overlay h3 {
      margin-top: 0;
      font-size: 18px;
    }

    .overlay p {
      font-size: 14px;
      margin: 6px 0;
    }

    .borrowed-cover {
      position: absolute;
      top: 0;
      left: 0;
      height: 100%;
      width: 100%;
      background-color: rgba(0, 0, 0, 0.6);
      color: white;
      display: flex;
      justify-content: center;
      align-items: center;
      font-size: 18px;
      font-weight: bold;
      border-radius: 12px;
      z-index: 5;
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
  <?php if ($role === 'teacher'): ?>
    <a href="student_details.php">Student Details</a>
  <?php endif; ?>
  <a href="pages/logout.php">Logout</a>
</div>


<div class="main-content">
  <div class="header">
    <h2>Welcome, <?php echo htmlspecialchars($name); ?> 👋</h2>
  </div>

  <div class="search-filter">
    <input type="text" id="searchInput" placeholder="Search books by title...">
    <select id="genreSelect">
      <option value="">All Genres</option>
      <option value="Fiction">Fiction</option>
      <option value="Non-fiction">Non-fiction</option>
      <option value="Fantasy">Fantasy</option>
      <option value="Science Fiction">Science Fiction</option>
      <option value="Mystery">Mystery</option>
      <option value="Biography">Biography</option>
      <option value="History">History</option>
      <option value="Self-help">Self-help</option>
      <option value="Romance">Romance</option>
      <option value="Dystopian">Dystopian</option>
      <option value="Indian Classics">Indian Classics</option>
    </select>
  </div>

  <h3 class="section-title">🌟 Top Rated Books</h3>
  <div class="top-rated" id="topRatedBooks">
    <?php while($top = mysqli_fetch_assoc($topBooks)) : ?>
      <div class="book-card"
           data-title="<?= strtolower($top['title']) ?>"
           data-genre="<?= $top['genre'] ?>">
        <img src="assets/books/<?= htmlspecialchars($top['cover_image']) ?>" alt="<?= htmlspecialchars($top['title']) ?>">
        <?php if (in_array($top['id'], $borrowedBookIds)) : ?>
          <div class="borrowed-cover">Borrowed</div>
        <?php endif; ?>
        <div class="overlay">
          <h3><?= htmlspecialchars($top['title']) ?></h3>
          <p><strong>Author:</strong> <?= htmlspecialchars($top['author']) ?></p>
          <p><strong>Genre:</strong> <?= htmlspecialchars($top['genre']) ?></p>
          <p><strong>Rating:</strong> ⭐ <?= number_format($top['rating'], 1) ?>/5</p>
          <p><?= htmlspecialchars($top['description']) ?></p>
        </div>
      </div>
    <?php endwhile; ?>
  </div>

  <h3 class="section-title">📚 Explore All Books</h3>
  <div class="book-grid" id="bookGrid">
    <?php mysqli_data_seek($allBooks, 0); while($book = mysqli_fetch_assoc($allBooks)) : ?>
      <div class="book-card"
           data-title="<?= strtolower($book['title']) ?>"
           data-genre="<?= $book['genre'] ?>">
        <img src="assets/books/<?= htmlspecialchars($book['cover_image']) ?>" alt="<?= htmlspecialchars($book['title']) ?>">
        <?php if (in_array($book['id'], $borrowedBookIds)) : ?>
          <div class="borrowed-cover">Borrowed</div>
        <?php endif; ?>
        <div class="overlay">
          <h3><?= htmlspecialchars($book['title']) ?></h3>
          <p><strong>Author:</strong> <?= htmlspecialchars($book['author']) ?></p>
          <p><strong>Genre:</strong> <?= htmlspecialchars($book['genre']) ?></p>
          <p><strong>Rating:</strong> ⭐ <?= number_format($book['rating'], 1) ?>/5</p>
          <p><?= htmlspecialchars($book['description']) ?></p>
        </div>
      </div>
    <?php endwhile; ?>
  </div>
</div>

<script>
  const searchInput = document.getElementById('searchInput');
  const genreSelect = document.getElementById('genreSelect');
  const allBooks = document.querySelectorAll('.book-card');

  function filterBooks() {
    const keyword = searchInput.value.toLowerCase();
    const selectedGenre = genreSelect.value;

    allBooks.forEach(book => {
      const title = book.getAttribute('data-title');
      const genre = book.getAttribute('data-genre');

      const titleMatch = title.includes(keyword);
      const genreMatch = selectedGenre === '' || genre === selectedGenre;

      book.style.display = (titleMatch && genreMatch) ? 'block' : 'none';
    });
  }

  searchInput.addEventListener('input', filterBooks);
  genreSelect.addEventListener('change', filterBooks);
</script>

</body>
</html>
