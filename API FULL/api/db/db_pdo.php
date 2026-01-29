<?php
$host = ""; 
$port = ""; 
$dbname = ""; 
$username = ""; 
$password = ""; 

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $username, $password);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

?>
