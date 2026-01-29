<?php
include 'cors.php'; 
require_once 'db.php'; 
require_once 'vendor/autoload.php'; 

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accessToken = $_COOKIE['access_token']; 
    $secretKey_access = "l0pTUf8Hc7BrywJ"; 

    try {
        $decoded = JWT::decode($accessToken, new Key($secretKey_access, 'HS256'));
        $user_id = $decoded->user_id;

        $query1 = "SELECT available_links FROM users WHERE user_id = $1";
        $result1 = pg_query_params($conn, $query1, array($user_id));

        if ($result1 && pg_num_rows($result1) > 0) {
            $row1 = pg_fetch_assoc($result1);
            $available_links = $row1['available_links'];

            $query2 = "SELECT COUNT(*) as link_count FROM links WHERE user_id = $1";
            $result2 = pg_query_params($conn, $query2, array($user_id));

            if ($result2) {
                $row2 = pg_fetch_assoc($result2);
                $link_count = $row2['link_count'];

                $response = array(
                    'status' => 'success',
                    'available_links' => $available_links,
                    'link_count' => $link_count
                );
                echo json_encode($response);
            } else {
                $response = array('status' => 'error', 'message' => 'Error fetching link count');
                echo json_encode($response);
            }
        } else {
            $response = array('status' => 'error', 'message' => 'No available links found');
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