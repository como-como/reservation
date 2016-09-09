<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
</head>
<body>
<div>
<?php

$user='testuser';
$password = 'testuser';
$dbName = 'reservation';
$host = 'localhost:3306';
$dsn = "mysql:host={$host};dbname={$dbName};charset=utf8";
try{
    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //echo "データベース{$dbName}に接続しました";
    echo "予約状況";

    $today = date("Y-m-d");

    //$sql1 = "SELECT id, name FROM room";
    //$sql = "SELECT *  FROM reservation";
    $sql = "SELECT reservation.date, 
                reservation.room as rroom,
                 reservation.room as rtimezone,
                 reservation.check,
                reservation.user,
                reservation.branch,
                reservation.text,
                room.name,
                timezone.hour
            FROM reservation, room, timezone
            WHERE reservation.room = room.id 
            AND reservation.timezone=timezone.timezone
            ORDER BY reservation.date, reservation.room, 
            reservation.timezone";

     $stm = $pdo->prepare($sql);
    $stm->execute();
    $result = $stm->fetchAll(PDO::FETCH_ASSOC);

    echo "<table border='1'>";
    echo "<thead><tr bgcolor='yellow'>";
    echo "<th>日付</th>";
    echo "<th>時間</th>";
    echo "<th>会議室</th>";
    echo "<th>予約者</th>";
    echo "<th>部署</th>";
    echo "<th>目的</th>";
    echo "</tr></thead><tbody>";

    echo "<tbody>";

    //$date1 = array_lice($result['date'],0,1);
    //$date =$date1;
    //var_dump($date);
    //var_dump($today);

    foreach ($result as $row) {

        if(($row['date']) >= $today) {
            #    echo "<tr bgcolor='white'>";
            #}else{
            #    echo "<tr bgcolor='grey'>";
            #}

            echo "<tr>";
            echo "<td>" . ($row['date']) . "</td>";
            echo "<td>" . ($row['hour']) . "</td>";
            echo "<td>" . ($row['name']) . "</td>";
            echo "<td>" . ($row['user']) . "</td>";
            echo "<td>" . ($row['branch']) . "</td>";
            echo "<td>" . ($row['text']) . "</td>";
            echo "</tr>";
            //$date = $row['date'];
        }
        }
    echo "</tbody>";
    echo "</table>";

}catch(Exception $e){
    echo '<span class="error">エラーがありました</span>';
    echo $e->getMessage();
    exit();
}
?>
    <a href="project_database/insertform.php">戻る</a>
</div>
</body>
</html>