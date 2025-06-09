<?php
session_start();

function loginOK() {
    return (isset($_SESSION["loggedin"]) && ($_SESSION["loggedin"]===true));
}

if (!loginOK()) { 
    header("location: login.php");
    exit();
}

require_once "dbconfig.php";

$conn = new mysqli($hostname, $dbuser, $dbpass, $database);

if ($conn->connect_error) {
    die("連線失敗: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = (int)$_POST['id'];
    $title = $_POST['title'];
    $year = $_POST['year'];
    $director = $_POST['director'];
    $mtype = $_POST['mtype'];
    $mdate = $_POST['mdate'];
    $content = $_POST['content'];

    $stmt = $conn->prepare("UPDATE movie SET title=?, year=?, director=?, mtype=?, mdate=?, content=? WHERE id=?");
    $stmt->bind_param("sissssi", $title, $year, $director, $mtype, $mdate, $content, $id);

    if ($stmt->execute()) {
        header("Location: movie_list.php");
        exit();
    } else {
        echo "更新失敗: " . $stmt->error;
    }
    $stmt->close();
}

$stmt = $conn->prepare("SELECT * FROM movie WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$movie = $result->fetch_assoc();

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>修改電影資料</title>
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
        <h2>修改電影資料</h2>
        <?php if ($movie): ?>
            <form method="post">
                <input type="hidden" name="id" value="<?php echo $movie['id']; ?>">

                <label>片名:</label>
                <input type="text" name="title" value="<?php echo htmlspecialchars($movie['title']); ?>" required>

                <label>上映年份:</label>
                <input type="number" name="year" value="<?php echo htmlspecialchars($movie['year']); ?>" required>

                <label>導演:</label>
                <input type="text" name="director" value="<?php echo htmlspecialchars($movie['director']); ?>" required>

                <label>類型:</label>
                <input type="text" name="mtype" value="<?php echo htmlspecialchars($movie['mtype']); ?>" required>

                <label>上映日期:</label>
                <input type="date" name="mdate" value="<?php echo $movie['mdate']; ?>" required>

                <label>內容介紹:</label>
                <textarea name="content" rows="4" required><?php echo htmlspecialchars($movie['content']); ?></textarea>

                <button type="submit">儲存</button>
            </form>
        <?php else: ?>
            <p>找不到該電影資料。</p>
        <?php endif; ?>
        <a class="back-link" href="movie_list.php">返回電影列表</a>
    </div>
</body>
</html>
