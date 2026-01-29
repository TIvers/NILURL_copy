<?php
include '../cors.php';
require_once '../db.php';
require_once '../vendor/autoload.php';

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_COOKIE['access_token'])) {
        $accessToken = $_COOKIE['access_token'];
        $secretKey_access = "";
        $input = json_decode(file_get_contents("php://input"), true);
        if (isset($input['pathS'])) {
            $pathS = str_replace('nilurl.ru/', '', $input['pathS']);
            try {
                $decoded = JWT::decode($accessToken, new Key($secretKey_access, 'HS256'));
                $user_id = $decoded->user_id;
                $updateQuery = "UPDATE users SET available_qr = available_qr - 1 WHERE user_id = $1 AND available_qr > 0 RETURNING available_qr";
                $result = pg_query_params($conn, $updateQuery, array($user_id));
                if ($result && pg_num_rows($result) > 0) {
                    $row = pg_fetch_assoc($result);
                    $available_qr = $row['available_qr'];
                    $linkUpdateQuery = "UPDATE links SET qr_open = true WHERE code_url = $1 AND user_id = $2";
                    $linkResult = pg_query_params($conn, $linkUpdateQuery, array($pathS, $user_id));
                    if ($linkResult && pg_affected_rows($linkResult) > 0) {
                        $response = array('status' => 'success', 'available_qr' => $available_qr);
                        echo json_encode($response);
                    } else {
                        $response = array('status' => 'error', 'message' => 'Failed to update qr_open. Make sure the pathS and user_id are correct.');
                        echo json_encode($response);
                    }
                } else {
                    $response = array('status' => 'error', 'message' => 'No available QR codes left or failed to update.');
                    echo json_encode($response);
                }
            } catch (Exception $e) {
                http_response_code(401);
                echo json_encode(array("success" => false, "message" => "Authentication error: " . $e->getMessage()));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("success" => false, "message" => "Missing pathS in request body."));
        }
    } else {
        http_response_code(401);
        echo json_encode(array("success" => false, "message" => "Access token is missing."));
    }
} else {
    http_response_code(405);
    echo json_encode(array("success" => false, "message" => "Method Not Allowed"));
}

pg_close($conn);
?>
