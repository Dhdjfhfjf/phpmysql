<?php
session_start(); // 確保在程式的頂部啟用 Session

// 資料庫連線資訊 (與你的登入邏輯相同)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "school";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("連線失敗: " . $conn->connect_error);
}

// 檢查使用者是否已登入
function isLoggedIn() {
    return isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true;
}

// 處理登出邏輯
if (isset($_GET["logout"])) {
    session_unset();
    session_destroy();
    header("Location: ?");
    exit();
}

// 處理登入邏輯 (如果將登入表單整合在此檔案)
if (isset($_POST["login"])) {
    $username = $_POST["username"];
    $password = $_POST["password"];

    $sql = "SELECT id, username, password FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user["password"])) {
            $_SESSION["loggedin"] = true;
            $_SESSION["userid"] = $user["id"];
            $_SESSION["username"] = $user["username"];
            header("Location: index.php"); // 登入成功後重新導向主要頁面
            exit();
        } else {
            $login_error = "密碼錯誤！";
        }
    } else {
        $login_error = "使用者名稱不存在！";
    }
    $stmt->close();
}

// 設定每頁顯示的筆數
$recordsPerPage = 10;
$currentPage = isset($_GET["page"]) ? $_GET["page"] : 1;
$startFrom = ($currentPage - 1) * $recordsPerPage;

// 查詢資料總筆數
$sqlTotal = "SELECT COUNT(*) AS total FROM movie";
$resultTotal = $conn->query($sqlTotal);
$rowTotal = $resultTotal->fetch_assoc();
$totalRecords = $rowTotal["total"];
$totalPages = ceil($totalRecords / $recordsPerPage);

// 查詢資料
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
        .login-status { text-align: right; margin-bottom: 10px; }
        .login-status a { margin-left: 10px; text-decoration: none; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid black; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .pagination { text-align: center; margin-top: 20px; }
        .pagination a { display: inline-block; padding: 8px 12px; text-decoration: none; border: 1px solid #ccc; margin: 0 5px; }
        .pagination a.active { background-color: #4CAF50; color: white; border: 1px solid #4CAF50; }
        .pagination a:hover:not(.active) { background-color: #ddd; }
        .actions a { margin-right: 10px; text-decoration: none; }
        .add-button { text-align: center; margin-bottom: 20px; }
        .add-button a { background-color: #007bff; color: white; padding: 10px 15px; text-decoration: none; }
        .error { color: red; margin-top: 10px; text-align: center; }
        .view-only { color: gray; font-style: italic; }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-status">
            <?php if (isLoggedIn()): ?>
                歡迎，<?php echo $_SESSION["username"]; ?>！ <a href="?logout=true">登出</a>
            <?php else: ?>
                <a href="login.php">登入</a>
            <?php endif; ?>
        </div>

        <h2>電影列表</h2>

        <?php if (isset($login_error)): ?>
            <p class="error"><?php echo $login_error; ?></p>
        <?php endif; ?>

        <?php if (isLoggedIn()): ?>
            <div class="add-button">
                <a href="?action=add">新增電影</a>
            </div>
        <?php else: ?>
            <p class="view-only">請登入以進行資料新增、修改和刪除。</p>
        <?php endif; ?>

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
                        if (isLoggedIn()):
                            echo "<a href='?action=edit&id=" . $row["id"] . "'>修改</a>";
                            echo "<a href='?action=delete&id=" . $row["id"] . "' onclick='return confirm(\"確定要刪除這筆資料嗎？\")'>刪除</a>";
                        else:
                            echo "<span class='view-only'>登入後可操作</span>";
                        endif;
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
// 處理查看單筆資料 (需要判斷是否登入，但查看通常不需要)
if (isset($_GET["action"]) && $_GET["action"] == "view" && isset($_GET["id"])) {
    // ... (與之前的查看單筆資料邏輯相同) ...
    // 在查看詳細資料的頁面也可以提供返回列表的連結和登入/登出狀態
}

// 處理新增資料 (只有登入後才能執行)
if (isset($_POST["add"]) && isLoggedIn()) {
    // ... (與之前的新增資料邏輯相同) ...
}

// 顯示新增表單 (只有登入後才能顯示)
if (isset($_GET["action"]) && $_GET["action"] == "add" && isLoggedIn()) {
    // ... (與之前的新增表單邏輯相同) ...
    // 在新增表單頁面也可以提供返回列表的連結和登入/登出狀態
} elseif (isset($_GET["action"]) && $_GET["action"] == "add" && !isLoggedIn()) {
    echo "<p class='error' style='text-align: center;'>請先登入才能新增資料。</p><p style='text-align: center;'><a href='?'>返回列表</a></p>";
    exit();
}

// 處理修改資料 (只有登入後才能執行)
if (isset($_POST["edit"]) && isLoggedIn()) {
    // ... (與之前的修改資料邏輯相同) ...
}

// 顯示修改表單 (只有登入後才能顯示)
if (isset($_GET["action"]) && $_GET["action"] == "edit" && isset($_GET["id"]) && isLoggedIn()) {
    // ... (與之前的修改表單邏輯相同) ...
    // 在修改表單頁面也可以提供返回列表的連結和登入/登出狀態
} elseif (isset($_GET["action"]) && $_GET["action"] == "edit" && !isLoggedIn()) {
    echo "<p class='error' style='text-align: center;'>請先登入才能修改資料。</p><p style='text-align: center;'><a href='?'>返回列表</a></p>";
    exit();
}

// 處理刪除資料 (只有登入後才能執行)
if (isset($_GET["action"]) && $_GET["action"] == "delete" && isset($_GET["id"]) && isLoggedIn()) {
    // ... (與之前的刪除資料邏輯相同) ...
} elseif (isset($_GET["action"]) && $_GET["action"] == "delete" && !isLoggedIn()) {
    echo "<p class='error' style='text-align: center;'>請先登入才能刪除資料。</p><p style='text-align: center;'><a href='?'>返回列表</a></p>";
    exit();
}

// 關閉資料庫連線
$conn->close();
?>