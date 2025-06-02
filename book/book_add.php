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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $bookname = $_POST['bookname'];
    $author = $_POST['author'];
    $publisher = $_POST['publisher'];
    $pubdate = $_POST['pubdate'];
    $price = $_POST['price'];
    $content = $_POST['content'];

    $stmt = $conn->prepare("INSERT INTO book (bookname, author, publisher, pubdate, price, content) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssis", $bookname, $author, $publisher, $pubdate, $price, $content);

    if ($stmt->execute()) {
        header("Location: book_list.php");
        exit();
    } else {
        echo "新增失敗: " . $stmt->error;
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新增書籍</title>
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
            background-color: #28a745;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 4px;
            margin-top: 15px;
            cursor: pointer;
            width: 100%;
        }
        button:hover {
            background-color: #218838;
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
        <h2>新增書籍</h2>
        <form method="post">
            <label>書名:</label>
            <input type="text" name="bookname" required>
            
            <label>作者:</label>
            <input type="text" name="author" required>
            
            <label>出版社:</label>
            <input type="text" name="publisher" required>
            
            <label>出版日期:</label>
            <input type="date" name="pubdate" required>
            
            <label>定價:</label>
            <input type="number" name="price" required>
            
            <label>內容說明:</label>
            <textarea name="content" rows="4" required></textarea>
            
            <button type="submit">新增</button>
        </form>
        <a class="back-link" href="book_list.php">返回書籍列表</a>
    </div>
</body>
</html>