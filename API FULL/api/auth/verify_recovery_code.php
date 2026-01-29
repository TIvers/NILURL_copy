<?php

require '../cors.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../db_pdo.php';

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->verificationCode) || !isset($data->email)) {
    echo json_encode(["success" => false, "message" => "Необходимые данные отсутствуют."]);
    exit();
}

$verificationCode = $data->verificationCode;
$email = $data->email;

try {
    $stmt = $pdo->prepare('SELECT code FROM mails WHERE email = :email AND type = :type ORDER BY time DESC LIMIT 1');
    $stmt->execute(['email' => $email, 'type' => 'recovery_code']);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $storedCode = $result['code'];
        if ($storedCode === $verificationCode) {
            include '../reset_password_random.php';
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