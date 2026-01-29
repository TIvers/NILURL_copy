<?php
require '../cors.php'; // Подключение обработки CORS


error_reporting(E_ALL);
ini_set('display_errors', 1);

// Подключение к базе данных
require_once '../db_pdo.php'; 

try {
    // Запрос для получения количества пользователей
    $sql = "SELECT COUNT(*) as user_count FROM users";
    $stmt = $pdo->query($sql);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        echo json_encode(array("user_count" => $result["user_count"]+100));
    } else {
        echo json_encode(array("user_count" => 0));
    }
} catch (PDOException $e) {
    // Обработка ошибок выполнения запроса
    echo json_encode(array("success" => false, "message" => "Ошибка при выполнении запроса: " . $e->getMessage()));
}
?>