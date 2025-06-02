<?php
session_start();

// Include config file
require_once "dbconfig.php";

function loginOK() {
    return (isset($_SESSION["loggedin"]) && ($_SESSION["loggedin"]===true));
}

// 建立連線
$conn = new mysqli($hostname, $dbuser, $dbpass, $database);

// 檢查連線
if ($conn->connect_error) {
    die("連線失敗: " . $conn->connect_error);
}

// 設定編碼，確保中文顯示正常
$conn->set_charset("utf8mb4");

// 刪除書籍處理
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM book WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: book_list.php");
    exit();
}

// 取得書籍列表
$result = $conn->query("SELECT * FROM book");
$conn->close();
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>書籍管理</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css">
    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid black; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        h1 {text-align:center;}
    </style>
</head>
<body>
    <h1>書籍管理</h1>

    <p>
    <?php if (loginOK()) { ?>
        <a class="btn btn-success" href="#" id="logout">Logout</a>
        管理者: <?= $_SESSION["username"]; ?>
    <?php } else { ?>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#loginModal">
            登入管理
        </button>
        
    <?php } ?> 
    </p>

    <table>
        <tr>
            <th>ID</th>
            <th>書名</th>
            <th>作者</th>
            <th>出版社</th>
            <th>出版日期</th>
            <th>定價</th>
            <th>內容說明</th>
            <th>操作
            <?php if (loginOK()) { ?>
                <a href="book_add.php">新增</a>
            <?php } ?> 

            </th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row["id"]; ?></td>
            <td><?php echo $row["bookname"]; ?></td>
            <td><?php echo $row["author"]; ?></td>
            <td><?php echo $row["publisher"]; ?></td>
            <td><?php echo $row["pubdate"]; ?></td>
            <td><?php echo $row["price"]; ?></td>
            <td><?php echo nl2br($row["content"]); ?></td>
            <td>
                <a href="book_detail.php?id=<?php echo $row['id']; ?>">查看</a>
                <?php if (loginOK()) { ?>
                    <a href="book_edit.php?id=<?php echo $row['id']; ?>">修改</a>
                    <a href="?delete=<?php echo $row['id']; ?>" onclick="return confirm('確定刪除?');">刪除</a>
                <?php } ?> 
            </td>
        </tr>
        <?php endwhile; ?>
    </table>





<!-- Modal -->
<div class="modal fade" id="loginModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">登入管理</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form action="#" method="post">
                    <div class="form-floating m-1">
                        <input type="text" class="form-control" name="username" id="username" placeholder="User Name" required="required">
                        <label for="username">User Name</label>
                    </div>
                    <div class="form-floating m-1">
                        <input type="password" class="form-control" name="userpass" id="userpass" placeholder="Password" required="required">
                        <label for="userpass">Password</label>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="login_button">登入系統</button>
                
            </div>

        </div>
    </div>
</div>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>

<!-- 透過 CDN 載入 jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function () {

    // 執行登入認證
    $('#login_button').click(function () {

        // 取出登入表單中，使用者帳號密碼的輸入值
        var username = $('#username').val();
        var userpass = $('#userpass').val();

        // alert("username"+username+ " userpass"+userpass);

        if (username != '' && userpass != '') {
            $.ajax({
                url: "action.php",
                method: "POST",
                data: {
                    "action": "login",
                    "username": username,
                    "userpass": userpass
                },

                success: function (data) {
                    if (data == 'Yes') {
                        location.reload();
                        alert("成功登入系統...");
                    } else {
                        // location.reload();
                        alert('帳密無法使用!');
                    }
                },

                error: function (data) {
                    alter('無法登入');
                }
            });
        } else {
            alert("兩個欄位都要填寫!");
        }
    });

    // 執行登出
    $('#logout').click(function () {
        $.ajax({
            url: "action.php",
            method: "POST",
            data: {
                "action": "logout",
            },
            success: function () {
                location.reload();
                alert("您已登出本系統...");
            }
        });
    });
});
</script>
</body>
</html>
