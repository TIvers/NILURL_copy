<?php
include '../cors.php';
require_once '../db.php';
require_once '../vendor/autoload.php';

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

$data = json_decode(file_get_contents('php://input'), true);
$username = $_POST['username'];

$accessToken = $_COOKIE['access_token'];
$secretKey_refresh = "";
$secretKey_access = "";
$algorithm = '';

try {
    $decoded = JWT::decode($accessToken, new Key($secretKey_access, ''));
    $user_id = $decoded->user_id;
    $user_status = $decoded->user_status;
} catch (Exception $e) {
    echo json_encode(array('success' => false, 'message' => 'Ошибка декодирования токена: ' . $e->getMessage()));
    exit;
}

$query = "SELECT * FROM users WHERE username = $1 AND user_id != $2";
$result = pg_query_params($conn, $query, array($username, $user_id));
if (pg_num_rows($result) > 0) {
    echo json_encode(array('success' => false, 'message' => 'Имя пользователя уже занято.'));
    exit;
}


$query = "SELECT * FROM users WHERE user_id = $1";
$result = pg_query_params($conn, $query, array($user_id));
if (pg_num_rows($result) == 0) {
    echo json_encode(array('success' => false, 'message' => 'Пользователь не найден.'));
    exit;
}

$userData = pg_fetch_assoc($result);


$query = "UPDATE users SET username = $1 WHERE user_id = $2";
$result = pg_query_params($conn, $query, array($username, $user_id));

if ($result) {
    $expirationTime = time() + 3600;  // access_token срок действия 1 час
    $refreshExpirationTime = time() + 604800;  // refresh_token срок действия 1 неделя

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
        'access_token' => $accessToken,
        'refresh_token' => $refreshToken
    ));
} else {
    echo json_encode(array('success' => false, 'message' => 'Ошибка при сохранении изменений.'));
}
ini_set('display_errors', 1);
pg_close($conn);
?>