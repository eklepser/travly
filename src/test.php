<?php
require_once './connect.php';

$pdo = getPDO();
if ($pdo === null) {
    die("Ошибка: PDO объект не создан");
}
$sql = "SELECT * FROM test";
$stmt = $pdo->query($sql);

$users = $stmt->fetchAll();

echo "Найдено записей: " . count($users) . "<br><br>";

foreach ($users as $user) {
    print_r($user);
    echo "<br>";
}
?>