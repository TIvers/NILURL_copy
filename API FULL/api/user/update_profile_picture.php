<?php

include '../cors.php'; 
require_once '../db.php'; 
require_once '../vendor/autoload.php'; 

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

$response = array();

if (isset($_COOKIE['access_token'])) {
    $accessToken = $_COOKIE['access_token'];
    $secretKey_access = ""; 

    try {

        $decoded = JWT::decode($accessToken, new Key($secretKey_access, 'HS256'));
        $userId = $decoded->user_id; 

        $uploadDir = "uploads/"; 
        $targetDir = "/var/www/niltestCICD/api/" . $uploadDir; 

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_picture'])) {
            $file = $_FILES['profile_picture'];
            $fileType = $file['type'];
            $fileSize = $file['size'];
            $tmpName = $file['tmp_name'];

            $validFormats = array("image/jpeg", "image/png");
            $maxSize = 2 * 1024 * 1024; 

            if (!in_array($fileType, $validFormats)) {
                $response['success'] = false;
                $response['message'] = "Только файлы .png и .jpg разрешены.";
            } else if ($fileSize > $maxSize) {
                $response['success'] = false;
                $response['message'] = "Файл не должен превышать 2 МБ.";
            } else {
                $currentPicQuery = "SELECT profile_picture FROM users WHERE user_id = $1";
                $currentPicResult = pg_prepare($conn, "select_current_pic", $currentPicQuery);
                $currentPicResult = pg_execute($conn, "select_current_pic", array($userId));
                $currentPicPath = pg_fetch_result($currentPicResult, 0, 'profile_picture');

                if ($currentPicPath) {
                    $fullCurrentPicPath = $targetDir . basename($currentPicPath);
                    if (file_exists($fullCurrentPicPath)) {
                        unlink($fullCurrentPicPath);
                    }
                }

                $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $fileName = uniqid() . "." . $fileExtension;
                $targetFilePath = $targetDir . $fileName; 
                $relativeFilePath = $uploadDir . $fileName; 

                if (move_uploaded_file($tmpName, $targetFilePath)) {
                    $query = "UPDATE users SET profile_picture = $1 WHERE user_id = $2";
                    $result = pg_prepare($conn, "update_profile_picture", $query);
                    $result = pg_execute($conn, "update_profile_picture", array($relativeFilePath, $userId));

                    if ($result) {
                        $response['success'] = true;
                        $response['message'] = "Изображение профиля успешно обновлено.";
                    } else {
                        $response['success'] = false;
                        $response['message'] = "Ошибка при обновлении изображения профиля в базе данных.";
                    }
                } else {
                    $response['success'] = false;
                    $response['message'] = "Ошибка при загрузке файла.";
                }
            }
        } else {
            $response['success'] = false;
            $response['message'] = "Неверный запрос.";
        }
    } catch (Exception $e) {
        $response['success'] = false;
        $response['message'] = "Ошибка аутентификации: " . $e->getMessage();
    }
} else {
    $response['success'] = false;
    $response['message'] = "Токен доступа отсутствует.";
}

pg_close($conn);

header('Content-Type: application/json');
echo json_encode($response);
?>