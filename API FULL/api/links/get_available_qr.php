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
        $query = "SELECT available_qr FROM users WHERE user_id = $1";
        $result = pg_query_params($conn, $query, array($user_id));
        if ($result && pg_num_rows($result) > 0) {
            $row = pg_fetch_assoc($result);
            $available_qr = $row['available_qr'];
            $response = array('status' => 'success', 'available_qr' => $available_qr);
            echo json_encode($response);
        } else {
            $response = array('status' => 'error', 'message' => 'No available QR codes found');
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
