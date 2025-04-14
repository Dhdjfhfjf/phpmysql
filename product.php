<?php

// 資料庫連線設定
$servername = "localhost"; // 你的伺服器名稱
$username = "root"; // 你的資料庫使用者名稱
$password = ""; // 你的資料庫密碼
$dbname = "class"; // 你的資料庫名稱

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    // 設定 PDO 錯誤模式為例外
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("資料庫連線失敗: " . $e->getMessage());
}

// 設定每頁顯示筆數
$recordsPerPage = 10;

// 取得目前頁碼
if (isset($_GET['page']) && is_numeric($_GET['page'])) {
    $currentPage = intval($_GET['page']);
} else {
    $currentPage = 1;
}

// 計算資料起始筆數
$startFrom = ($currentPage - 1) * $recordsPerPage;

// 取得總資料筆數
$totalRecords = $conn->query("SELECT COUNT(*) FROM product")->fetchColumn();
// 計算總頁數
$totalPages = ceil($totalRecords / $recordsPerPage);

// 處理動作
if (isset($_GET['action'])) {
    $action = $_GET['action'];

    switch ($action) {
        case 'view':
            // 查看單筆資料詳細內容
            if (isset($_GET['id']) && is_numeric($_GET['id'])) {
                $id = intval($_GET['id']);
                $stmt = $conn->prepare("SELECT * FROM product WHERE id = :id");
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                $product = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($product) {
                    echo "<h2>產品詳細資料</h2>";
                    echo "<p>ID: " . htmlspecialchars($product['id']) . "</p>";
                    echo "<p>產品名稱: " . htmlspecialchars($product['pname']) . "</p>";
                    echo "<p>產品規格: " . htmlspecialchars($product['pspec']) . "</p>";
                    echo "<p>產品定價: " . htmlspecialchars($product['price']) . "</p>";
                    echo "<p>製作日期: " . htmlspecialchars($product['pdate']) . "</p>";
                    echo "<p>內容說明: " . nl2br(htmlspecialchars($product['content'])) . "</p>";
                    echo "<p><a href='?'>返回列表</a></p>";
                } else {
                    echo "<p>查無此產品資料。</p>";
                    echo "<p><a href='?'>返回列表</a></p>";
                }
            } else {
                echo "<p>ID 格式不正確。</p>";
                echo "<p><a href='?'>返回列表</a></p>";
            }
            exit();
            break;

        case 'add_form':
            // 顯示新增資料表單
            echo "<h2>新增產品資料</h2>";
            echo "<form method='post' action='?action=add'>";
            echo "產品名稱: <input type='text' name='pname' required><br><br>";
            echo "產品規格: <input type='text' name='pspec' required><br><br>";
            echo "產品定價: <input type='number' name='price' required><br><br>";
            echo "製作日期: <input type='date' name='pdate' required><br><br>";
            echo "內容說明: <textarea name='content' rows='5' cols='50' required></textarea><br><br>";
            echo "<input type='submit' value='新增'>";
            echo "</form>";
            echo "<p><a href='?'>返回列表</a></p>";
            exit();
            break;

        case 'add':
            // 處理新增資料
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $pname = $_POST['pname'];
                $pspec = $_POST['pspec'];
                $price = $_POST['price'];
                $pdate = $_POST['pdate'];
                $content = $_POST['content'];

                $stmt = $conn->prepare("INSERT INTO product (pname, pspec, price, pdate, content) VALUES (:pname, :pspec, :price, :pdate, :content)");
                $stmt->bindParam(':pname', $pname);
                $stmt->bindParam(':pspec', $pspec);
                $stmt->bindParam(':price', $price);
                $stmt->bindParam(':pdate', $pdate);
                $stmt->bindParam(':content', $content);

                if ($stmt->execute()) {
                    echo "<p>資料新增成功！<a href='?'>返回列表</a></p>";
                } else {
                    echo "<p>資料新增失敗。</p>";
                    print_r($stmt->errorInfo());
                    echo "<p><a href='?action=add_form'>返回新增表單</a> | <a href='?'>返回列表</a></p>";
                }
                exit();
            } else {
                echo "<p>無效的請求。</p>";
                echo "<p><a href='?'>返回列表</a></p>";
                exit();
            }
            break;

        case 'edit_form':
            // 顯示修改資料表單
            if (isset($_GET['id']) && is_numeric($_GET['id'])) {
                $id = intval($_GET['id']);
                $stmt = $conn->prepare("SELECT * FROM product WHERE id = :id");
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                $product = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($product) {
                    echo "<h2>修改產品資料</h2>";
                    echo "<form method='post' action='?action=edit&id=" . htmlspecialchars($product['id']) . "'>";
                    echo "產品名稱: <input type='text' name='pname' value='" . htmlspecialchars($product['pname']) . "' required><br><br>";
                    echo "產品規格: <input type='text' name='pspec' value='" . htmlspecialchars($product['pspec']) . "' required><br><br>";
                    echo "產品定價: <input type='number' name='price' value='" . htmlspecialchars($product['price']) . "' required><br><br>";
                    echo "製作日期: <input type='date' name='pdate' value='" . htmlspecialchars($product['pdate']) . "' required><br><br>";
                    echo "內容說明: <textarea name='content' rows='5' cols='50' required>" . htmlspecialchars($product['content']) . "</textarea><br><br>";
                    echo "<input type='submit' value='儲存修改'>";
                    echo "</form>";
                    echo "<p><a href='?'>返回列表</a></p>";
                } else {
                    echo "<p>查無此產品資料，無法進行修改。</p>";
                    echo "<p><a href='?'>返回列表</a></p>";
                }
            } else {
                echo "<p>ID 格式不正確。</p>";
                echo "<p><a href='?'>返回列表</a></p>";
            }
            exit();
            break;

        case 'edit':
            // 處理修改資料
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['id']) && is_numeric($_GET['id'])) {
                $id = intval($_GET['id']);
                $pname = $_POST['pname'];
                $pspec = $_POST['pspec'];
                $price = $_POST['price'];
                $pdate = $_POST['pdate'];
                $content = $_POST['content'];

                $stmt = $conn->prepare("UPDATE product SET pname = :pname, pspec = :pspec, price = :price, pdate = :pdate, content = :content WHERE id = :id");
                $stmt->bindParam(':pname', $pname);
                $stmt->bindParam(':pspec', $pspec);
                $stmt->bindParam(':price', $price);
                $stmt->bindParam(':pdate', $pdate);
                $stmt->bindParam(':content', $content);
                $stmt->bindParam(':id', $id);

                if ($stmt->execute()) {
                    echo "<p>資料修改成功！<a href='?'>返回列表</a></p>";
                } else {
                    echo "<p>資料修改失敗。</p>";
                    print_r($stmt->errorInfo());
                    echo "<p><a href='?action=edit_form&id=" . $id . "'>返回修改表單</a> | <a href='?'>返回列表</a></p>";
                }
                exit();
            } else {
                echo "<p>無效的請求。</p>";
                echo "<p><a href='?'>返回列表</a></p>";
                exit();
            }
            break;

        case 'delete':
            // 處理刪除資料
            if (isset($_GET['id']) && is_numeric($_GET['id'])) {
                $id = intval($_GET['id']);
                $stmt = $conn->prepare("DELETE FROM product WHERE id = :id");
                $stmt->bindParam(':id', $id);

                if ($stmt->execute()) {
                    echo "<p>資料刪除成功！<a href='?'>返回列表</a></p>";
                } else {
                    echo "<p>資料刪除失敗。</p>";
                    print_r($stmt->errorInfo());
                    echo "<p><a href='?'>返回列表</a></p>";
                }
                exit();
            } else {
                echo "<p>ID 格式不正確。</p>";
                echo "<p><a href='?'>返回列表</a></p>";
                exit();
            }
            break;

        default:
            // 顯示資料列表
            break;
    }
}

