<?php
require_once("lib/util.php");
$gobackURL = "insertform.php";

// 文字エンコードの検証
if (!cken($_POST)) {
    header("Location:{$gobackURL}");
    exit();
}
?>

<?php
/*if (isset($_POST)) {
    print_r($_POST);
}
*/?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>会議室予約結果</title>
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
<div>
    <?php
    // 簡単なエラー処理
    $errors = [];
    if (!isset($_POST["date"]) || ($_POST["date"] === "")) {
        $errors[] = "日付の選択がされていません。";
    }
    if (!isset($_POST["timezone"]) || ($_POST["timezone"] === "")) {
        $errors[] = "時間帯の選択がされていません。";
    }
    if (!isset($_POST["room"]) || ($_POST["room"] === "")) {
        $errors[] = "会議室の選択がされていません。";
    }
    if (!isset($_POST["user"]) || ($_POST["user"] === "")) {
        $errors[] = "氏名が入力されていません。";
    }
    if (!isset($_POST["branch"]) || ($_POST["branch"] === "")) {
        $errors[] = "所属部署が入力されていません。";
    }

    //エラーがあったとき
    if (count($errors) > 0) {
        echo '<ol class="error">';
        foreach ($errors as $value) {
            echo "<li>", $value, "</li>";
        }
        echo "</ol>";
        echo "<hr>";
        echo "<a href=", $gobackURL, ">戻る</a>";
        exit();
    }

    // データベースユーザ
    $user = 'testuser';
    $password = 'testuser';
    // 利用するデータベース
    $dbName = 'reservation';
    // MySQLサーバ
    $host = 'localhost:3306';
    // MySQLのDSN文字列
    $dsn = "mysql:host={$host};dbname={$dbName};charset=utf8";

    $date = $_POST["date"];
    $timezone = $_POST["timezone"];
    $room = $_POST["room"];
    $booking_name = $_POST["user"];
    $branch = $_POST["branch"];

    //MySQLデータベースに接続する
    try {
        $pdo = new PDO($dsn, $user, $password);
        // プリペアドステートメントのエミュレーションを無効にする
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        // 例外がスローされる設定にする
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (Exception $e) {
        $err = '<span class="error">エラーがありました。</span><br>';
        $err .= $e->getMessage();
        exit($err);
    }

    $sql1 = "SELECT * FROM reservation WHERE date=\"".$date."\" AND timezone=".$timezone." AND room=".$room;
    $stm = $pdo->prepare($sql1);
    $stm->execute();
    $result = $stm->fetchAll(PDO::FETCH_ASSOC);
if (!count($result)){
    $sql2 = "INSERT INTO reservation (date, timezone, room, user, branch) VALUES (\"$date\", \"$timezone\", \"$room\", \"$booking_name\", \"$branch\")";
    $stm = $pdo->prepare($sql2);
    $stm->execute();

    $sql3 = "SELECT * FROM timezone";
    $stm = $pdo->prepare($sql3);
    $stm->execute();
    $timezone_list = $stm->fetchAll(PDO::FETCH_ASSOC);

    $timezone_readable;
    foreach($timezone_list as $timezone_item){
    if ($timezone==$timezone_item["timezone"]){
        $timezone_readable = $timezone_item["hour"];
    break;
    }
    }

    $sql4 = "SELECT * FROM room";
    $stm = $pdo->prepare($sql4);
    $stm->execute();
    $room_list = $stm->fetchAll(PDO::FETCH_ASSOC);

    $room_readable;
    foreach($room_list as $room_item){
        if ($room==$room_item["id"]){
            $room_readable = $room_item["name"];
            break;
        }
    }

    echo "<h2>下記の内容で予約を受け付けました。</h2><br>";
    echo "日付: ".$date."<br>";
    echo "時間帯: ".$timezone_readable."<br>";
    echo "会議室: ".$room_readable."<br>";
    echo "氏名: ".$booking_name."<br>";
    echo "部署: ".$branch;
} else {
    echo "<h2>予約済みのため、予約できません。「戻る」で再選択してください。</h2>";
}
  ?>
    <hr>
    <p><a href="<?php echo $gobackURL ?>">戻る</a></p>
</div>
</body>
</html>
