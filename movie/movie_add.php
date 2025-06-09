<?php
session_start();

function loginOK() {
    return (isset($_SESSION["loggedin"]) && ($_SESSION["loggedin"]===true));
}

if (!loginOK()) { 
    header("location: login.php");
    exit();
}

// Include config file
require_once "dbconfig.php";

$conn = new mysqli($hostname, $dbuser, $dbpass, $database);
if ($conn->connect_error) {
    die("連線失敗: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $year = $_POST['year'];
    $director = $_POST['director'];
    $mtype = $_POST['mtype'];
    $mdate = $_POST['mdate'];
    $content = $_POST['content'];

    $stmt = $conn->prepare("INSERT INTO movie (title, year, director, mtype, mdate, content) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sissss", $title, $year, $director, $mtype, $mdate, $content);

    if ($stmt->execute()) {
        header("Location: movie_list.php");
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
    <title>新增電影</title>
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
        <h2>新增電影</h2>
        <form method="post">
            <label>片名:</label>
            <input type="text" name="title" required>

            <label>年份:</label>
            <input type="number" name="year" required>

            <label>導演:</label>
            <input type="text" name="director" required>

            <label>類型:</label>
            <input type="text" name="mtype" required>

            <label>上映日期:</label>
            <input type="date" name="mdate" required>

            <label>內容說明:</label>
            <textarea name="content" rows="4" required></textarea>

            <button type="submit">新增</button>
        </form>
        <a class="back-link" href="movie_list.php">返回電影列表</a>
    </div>
</body>
</html>
