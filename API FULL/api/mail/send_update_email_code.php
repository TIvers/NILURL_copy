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

// Получение JSON данных из запроса
$data = json_decode(file_get_contents('php://input'), true);

// Проверка наличия необходимых данных
if (!isset($data['email_new'])) {
    echo json_encode(["success" => false, "message" => "Необходимые данные отсутствуют."]);
    exit();
}

$email_new = $data['email_new'];

$accessToken = $_COOKIE['access_token'];
$secretKey_access = "";

try {
    $decoded = JWT::decode($accessToken, new Key($secretKey_access, 'HS256'));
    $email = $decoded->email;
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Ошибка при декодировании токена: {$e->getMessage()}"]);
    exit();
}

if (!filter_var($email_new, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["success" => false, "message" => "Некорректный email адрес."]);
    exit();
}

try {
    // Проверка уникальности нового email
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = :email_new');
    $stmt->execute(['email_new' => $email_new]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        echo json_encode(["success" => false, "message" => "Email уже используется."]);
        exit();
    }

    // Генерация кода подтверждения
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
        $mail->setFrom('', 'NILUrl'); 
        $mail->addAddress($email);

        // Настройки письма
        $mail->isHTML(true);
        $mail->Subject = 'Код подтверждения';
        $mail->Body    = "Здравствуйте!<br><br>Ваш код подтверждения: <b>$verificationCode</b><br><br>Введите этот код для завершения процесса.";

        // Отправка письма
        $mail->send();

        // Сохранение кода подтверждения в БД
        $stmt = $pdo->prepare('INSERT INTO mails (email, type, time, code) VALUES (:email, :type, NOW(), :code)');
        if ($stmt->execute([
            'email' => $email,
            'type' => 'verification_code',
            'code' => $verificationCode
        ])) {
            echo json_encode(["success" => true, "message" => "Письмо успешно отправлено и сохранено в БД."]);
        } else {
            echo json_encode(["success" => false, "message" => "Письмо отправлено, но сохранение в БД не удалось."]);
        }
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Ошибка при отправке письма: {$mail->ErrorInfo}"]);
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Ошибка при проверке email: {$e->getMessage()}"]);
}
?>