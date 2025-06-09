<?php

// 資料庫連線資訊
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "school";

// 建立資料庫連線
$conn = new mysqli($servername, $username, $password, $dbname);

// 檢查連線是否成功
if ($conn->connect_error) {
    die("連線失敗: " . $conn->connect_error);
}

// 設定每頁顯示的筆數
$recordsPerPage = 10;

// 取得目前頁碼
$currentPage = isset($_GET["page"]) ? $_GET["page"] : 1;
$startFrom = ($currentPage - 1) * $recordsPerPage;

// 處理查看單筆資料
if (isset($_GET["action"]) && $_GET["action"] == "view" && isset($_GET["id"])) {
    $id = $_GET["id"];
    $sql = "SELECT * FROM movie WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $movie = $result->fetch_assoc();
    $stmt->close();
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>電影詳細資料</title>
        <style>
            body { font-family: Arial, sans-serif; }
            .container { width: 80%; margin: 20px auto; border: 1px solid #ccc; padding: 20px; }
            h2 { text-align: center; }
            p { margin-bottom: 10px; }
            a { text-decoration: none; }
            .back-link { display: block; margin-top: 20px; text-align: center; }
        </style>
    </head>
    <body>
        <div class="container">
            <h2>電影詳細資料</h2>
            <?php if ($movie): ?>
                <p><strong>ID:</strong> <?php echo $movie["id"]; ?></p>
                <p><strong>電影名稱:</strong> <?php echo $movie["title"]; ?></p>
                <p><strong>發行年份:</strong> <?php echo $movie["year"]; ?></p>
                <p><strong>導演:</strong> <?php echo $movie["director"]; ?></p>
                <p><strong>類型:</strong> <?php echo $movie["mtype"]; ?></p>
                <p><strong>首映日期:</strong> <?php echo $movie["mdate"]; ?></p>
                <p><strong>內容簡介:</strong> <?php echo nl2br($movie["content"]); ?></p>
            <?php else: ?>
                <p>找不到該筆資料。</p>
            <?php endif; ?>
            <p class="back-link"><a href="?">返回列表</a></p>
        </div>
    </body>
    </html>
    <?php
    exit();
}

// 處理新增資料
if (isset($_POST["add"])) {
    $title = $_POST["title"];
    $year = $_POST["year"];
    $director = $_POST["director"];
    $mtype = $_POST["mtype"];
    $mdate = $_POST["mdate"];
    $content = $_POST["content"];

    $sql = "INSERT INTO movie (title, year, director, mtype, mdate, content) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sissss", $title, $year, $director, $mtype, $mdate, $content);
    if ($stmt->execute()) {
        header("Location: ?"); // 新增成功後重新導向列表頁面
        exit();
    } else {
        echo "新增資料失敗: " . $stmt->error;
    }
    $stmt->close();
}

