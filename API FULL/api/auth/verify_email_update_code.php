<?php
require '../vendor/autoload.php';
require '../cors.php';
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../db_pdo.php';

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->code) || !isset($data->email)) {
    echo json_encode(["success" => false, "message" => "Необходимые данные отсутствуют."]);
    exit();
}

$verificationCode = $data->code;
$email_new = $data->email;

$accessToken = $_COOKIE['access_token'];
$secretKey_access = "l0pTUf8Hc7BrywJ";
$secretKey_refresh = "another_secret_key";
$algorithm = 'HS256';

try {
    $decoded = JWT::decode($accessToken, new Key($secretKey_access, 'HS256'));
    $email_old = $decoded->email;
    $user_id = $decoded->user_id;
    $user_status = $decoded->user_status;
    $username = $decoded->username;
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Ошибка при декодировании токена: {$e->getMessage()}"]);
    exit();
}

try {
    $stmt = $pdo->prepare('SELECT code FROM mails WHERE email = :email AND type = :type ORDER BY time DESC LIMIT 1');
    $stmt->execute(['email' => $email_old, 'type' => 'verification_code']);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $storedCode = $result['code'];
        if ($storedCode === $verificationCode) {
            $updateStmt = $pdo->prepare('UPDATE users SET email = :email_new WHERE email = :email_old');
            $updateStmt->execute(['email_new' => $email_new, 'email_old' => $email_old]);

            $userStmt = $pdo->prepare('SELECT * FROM users WHERE email = :email_new');
            $userStmt->execute(['email_new' => $email_new]);
            $userData = $userStmt->fetch(PDO::FETCH_ASSOC);

            if ($userData) {
                $expirationTime = time() + 3600;
                $refreshExpirationTime = time() + 604800;

                $payload = array(
                    "success" => true,
                    "user_status" => $user_status,
                    'user_id' => $user_id,
                    'username' => $username,
                    'email' => $userData['email'],
                    'exp' => $expirationTime
                );

                $accessToken = JWT::encode($payload, $secretKey_access, $algorithm);

                $refreshPayload = array(
                    "success" => true,
                    'user_id' => $user_id,
                    'exp' => $refreshExpirationTime
                );

                $refreshToken = JWT::encode($refreshPayload, $secretKey_refresh, $algorithm);

                echo json_encode(array(
                    'success' => true,
                    "message" => "Код подтверждения верный. Email обновлен.",
                    'access_token' => $accessToken,
                    'refresh_token' => $refreshToken
                ));
            } else {
                echo json_encode(["success" => false, "message" => "Ошибка при получении данных пользователя."]);
            }
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