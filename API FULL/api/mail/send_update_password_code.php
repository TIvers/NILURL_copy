<?php
require '../vendor/autoload.php'; // Подключение автозагрузчика Composer
require '../cors.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
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
if (!isset($data->code) || !isset($data->password)) {
    echo json_encode(["success" => false, "message" => "Необходимые данные отсутствуют."]);
    exit();
}

$verificationCode = $data->code;
$password = $data->password;

$accessToken = $_COOKIE['access_token'];
$secretKey_access = "";

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

try {
    $decoded = JWT::decode($accessToken, new Key($secretKey_access, 'HS256'));
    $email = $decoded->email;
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Ошибка при декодировании токена: {$e->getMessage()}"]);
    exit();
}

try {
    
    $stmt = $pdo->prepare('SELECT code FROM mails WHERE email = :email AND type = :type ORDER BY time DESC LIMIT 1');
    $stmt->execute(['email' => $email, 'type' => 'verification_code_password']);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $storedCode = $result['code'];
        if ($storedCode === $verificationCode) {
            
            $updateStmt = $pdo->prepare('UPDATE users SET password = :password WHERE email = :email');
            $updateStmt->execute(['password' => $hashedPassword, 'email' => $email]);
    
                echo json_encode(array(
                    'success' => true,
                    "message" => "Код подтверждения верный. Password обновлен.",
                ));
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