// 顯示新增表單
if (isset($_GET["action"]) && $_GET["action"] == "add") {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>新增電影</title>
        <style>
            body { font-family: Arial, sans-serif; }
            .container { width: 60%; margin: 20px auto; border: 1px solid #ccc; padding: 20px; }
            h2 { text-align: center; }
            label { display: block; margin-bottom: 5px; }
            input[type="text"], input[type="number"], input[type="date"], textarea { width: 100%; padding: 8px; margin-bottom: 10px; border: 1px solid #ddd; box-sizing: border-box; }
            textarea { height: 150px; }
            button[type="submit"], .back-link a { background-color: #4CAF50; color: white; padding: 10px 15px; border: none; cursor: pointer; text-decoration: none; display: inline-block; margin-top: 10px; }
            .back-link { text-align: center; margin-top: 20px; }
        </style>
    </head>
    <body>
        <div class="container">
            <h2>新增電影</h2>
            <form method="post">
                <label for="title">電影名稱:</label>
                <input type="text" name="title" required>

                <label for="year">發行年份:</label>
                <input type="number" name="year" required>

                <label for="director">導演:</label>
                <input type="text" name="director" required>

                <label for="mtype">類型:</label>
                <input type="text" name="mtype" required>

                <label for="mdate">首映日期:</label>
                <input type="date" name="mdate" required>

                <label for="content">內容簡介:</label>
                <textarea name="content" required></textarea>

                <button type="submit" name="add">新增</button>
            </form>
            <p class="back-link"><a href="?">返回列表</a></p>
        </div>
    </body>
    </html>
    <?php
    exit();
}

// 處理修改資料
if (isset($_POST["edit"])) {
    $id = $_POST["id"];
    $title = $_POST["title"];
    $year = $_POST["year"];
    $director = $_POST["director"];
    $mtype = $_POST["mtype"];
    $mdate = $_POST["mdate"];
    $content = $_POST["content"];

    $sql = "UPDATE movie SET title=?, year=?, director=?, mtype=?, mdate=?, content=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sissssi", $title, $year, $director, $mtype, $mdate, $content, $id);
    if ($stmt->execute()) {
        header("Location: ?"); // 修改成功後重新導向列表頁面
        exit();
    } else {
        echo "修改資料失敗: " . $stmt->error;
    }
    $stmt->close();
}

// 顯示修改表單
if (isset($_GET["action"]) && $_GET["action"] == "edit" && isset($_GET["id"])) {
    $id = $_GET["id"];
    $sql = "SELECT * FROM movie WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $movie = $result->fetch_assoc();
    $stmt->close();

    if (!$movie) {
        echo "找不到該筆資料。";
        exit();
    }
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>修改電影</title>
        <style>
            body { font-family: Arial, sans-serif; }
            .container { width: 60%; margin: 20px auto; border: 1px solid #ccc; padding: 20px; }
            h2 { text-align: center; }
            label { display: block; margin-bottom: 5px; }
            input[type="text"], input[type="number"], input[type="date"], textarea { width: 100%; padding: 8px; margin-bottom: 10px; border: 1px solid #ddd; box-sizing: border-box; }
            textarea { height: 150px; }
            button[type="submit"], .back-link a { background-color: #007bff; color: white; padding: 10px 15px; border: none; cursor: pointer; text-decoration: none; display: inline-block; margin-top: 10px; }
            .back-link { text-align: center; margin-top: 20px; }
        </style>
    </head>
    <body>
        <div class="container">
            <h2>修改電影</h2>
            <form method="post">
                <input type="hidden" name="id" value="<?php echo $movie["id"]; ?>">
                <label for="title">電影名稱:</label>
                <input type="text" name="title" value="<?php echo $movie["title"]; ?>" required>

                <label for="year">發行年份:</label>
                <input type="number" name="year" value="<?php echo $movie["year"]; ?>" required>

                <label for="director">導演:</label>
                <input type="text" name="director" value="<?php echo $movie["director"]; ?>" required>

                <label for="mtype">類型:</label>
                <input type="text" name="mtype" value="<?php echo $movie["mtype"]; ?>" required>

                <label for="mdate">首映日期:</label>
                <input type="date" name="mdate" value="<?php echo $movie["mdate"]; ?>" required>

                <label for="content">內容簡介:</label>
                <textarea name="content" required><?php echo $movie["content"]; ?></textarea>

                <button type="submit" name="edit">儲存修改</button>
            </form>
            <p class="back-link"><a href="?">返回列表</a></p>
        </div>
    </body>
    </html>
    <?php
    exit();
}

// 處理刪除資料
if (isset($_GET["action"]) && $_GET["action"] == "delete" && isset($_GET["id"])) {
    $id = $_GET["id"];
    $sql = "DELETE FROM movie WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header("Location: ?"); // 刪除成功後重新導向列表頁面
        exit();
    } else {
        echo "刪除資料失敗: " . $stmt->error;
    }
    $stmt->close();
}

// 顯示資料列表及換頁
$sqlTotal = "SELECT COUNT(*) AS total FROM movie";
$resultTotal = $conn->query($sqlTotal);
$rowTotal = $resultTotal->fetch_assoc();
$totalRecords = $rowTotal["total"];
$totalPages = ceil($totalRecords / $recordsPerPage);

$sql = "SELECT id, title, year, director, mtype, mdate FROM movie LIMIT $startFrom, $recordsPerPage";
$result = $conn->query($sql);

?>
<!DOCTYPE html>
<html>
<head>
    <title>電影列表</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .container { width: 80%; margin: 20px auto; }
        h2 { text-align: center; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid black; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .pagination { text-align: center; margin-top: 20px; }
        .pagination a { display: inline-block; padding: 8px 12px; text-decoration: none; border: 1px solid #ccc; margin: 0 5px; }
        .pagination a.active { background-color: #4CAF50; color: white; border: 1px solid #4CAF50; }
        .pagination a:hover:not(.active) { background-color: #ddd; }
        .actions a { margin-right: 10px; text-decoration: none; }
        .add-button { display: block; text-align: center; margin-bottom: 20px; }
        .add-button a { background-color: #007bff; color: white; padding: 10px 15px; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <h2>電影列表</h2>
        <div class="add-button">
            <a href="?action=add">新增電影</a>
        </div>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>電影名稱</th>
                    <th>發行年份</th>
                    <th>導演</th>
                    <th>類型</th>
                    <th>首映日期</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row["id"]. "</td>";
                        echo "<td>" . $row["title"]. "</td>";
                        echo "<td>" . $row["year"]. "</td>";
                        echo "<td>" . $row["director"]. "</td>";
                        echo "<td>" . $row["mtype"]. "</td>";
                        echo "<td>" . $row["mdate"]. "</td>";
                        echo "<td class='actions'>";
                        echo "<a href='?action=view&id=" . $row["id"] . "'>查看</a>";
                        echo "<a href='?action=edit&id=" . $row["id"] . "'>修改</a>";
                        echo "<a href='?action=delete&id=" . $row["id"] . "' onclick='return confirm(\"確定要刪除這筆資料嗎？\")'>刪除</a>";
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>沒有資料</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <div class="pagination">
            <?php
            for ($i = 1; $i <= $totalPages; $i++) {
                if ($i == $currentPage) {
                    echo "<a class='active' href='?page=" . $i . "'>" . $i . "</a>";
                } else {
                    echo "<a href='?page=" . $i . "'>" . $i . "</a>";
                }
            }
            ?>
        </div>
    </div>
</body>
</html>

<?php
// 關閉資料庫連線
$conn->close();
?>