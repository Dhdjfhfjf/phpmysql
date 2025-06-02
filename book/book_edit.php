<?php
session_start();

function loginOK() {
    return (isset($_SESSION["loggedin"]) && ($_SESSION["loggedin"]===true));
}

if (!loginOK()) { 
    header("location: login.php");
}

// Include config file
require_once "dbconfig.php";

$conn = new mysqli($hostname, $dbuser, $dbpass, $database);

if ($conn->connect_error) {
    die("連線失敗: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = (int)$_POST['id'];
    $bookname = $_POST['bookname'];
    $author = $_POST['author'];
    $publisher = $_POST['publisher'];
    $pubdate = $_POST['pubdate'];
    $price = $_POST['price'];
    $content = $_POST['content'];

    $stmt = $conn->prepare("UPDATE book SET bookname=?, author=?, publisher=?, pubdate=?, price=?, content=? WHERE id=?");
    $stmt->bind_param("ssssisi", $bookname, $author, $publisher, $pubdate, $price, $content, $id);

    if ($stmt->execute()) {
        header("Location: book_list.php");
        exit();
    } else {
        echo "更新失敗: " . $stmt->error;
    }
    $stmt->close();
}

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
    <title>修改書籍</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 20px;
            background-color: #f9f9f9;
        }
        .container {
            max-width: 500px;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            margin: auto;
        }
        label {
            font-weight: bold;
            display: block;
            margin-top: 10px;
        }
        input, textarea {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            background-color: #007bff;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 4px;
            margin-top: 15px;
            cursor: pointer;
            width: 100%;
        }
        button:hover {
            background-color: #0056b3;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 10px;
            color: #007bff;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>修改書籍</h2>
        <?php if ($book): ?>
            <form method="post">
                <input type="hidden" name="id" value="<?php echo $book['id']; ?>">
                
                <label>書名:</label>
                <input type="text" name="bookname" value="<?php echo htmlspecialchars($book['bookname']); ?>" required>
                
                <label>作者:</label>
                <input type="text" name="author" value="<?php echo htmlspecialchars($book['author']); ?>" required>
                
                <label>出版社:</label>
                <input type="text" name="publisher" value="<?php echo htmlspecialchars($book['publisher']); ?>" required>
                
                <label>出版日期:</label>
                <input type="date" name="pubdate" value="<?php echo $book['pubdate']; ?>" required>
                
                <label>定價:</label>
                <input type="number" name="price" value="<?php echo $book['price']; ?>" required>
                
                <label>內容說明:</label>
                <textarea name="content" rows="4" required><?php echo htmlspecialchars($book['content']); ?></textarea>
                
                <button type="submit">儲存</button>
            </form>
        <?php else: ?>
            <p>找不到該書籍。</p>
        <?php endif; ?>
        <a class="back-link" href="book_list.php">返回書籍列表</a>
    </div>
</body>
</html>
