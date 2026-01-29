<?php
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../cors.php';
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../db_pdo.php';

$accessToken = $_COOKIE['access_token'];
$secretKey_access = "";

try {
    $decoded = JWT::decode($accessToken, new Key($secretKey_access, 'HS256'));
    $email = $decoded->email;

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.yandex.ru'; 
        $mail->SMTPAuth = true;
        $mail->Username = '';
        $mail->Password = '';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->CharSet = 'UTF-8';
        $mail->setFrom('', 'NILUrl Удаление аккаунта'); 
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Ваш аккаунт будет удален через 30 дней';
        $mail->Body    = "Здравствуйте!<br><br>Ваш аккаунт будет удален через 30 дней. Чтобы восстановить аккаунт, войдите в свой аккаунт по ссылке: <a href='https://nilurl.ru/login'>https://nilurl.ru/login</a><br><br>Если вы не запрашивали удаление аккаунта, пожалуйста, свяжитесь с нами.";

        $mail->send();

        echo json_encode(["success" => true, "message" => "Уведомление об удалении аккаунта успешно отправлено."]);
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Ошибка при отправке письма: {$mail->ErrorInfo}"]);
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Ошибка при декодировании токена: {$e->getMessage()}"]);
}
?>