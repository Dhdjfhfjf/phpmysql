<?php
// Include config file
require_once "dbconfig.php";

$conn = new mysqli($hostname, $dbuser, $dbpass, $database);

if ($conn->connect_error) {
    die("連線失敗: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $conn->prepare("SELECT * FROM book WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$book = $result->fetch_assoc();

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>書籍詳細資訊</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 20px;
            background-color: #f9f9f9;
        }
        .container {
            max-width: 600px;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            margin: auto;
        }
        h2 {
            text-align: center;
        }
        .info {
            margin-bottom: 15px;
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .info label {
            font-weight: bold;
            display: block;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #007bff;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>書籍詳細資訊</h2>
        <?php if ($book): ?>
            <div class="info"><label>書名:</label> <?php echo htmlspecialchars($book['bookname']); ?></div>
            <div class="info"><label>作者:</label> <?php echo htmlspecialchars($book['author']); ?></div>
            <div class="info"><label>出版社:</label> <?php echo htmlspecialchars($book['publisher']); ?></div>
            <div class="info"><label>出版日期:</label> <?php echo $book['pubdate']; ?></div>
            <div class="info"><label>定價:</label> <?php echo $book['price']; ?> 元</div>
            <div class="info"><label>內容說明:</label> <?php echo nl2br(htmlspecialchars($book['content'])); ?></div>
        <?php else: ?>
            <p>找不到該書籍。</p>
        <?php endif; ?>
        <a class="back-link" href="book_list.php">返回書籍列表</a>
    </div>
</body>
</html>
