<?php

require '../vendor/autoload.php'; // Подключение автозагрузчика Composer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Функция для генерации случайного пароля
function generateRandomPassword($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomPassword = '';
    for ($i = 0; $i < $length; $i++) {
        $randomPassword .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomPassword;
}

// Генерация нового пароля
$newPassword = generateRandomPassword();
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

try {
    // Подготовка SQL-запроса для обновления пароля пользователя
    $updateStmt = $pdo->prepare('UPDATE users SET password = :password WHERE email = :email');
    $updateStmt->execute(['password' => $hashedPassword, 'email' => $email]);

    // Настройка PHPMailer
    $mail = new PHPMailer(true);

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
    $mail->setFrom('', 'NILUrl Восстановление пароля');
    $mail->addAddress($email);

    // Настройки письма
    $mail->isHTML(true);
    $mail->Subject = 'Ваш новый пароль NILUrl';
    $mail->Body    = "Здравствуйте!<br><br>Ваш новый пароль: <b>$newPassword</b><br><br>Пожалуйста, измените его после первого входа.";

    // Отправка письма
    $mail->send();

    // Отправка успешного ответа на фронт
    echo json_encode(["success" => true, "message" => "Код подтверждения верный. Новый пароль отправлен на вашу электронную почту."]);
} catch (Exception $e) {
    // В случае ошибки выполнения запроса или отправки письма
    echo json_encode(["success" => false, "message" => "Ошибка при обновлении пароля или отправке письма: {$e->getMessage()}"]);
}

?>