<?php
require '../vendor/autoload.php'; // Подключение автозагрузчика Composer
require '../cors.php';
require_once '../db_pdo.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Получение данных из POST-запроса
$data = json_decode(file_get_contents("php://input"));

// Проверка наличия необходимых данных
if (!isset($data->email) || !isset($data->username)) {
    echo json_encode(["success" => false, "message" => "Необходимые данные отсутствуют."]);
    exit();
}

$email = $data->email;
$username = $data->username;

// Генерация проверочного кода
$verificationCode = rand(100000, 999999);

// Настройка PHPMailer
$mail = new PHPMailer(true);

try {
    // Настройки SMTP для Яндекса
    $mail->isSMTP();
    $mail->Host = 'smtp.yandex.ru'; 
    $mail->SMTPAuth = true;
    $mail->Username = ''; // Ваш email
    $mail->Password = ''; // Ваш пароль приложения
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->CharSet = 'UTF-8';
    // Получатель и отправитель
    $mail->setFrom('', 'NILUrl Регистрация'); 
    $mail->addAddress($email);

    // Настройки письма
    $mail->isHTML(true);
    $mail->Subject = 'Код для верификации NILUrl';
    $mail->Body    = "Здравствуйте, $username!<br><br>Ваш проверочный код: <b>$verificationCode</b><br><br>Спасибо за регистрацию!";

    // Отправка письма
    $mail->send();

    // Сохранение данных в таблицу mails
    $stmt = $pdo->prepare('INSERT INTO mails (email, type, time, code) VALUES (:email, :type, NOW(), :code)');
    if ($stmt->execute([
        'email' => $email,
        'type' => 'register_code',
        'code' => $verificationCode
    ])) {
        // Отправка на фронт только "success => true"
        echo json_encode(["success" => true, "message" => "Письмо успешно отправлено и сохранено в БД."]);
    } else {
        echo json_encode(["success" => false, "message" => "Письмо отправлено, но сохранение в БД не удалось."]);
    }

} catch (Exception $e) {
    // В случае ошибки отправки письма
    echo json_encode(["success" => false, "message" => "Ошибка при отправке письма: {$mail->ErrorInfo}"]);
}
?>