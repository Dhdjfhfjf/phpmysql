<?php
session_start(); // 啟用 Session

// 資料庫連線資訊 (與你的主要程式相同)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "school";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("連線失敗: " . $conn->connect_error);
}

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

// 登出邏輯
if (isset($_GET["logout"])) {
    session_unset();
    session_destroy();
    header("Location: ?"); // 登出後重新導向主要頁面
    exit();
}

// 檢查使用者是否已登入
function isLoggedIn() {
    return isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>使用者登入</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .container { width: 300px; margin: 50px auto; border: 1px solid #ccc; padding: 20px; }
        h2 { text-align: center; }
        label { display: block; margin-bottom: 5px; }
        input[type="text"], input[type="password"] { width: 100%; padding: 8px; margin-bottom: 10px; border: 1px solid #ddd; box-sizing: border-box; }
        button[type="submit"] { background-color: #007bff; color: white; padding: 10px 15px; border: none; cursor: pointer; width: 100%; }
        .error { color: red; margin-top: 10px; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <h2>使用者登入</h2>
        <?php if (isset($login_error)): ?>
            <p class="error"><?php echo $login_error; ?></p>
        <?php endif; ?>
        <form method="post" action="">
            <label for="username">使用者名稱:</label>
            <input type="text" name="username" required>

            <label for="password">密碼:</label>
            <input type="password" name="password" required>

            <button type="submit" name="login">登入</button>
        </form>
    </div>
</body>
</html>