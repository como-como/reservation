<?php
require_once("lib/util.php");
$gobackURL = "insertform.php";

// データベースユーザ
$user = 'testuser';
$password = 'testuser';
// 利用するデータベース
$dbName = 'reservation';
// MySQLサーバ
$host = 'localhost:3306';
// MySQLのDSN文字列
$dsn = "mysql:host={$host};dbname={$dbName};charset=utf8";
//MySQLデータベースに接続する
try {
    $pdo = new PDO($dsn, $user, $password);
    // プリペアドステートメントのエミュレーションを無効にする
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    // 例外がスローされる設定にする
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // roomテーブルからidとnameを取り出す
    $sql1 = "SELECT id, name, capcity FROM room";
    // プリペアドステートメントを作る
    $stm = $pdo->prepare($sql1);
    // SQLクエリを実行する
    $stm->execute();
    // 結果の取得（連想配列で受け取る）
    $room = $stm->fetchAll(PDO::FETCH_ASSOC);

    // テーブルからtimezoneとhourを取り出す
    $sql2 = "SELECT timezone, hour FROM timezone";
    // プリペアドステートメントを作る
    $stm = $pdo->prepare($sql2);
    // SQLクエリを実行する
    $stm->execute();
    // 結果の取得（連想配列で受け取る）
    $timezone = $stm->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $err = '<span class="error">エラーがありました。</span><br>';
    $err .= $e->getMessage();
    exit($err);
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>いいね株式会社 会議室予約システム</title>
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
<h1>いいね株式会社 会議室予約システム</h1>

<div>
    <!-- 入力フォームを作る -->
    <form method="POST" action="insert_reservation.php">
        <ul>
            <li>日付を選ぶ：
                <input type="date" name="date">
            </li>
            <li>時間帯：
                <select name="timezone">
                    <?php
                    foreach ($timezone as $row) {
                        echo '<option value="', $row["timezone"], '">', $row["hour"],"</option>";
                    }
                    ?>
                </select>
            </li>

            <li>会議室：
                <select name="room">
                    <?php
                    foreach ($room as $row) {
                        echo '<option value="', $row["id"], '">', $row["name"], "（", $row["capcity"], "名まで）</option>";
                    }
                    ?>
                </select>
            </li>
            <li>
                <label>参加人数：
                    <input type="number" name="amount" placeholder="半角数字で記入">
                </label>
            </li>
            <li>コーヒー：
                <label><input type="radio" name="coffee" value="必要" checked>必要</label>
                <label><input type="radio" name="coffee" value="不要">不要</label>
            </li>
            <li>
                <label>氏名：
                    <input type="text" name="user" placeholder="フルネームで記入">
                </label>
            </li>
            <li>
                <label>所属部署：
                    <input type="text" name="branch" placeholder="部署名を記入">
            </li>
            <li>
                <label>使用目的：
                    <input type="text" name="text" placeholder="使用目的を記入">
            </li>
            <li><input type="submit" value="予約する"></li>
        </ul>
    </form>
</div>
</body>
</html>
