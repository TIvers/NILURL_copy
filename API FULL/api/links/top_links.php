<?php
include '../cors.php';
require_once '../db.php';
require_once '../vendor/autoload.php';

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

$accessToken = $_COOKIE['access_token'];
$secretKey_access = "";

try {
    $decoded = JWT::decode($accessToken, new Key($secretKey_access, 'HS256'));
    $user_id = $decoded->user_id;
    $query = "
        SELECT l.code_url, COALESCE(r.clicks, 0) as clicks
        FROM links l
        LEFT JOIN (
            SELECT code_url, COUNT(*) as clicks
            FROM redirects
            GROUP BY code_url
        ) r ON l.code_url = r.code_url
        WHERE l.user_id = $1
        ORDER BY clicks DESC
    ";
    $result = pg_query_params($conn, $query, array($user_id));
    if ($result) {
        $links = pg_fetch_all($result);
        $links_with_clicks = array();
        foreach ($links as $link) {
            $full_url = "http://nilurl.ru/" . $link['code_url'];
            $clicks = $link['clicks'];
            $links_with_clicks[] = array('url' => $full_url, 'clicks' => $clicks);
        }
        echo json_encode($links_with_clicks);
    } else {
        echo json_encode(array("success" => false, "message" => "Ошибка при получении данных."));
    }
} catch (Exception $e) {
    echo json_encode(array("success" => false, "message" => "Ошибка декодирования токена: " . $e->getMessage()));
}

pg_close($conn);
?>
