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

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $query = "SELECT profile_picture FROM users WHERE user_id = $1";
            $result = pg_prepare($conn, "select_svg", $query);
            $result = pg_execute($conn, "select_svg", array($userId));
            
            if ($row = pg_fetch_assoc($result)) {
                $relativePath = $row['profile_picture'];
                if ($relativePath !== null) {
                    $baseUrl = "https://nilurl.ru:8000/"; // base URL
                    $fullUrl = $baseUrl . $relativePath; // combine base URL with relative path
                    
                    $response['success'] = true;
                    $response['svg'] = $fullUrl; // send full URL
                } else {
                    $response['success'] = false;
                    $response['message'] = "SVG не найдено.";
                }
            } else {
                $response['success'] = false;
                $response['message'] = "SVG не найдено.";
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