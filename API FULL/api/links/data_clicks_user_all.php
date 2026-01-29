<?php
include '../cors.php';
require_once '../db_pdo.php';
require_once '../vendor/autoload.php';

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

$accessToken = $_COOKIE['access_token'];
$secretKey_access = "";
$algorithm = '';

try {
    $decoded = JWT::decode($accessToken, new Key($secretKey_access, ''));
    $user_id = $decoded->user_id;
} catch (Exception $e) {
    echo json_encode(array('success' => false, 'message' => 'Ошибка декодирования токена: ' . $e->getMessage()));
    exit;
}

$pathS = isset($_GET['pathS']) ? $_GET['pathS'] : null;

if ($pathS) {
    if (strpos($pathS, '://') === false) {
        $pathS = 'http://' . $pathS;
    }
    $parsedUrl = parse_url($pathS);
    if (isset($parsedUrl['path'])) {
        $pathS = ltrim($parsedUrl['path'], '/');
    } else {
        echo json_encode(array('success' => false, 'message' => 'Некорректный URL pathS'));
        exit;
    }
    $stmt = $pdo->prepare("SELECT * FROM redirects WHERE code_url = :pathS");
    $stmt->execute(['pathS' => $pathS]);
    $redirects = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stmt = $pdo->prepare("SELECT DISTINCT code_url FROM links WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    $code_urls = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $stmt = $pdo->prepare("SELECT * FROM redirects WHERE code_url IN (" . implode(',', array_fill(0, count($code_urls), '?')) . ")");
    $stmt->execute($code_urls);
    $redirects = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

echo json_encode($redirects);

?>
