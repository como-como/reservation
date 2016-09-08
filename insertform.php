<?php
require_once("lib/util.php");
$gobackURL = "insertform.html";

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

  // ブランドテーブルからブランドIDとブランド名を取り出す
  $sql = "SELECT id, name FROM room";
  // プリペアドステートメントを作る
  $stm = $pdo->prepare($sql);
  // SQLクエリを実行する
  $stm->execute();
  // 結果の取得（連想配列で受け取る）
  $room = $stm->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
  $err =  '<span class="error">エラーがありました。</span><br>';
  $err .= $e->getMessage();
  exit($err);
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<title>会議室予約システム</title>
<link href="../css/style.css" rel="stylesheet">
</head>
<body>
<h1>会議室予約システム</h1>
<div>
  <!-- 入力フォームを作る -->
  <form method="POST" action="insert_reservation.php">
    <ul>
      <li>予約日を選択してください：
        <input type="date" name="date" value="<?php echo "{$postDate}"?>">
      </li>
      <li>会議室を選んでください：
      <select name="room">
        <?php
        // 会議室はroomテーブルに登録してあるものから選ぶ
        foreach ($room as $row){
          echo '<option value="', $row["id"], '">', $row["name"], "</option>";
        }
        ?>
        </select>
      </li>
      <li>
        <label>名前：
        <input type="text" name="user" placeholder="氏名をフルネームで記載">
        </label>
      </li>
      <li>部署：
        <input type="text" name="branch">
      </li>
      <li><input type="submit" value="予約する"></li>
    </ul>
  </form>
</div>
</body>
</html>
