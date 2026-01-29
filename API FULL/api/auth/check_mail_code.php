<?php
require '../vendor/autoload.php';
require '../cors.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../db_pdo.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->email) || !isset($data->verificationCode)) {
    echo json_encode(["success" => false, "message" => "Необходимые данные отсутствуют."]);
    exit();
}

$email = $data->email;
$verificationCode = $data->verificationCode;

try {
    $stmt = $pdo->prepare('SELECT code FROM mails WHERE email = :email AND type = :type ORDER BY time DESC LIMIT 1');
    $stmt->execute(['email' => $email, 'type' => 'register_code']);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $storedCode = $result['code'];
        if ($storedCode === $verificationCode) {
            echo json_encode(["success" => true, "message" => "Код подтверждения верный."]);
        } else {
            echo json_encode(["success" => false, "message" => "Неверный код подтверждения."]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Письмо с кодом подтверждения не найдено."]);
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Ошибка при проверке кода: {$e->getMessage()}"]);
}
?>