// 取得分頁資料
$stmt = $conn->prepare("SELECT * FROM product ORDER BY id DESC LIMIT :start, :limit");
$stmt->bindParam(':start', $startFrom, PDO::PARAM_INT);
$stmt->bindParam(':limit', $recordsPerPage, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html>
<head>
    <title>產品管理</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
        .pagination a {
            margin: 0 5px;
            text-decoration: none;
        }
        .pagination .current {
            font-weight: bold;
        }
    </style>
</head>
<body>

    <h2>產品列表</h2>

    <p><a href="?action=add_form">新增產品</a></p>

    <?php if (!empty($products)): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>產品名稱</th>
                    <th>產品規格</th>
                    <th>產品定價</th>
                    <th>製作日期</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($product['id']); ?></td>
                        <td><?php echo htmlspecialchars($product['pname']); ?></td>
                        <td><?php echo htmlspecialchars($product['pspec']); ?></td>
                        <td><?php echo htmlspecialchars($product['price']); ?></td>
                        <td><?php echo htmlspecialchars($product['pdate']); ?></td>
                        <td>
                            <a href="?action=view&id=<?php echo htmlspecialchars($product['id']); ?>">查看</a> |
                            <a href="?action=edit_form&id=<?php echo htmlspecialchars($product['id']); ?>">修改</a> |
                            <a href="?action=delete&id=<?php echo htmlspecialchars($product['id']); ?>" onclick="return confirm('確定要刪除這筆資料嗎？');">刪除</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="pagination">
            <?php if ($totalPages > 1): ?>
                <?php if ($currentPage > 1): ?>
                    <a href="?page=<?php echo ($currentPage - 1); ?>">上一頁</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php if ($i === $currentPage): ?>
                        <span class="current"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($currentPage < $totalPages): ?>
                    <a href="?page=<?php echo ($currentPage + 1); ?>">下一頁</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>

    <?php else: ?>
        <p>目前沒有產品資料。</p>
    <?php endif; ?>

</body>
</html>

<?php
// 關閉資料庫連線
$conn = null;
?>