<?php
require '../vendor/autoload.php'; // Подключение автозагрузчика Composer
require '../cors.php';
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

// Включение обработки ошибок
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Подключение к базе данных
require_once '../db_pdo.php';

// Получение данных из POST-запроса
$data = json_decode(file_get_contents("php://input"));

// Проверка наличия необходимых данных
if (!isset($data->code)) {
    echo json_encode(["success" => false, "message" => "Необходимые данные отсутствуют."]);
    exit();
}

$accessToken = $_COOKIE['access_token'];
$secretKey_access = "";

try {
    $decoded = JWT::decode($accessToken, new Key($secretKey_access, 'HS256'));
    $email = $decoded->email;
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Ошибка при декодировании токена: {$e->getMessage()}"]);
    exit();
}

$verificationCode = $data->code;

try {
    // Подготовка SQL-запроса для получения последнего кода подтверждения
    $stmt = $pdo->prepare('SELECT code FROM mails WHERE email = :email AND type = :type ORDER BY time DESC LIMIT 1');
    $stmt->execute(['email' => $email, 'type' => 'delete_account_code']);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $storedCode = $result['code'];
        if ($storedCode === $verificationCode) {
            // Код подтверждения верный, обновляем дату удаления аккаунта
            $deletionDate = (new DateTime())->modify('+1 month')->format('Y-m-d H:i:s');

            $updateStmt = $pdo->prepare('UPDATE users SET deletion_date = :deletion_date WHERE email = :email');
            $updateStmt->execute(['deletion_date' => $deletionDate, 'email' => $email]);

            echo json_encode(["success" => true, "message" => "Код подтверждения верный. Аккаунт будет удалён {$deletionDate}."]);
        } else {
            echo json_encode(["success" => false, "message" => "Неверный код подтверждения."]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Письмо с кодом подтверждения не найдено."]);
    }
} catch (Exception $e) {
    // В случае ошибки выполнения запроса
    echo json_encode(["success" => false, "message" => "Ошибка при проверке кода: {$e->getMessage()}"]);
}
?>