<?php
$servername = "localhost"; // 您的資料庫主機名稱
$username = "root"; // 您的資料庫使用者名稱
$password = ""; // 您的資料庫密碼
$dbname = "school"; // 您的資料庫名稱

// 建立資料庫連接
$conn = new mysqli($servername, $username, $password, $dbname);

// 檢查連接是否成功
if ($conn->connect_error) {
    die("資料庫連接失敗: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) && is_numeric($_GET['id']) ? intval($_GET['id']) : 0;
$message = isset($_GET['message']) ? $_GET['message'] : '';
$error = [];

// 處理新增資料
if ($action == 'add' && $_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $year = trim($_POST['year']);
    $director = trim($_POST['director']);
    $mtype = trim($_POST['mtype']);
    $mdate = trim($_POST['mdate']);
    $content = trim($_POST['content']);

    if (empty($title)) { $error['title'] = "電影名稱不得為空。"; }
    if (!is_numeric($year) || strlen($year) != 4) { $error['year'] = "發行年代必須為四位數字。"; }
    if (empty($director)) { $error['director'] = "導演不得為空。"; }
    if (empty($mtype)) { $error['mtype'] = "類型不得為空。"; }
    if (empty($mdate)) { $error['mdate'] = "首映日期不得為空。"; }
    if (empty($content)) { $error['content'] = "內容簡介不得為空。"; }

    if (empty($error)) {
        $sql = "INSERT INTO movie (title, year, director, mtype, mdate, content) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sissss", $title, $year, $director, $mtype, $mdate, $content);
        if ($stmt->execute()) { header("Location: movie_management.php?message=新增成功"); exit; } else { echo "新增資料失敗: " . $stmt->error; }
        $stmt->close();
    }
}

// 處理修改資料
if ($action == 'edit' && $_SERVER["REQUEST_METHOD"] == "POST" && $id > 0) {
    $title = trim($_POST['title']);
    $year = trim($_POST['year']);
    $director = trim($_POST['director']);
    $mtype = trim($_POST['mtype']);
    $mdate = trim($_POST['mdate']);
    $content = trim($_POST['content']);

    if (empty($title)) { $error['title'] = "電影名稱不得為空。"; }
    if (!is_numeric($year) || strlen($year) != 4) { $error['year'] = "發行年代必須為四位數字。"; }
    if (empty($director)) { $error['director'] = "導演不得為空。"; }
    if (empty($mtype)) { $error['mtype'] = "類型不得為空。"; }
    if (empty($mdate)) { $error['mdate'] = "首映日期不得為空。"; }
    if (empty($content)) { $error['content'] = "內容簡介不得為空。"; }

    if (empty($error)) {
        $sql = "UPDATE movie SET title=?, year=?, director=?, mtype=?, mdate=?, content=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sissssi", $title, $year, $director, $mtype, $mdate, $content, $id);
        if ($stmt->execute()) { header("Location: movie_management.php?message=修改成功"); exit; } else { echo "修改資料失敗: " . $stmt->error; }
        $stmt->close();
    }
}

