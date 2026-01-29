<?php
include '../cors.php';
require_once '../db.php';
require_once '../vendor/autoload.php';

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accessToken = $_COOKIE['access_token'];
    $secretKey_access = "";
    try {
        $decoded = JWT::decode($accessToken, new Key($secretKey_access, 'HS256'));
        $user_id = $decoded->user_id;
        $query = "SELECT tag, tag_svgcolor, tag_backgrounds FROM links WHERE user_id = $1";
        $result = pg_query_params($conn, $query, array($user_id));
        if ($result && pg_num_rows($result) > 0) {
            $tags = array();
            while ($row = pg_fetch_assoc($result)) {
                $tags[] = array(
                    'text' => $row['tag'],
                    'textColor' => $row['tag_svgcolor'],
                    'bgColor' => $row['tag_backgrounds']
                );
            }
            $response = array('status' => 'success', 'tags' => $tags);
            echo json_encode($response);
        } else {
            $response = array('status' => 'error', 'message' => 'No tags found');
            echo json_encode($response);
        }
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(array("success" => false, "message" => "Authentication error: " . $e->getMessage()));
    }
} else {
    http_response_code(405);
    echo json_encode(array("success" => false, "message" => "Method Not Allowed"));
}

pg_close($conn);
?>
