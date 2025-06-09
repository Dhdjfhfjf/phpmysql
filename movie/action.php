<?php
session_start();

require_once "dbconfig.php";

$conn = new PDO("mysql:host=$hostname;dbname=$database;charset=UTF8", $dbuser, $dbpass);

// 設定錯誤處理模式 set the PDO error mode to exception
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$action = $_POST["action"];

if ($action == "login") {
    $name1 = $_POST["username"];
    $pass1 = $_POST["userpass"];

    $stmt = $conn->prepare("SELECT * FROM `users` WHERE `username`='$name1';");
    $stmt->execute();

    //帳號正確
    if ($stmt->rowCount()==1) {

        $ds = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $dd = $ds[0];

        $id = $dd["id"];
        $username = $dd["username"];
        $hashed_password = $dd["userpass"];

        if (password_verify($pass1, $hashed_password)){

            // Store data in session variables
            $_SESSION["loggedin"] = true;
            $_SESSION["id"] = $id;
            $_SESSION["username"] = $username;

            echo "Yes";

        } else {
            echo "No";

        }

    } else {
        echo "No";
    }

} elseif ($action == "logout") {

    unset($_SESSION["username"]);
    session_destroy();

}
// Close connection
unset($conn);
?>