// 處理刪除資料
if ($action == 'delete' && $id > 0) {
    $sql = "DELETE FROM movie WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) { header("Location: movie_management.php?message=刪除成功"); exit; } else { echo "刪除資料失敗: " . $stmt->error; }
    $stmt->close();
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>電影管理</title>
    <style>
        body { font-family: sans-serif; margin: 20px; }
        h1, h2 { margin-bottom: 15px; }
        table { width: 80%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .pagination { margin-top: 10px; }
        .pagination a { padding: 5px 10px; border: 1px solid #ccc; margin-right: 5px; text-decoration: none; }
        .pagination .current { font-weight: bold; }
        .container { width: 60%; margin: 20px auto; padding: 20px; border: 1px solid #ddd; }
        label { display: block; margin-bottom: 5px; }
        input[type="text"], input[type="date"], textarea { width: 100%; padding: 8px; margin-bottom: 10px; border: 1px solid #ccc; box-sizing: border-box; }
        .error { color: red; }
        button { padding: 10px 15px; background-color: #4CAF50; color: white; border: none; cursor: pointer; }
        button.edit { background-color: #008CBA; }
        button.delete { background-color: #f44336; }
        button:hover { opacity: 0.8; }
        p a { text-decoration: none; }
        .message { color: green; margin-bottom: 10px; }
    </style>
</head>
<body>
    <h1>電影管理</h1>

    <?php if ($message): ?>
        <p class="message"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <?php if ($action == 'list'): ?>
        <p><a href="?action=add">新增電影</a></p>
        <?php
        $perPage = 10;
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $start = ($page - 1) * $perPage;

        $totalResult = $conn->query("SELECT COUNT(*) AS total FROM movie");
        $totalRow = $totalResult->fetch_assoc();
        $totalRecords = $totalRow['total'];
        $totalPages = ceil($totalRecords / $perPage);

        $sql = "SELECT id, title, year, director FROM movie LIMIT $start, $perPage";
        $result = $conn->query($sql);

        if ($result->num_rows > 0):
        ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>電影名稱</th>
                        <th>發行年代</th>
                        <th>導演</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['title']); ?></td>
                            <td><?php echo $row['year']; ?></td>
                            <td><?php echo htmlspecialchars($row['director']); ?></td>
                            <td>
                                <a href="?action=view&id=<?php echo $row['id']; ?>">查看</a> |
                                <a href="?action=edit&id=<?php echo $row['id']; ?>">編輯</a> |
                                <a href="?action=delete&id=<?php echo $row['id']; ?>" onclick="return confirm('確定要刪除這筆資料嗎？');">刪除</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <div class="pagination">
                <?php if ($totalPages > 1): ?>
                    <?php if ($page > 1): ?>
                        <a href="?action=list&page=<?php echo ($page - 1); ?>">上一頁</a>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?action=list&page=<?php echo $i; ?>" class="<?php echo ($i == $page) ? 'current' : ''; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>
                    <?php if ($page < $totalPages): ?>
                        <a href="?action=list&page=<?php echo ($page + 1); ?>">下一頁</a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <p>目前沒有電影資料。</p>
        <?php endif; ?>

    <?php elseif ($action == 'view' && $id > 0): ?>
        <?php
        $sql = "SELECT * FROM movie WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $movie = $result->fetch_assoc();
        $stmt->close();

        if ($movie):
        ?>
            <h2>電影詳細資料</h2>
            <p><strong>ID:</strong> <?php echo $movie['id']; ?></p>
            <p><strong>電影名稱:</strong> <?php echo htmlspecialchars($movie['title']); ?></p>
            <p><strong>發行年代:</strong> <?php echo $movie['year']; ?></p>
            <p><strong>導演:</strong> <?php echo htmlspecialchars($movie['director']); ?></p>
            <p><strong>類型:</strong> <?php echo htmlspecialchars($movie['mtype']); ?></p>
            <p><strong>首映日期:</strong> <?php echo $movie['mdate']; ?></p>
            <p><strong>內容簡介:</strong><br><?php echo nl2br(htmlspecialchars($movie['content'])); ?></p>
            <p><a href="?action=list">返回電影列表</a></p>
        <?php else: ?>
            <p>找不到該筆電影資料。</p>
        <?php endif; ?>

    <?php elseif ($action == 'add'): ?>
        <h2>新增電影</h2>
        <div class="container">
            <form method="post" action="?action=add">
                <div>
                    <label for="title">電影名稱:</label>
                    <input type="text" id="title" name="title" value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>">
                    <span class="error"><?php echo isset($error['title']) ? $error['title'] : ''; ?></span>
                </div>
                <div>
                    <label for="year">發行年代:</label>
                    <input type="text" id="year" name="year" value="<?php echo isset($_POST['year']) ? htmlspecialchars($_POST['year']) : ''; ?>">
                    <span class="error"><?php echo isset($error['year']) ? $error['year'] : ''; ?></span>
                </div>
                <div>
                    <label for="director">導演:</label>
                    <input type="text" id="director" name="director" value="<?php echo isset($_POST['director']) ? htmlspecialchars($_POST['director']) : ''; ?>">
                    <span class="error"><?php echo isset($error['director']) ? $error['director'] : ''; ?></span>
                </div>
                <div>
                    <label for="mtype">類型:</label>
                    <input type="text" id="mtype" name="mtype" value="<?php echo isset($_POST['mtype']) ? htmlspecialchars($_POST['mtype']) : ''; ?>">
                    <span class="error"><?php echo isset($error['mtype']) ? $error['mtype'] : ''; ?></span>
                </div>
                <div>
                    <label for="mdate">首映日期:</label>
                    <input type="date" id="mdate" name="mdate" value="<?php echo isset($_POST['mdate']) ? htmlspecialchars($_POST['mdate']) : ''; ?>">
                    <span class="error"><?php echo isset($error['mdate']) ? $error['mdate'] : ''; ?></span>
                </div>
                <div>
                    <label for="content">內容簡介:</label>
                    <textarea id="content" name="content"><?php echo isset($_POST['content']) ? htmlspecialchars($_POST['content']) : ''; ?></textarea>
                    <span class="error"><?php echo isset($error['content']) ? $error['content'] : ''; ?></span>
                </div>
                <button type="submit">新增</button>
            </form>
            <p><a href="?action=list">返回電影列表</a></p>
        </div>

    <?php elseif ($action == 'edit' && $id > 0): ?>
        <?php
        $sql = "SELECT * FROM movie WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $movie = $result->fetch_assoc();
        $stmt->close();

        if ($movie):
        ?>
            <h2>編輯電影</h2>
            <div class="container">
                <form method="post" action="?action=edit&id=<?php echo $id; ?>">
                    <div>
                        <label for="title">電影名稱:</label>
                        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($movie['title']); ?>">
                        <span class="error"><?php echo isset($error['title']) ? $error['title'] : ''; ?></span>
                    </div>
                    <div>
                        <label for="year">發行年代:</label>
                        <input type="text" id="year" name="year" value="<?php echo htmlspecialchars($movie['year']); ?>">
                        <span class="error"><?php echo isset($error['year']) ? $error['year'] : ''; ?></span>
                    </div>
                    <div>
                        <label for="director">導演:</label>
                        <input type="text" id="director" name="director" value="<?php echo htmlspecialchars($movie['director']); ?>">
                        <span class="error"><?php echo isset($error['director']) ? $error['director'] : ''; ?></span>
                    </div>
                    <div>
                        <label for="mtype">類型:</label>
                        <input type="text" id="mtype" name="mtype" value="<?php echo htmlspecialchars($movie['mtype']); ?>">
                        <span class="error"><?php echo isset($error['mtype']) ? $error['mtype'] : ''; ?></span>
                    </div>
                    <div>
                        <label for="mdate">首映日期:</label>
                        <input type="date" id="mdate" name="mdate" value="<?php echo htmlspecialchars($movie['mdate']); ?>">
                        <span class="error"><?php echo isset($error['mdate']) ? $error['mdate'] : ''; ?></span>
                    </div>
                    <div>
                        <label for="content">內容簡介:</label>
                        <textarea id="content" name="content"><?php echo htmlspecialchars($movie['content']); ?></textarea>
                        <span class="error"><?php echo isset($error['content']) ? $error['content'] : ''; ?></span>
                    </div>
                    <button type="submit" class="edit">儲存</button>
                </form>
                <p><a href="?action=list">返回電影列表</a></p>
            </div>
        <?php else: ?>
            <p>找不到要編輯的電影資料。</p>
        <?php endif; ?>

    <?php else: ?>
        <p>未知的操作。</p>
    <?php endif; ?>

    <?php $conn->close(); ?>
</body>
</html>