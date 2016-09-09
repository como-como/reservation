<?php
require_once("lib/util.php");
$gobackURL = "insertform.php";

// 文字エンコードの検証
if (!cken($_POST)) {
    header("Location:{$gobackURL}");
    exit();
}

session_start();
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
<div class="wrapper">
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
    //[★要編集]フォームと変数名を合わせる
    //人数は任意入力にする。未入力なら1名と仮定
    if (!isset($_POST["amount"]) || ($_POST["amount"] === "")) {
        $amount = 1;
    } elseif(!ctype_digit($_POST["amount"])) {
        $errors[] = "参加人数が半角英数の整数値ではありません。";
    } else {
        $amount = $_POST['amount'];
        $_SESSION['amount'] = $_POST['amount'];
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
    $_SESSION["date"] = $_POST["date"];
    $timezone = $_POST["timezone"];
    $_SESSION["timezone"] = $_POST["timezone"];
    $room = $_POST["room"];
    $_SESSION["room"] = $_POST["room"];
    $booking_name = $_POST["user"];
    $_SESSION["user"] = $_POST["user"];
    $branch = $_POST["branch"];
    $_SESSION["branch"] = $_POST["branch"];

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

    // --------------------
    // 収容人数に関する処理
    // --------------------
    $sql4 = "SELECT * FROM room";
    $stm = $pdo->prepare($sql4);
    $stm->execute();
    $room_list = $stm->fetchAll(PDO::FETCH_ASSOC);

    //参加人数<=収容人数　の会議室リスト(希望以外も含む)
    foreach ($room_list as $row) {
        if($row['capcity']>=$amount) {
            $usable[$row['id']] = $row['name'];
        }
    }
    //print_r($usable); //確認

    //参加人数が収容人数内かどうか
    if(isset($usable)) {
        $reservable = array_key_exists($room, $usable);
    } else {
        $reservable = false;
    }
    if( !$reservable ) { //収容人数 NG
        $errors[] = "参加人数が会議室の収容人数を超えています。";
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

    $sql1 = "SELECT * FROM reservation, room WHERE date=\"".$date."\" AND timezone=".$timezone." AND room=".$room;
    $stm = $pdo->prepare($sql1);
    $stm->execute();
    $result = $stm->fetchAll(PDO::FETCH_ASSOC);

if (!count($result)){ //希望時間に予約が入っていない場合

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

} else if( count($usable)>1 ) { //希望通りに予約できないけど、同時刻のほかの会議室はどうか

    unset( $usable[$room] ); //最初の希望は通らなかったので消す
    //print_r($usable);

    foreach($usable as $row1) { //会議室ごとに走査
        $key = array_search($row1, $usable);
        foreach ($result as $row2) { //予約すべてを走査

            //日付と時間がかぶってて、かつ、使いたい会議室
            if ($row2['date'] === $date && $row2['timezone'] === $timezone && $row2['room'] === $key ) {
                echo $usable[$key] .'は予約が入っています。';
                unset( $usable[$key] ); //その会議室を削除
                //print_r($usable); //確認
            }
        }
    }

    if(count($usable)>0) {
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

        echo '<h2>予約希望日：'. $date .'　'. $timezone_readable .'</h2>';
        echo '<p>ご希望の会議室は既に予約が入っています。<br>ご希望の時間に予約が可能な会議室は以下の通りです。</p>';
        echo '<ul class="roomlist">';
        foreach ($usable as $row) {
            echo '<li>'. $row .'</li>';
        }
        echo '</ul>';
    } else {
        echo '<p>使用可能な会議室はありません。</p>';
    }

} else {
    echo "<h2>予約済みのため、予約できません。「戻る」で再選択してください。</h2>";
}
  ?>
    <hr>
    <p><a href="<?php echo $gobackURL ?>">戻る</a></p>
</div>
</body>
</html>
