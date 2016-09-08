<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>DB</title>
    <meta name="description" content="">
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <link rel="icon shortcut" href="./_favicon.ico" type="image/x-icon">
    <link rel="icon" sizes="any" href="./favicon.svg" type="image/svg+xml">
    <link rel="mask-icon" href="./favicon.svg" color="black">
    <style>
        table {
            border-collapse: collapse;
        }
        th,td {
            padding: 0.3rem 0.7rem;
            border: 1px solid #999;
            text-align: center;
        }
        input[type=submit] {
            padding: 1rem 5rem;
        }
    </style>
</head>

<body>
<div class="main-contents">
    <h2>会議室予約</h2>
    <p>会議室が表示されるだけです</p>

    <?php

    $user='testuser';
    $password = 'testuser';
    $dbName = 'reservation';
    $host = 'localhost:3306';
    $dsn = "mysql:host={$host};dbname={$dbName};charset=utf8";

    try{
        $pdo = new PDO($dsn, $user, $password); //DB接続
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "<p>データベース{$dbName}に接続しました</p>";

        $sql1 = "SELECT id, name FROM room";
        $stm = $pdo->prepare($sql1);
        $stm->execute();
        $rooms = $stm->fetchAll(PDO::FETCH_ASSOC);

    }catch(Exception $e){
        echo '<p class="error">エラーがありました</p>';
        echo $e->getMessage();
        exit();
    }
    ?>

    <form method="POST" action="reserve.php">
        <p>ブランド：
            <select name="room">
                <?php
                foreach ($rooms as $row) {
                    echo '<option value="'. $row["id"] .'">'. $row["name"] .'</option>';
                }
                ?>
            </select></p>
        <p><label>個数：<input type="number" name="quantity" placeholder="半角数字"></label></p>
        <input type="submit" value="追加する">
    </form>
</div>
</body>
</html>