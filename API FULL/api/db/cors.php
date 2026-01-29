<?php

$origin = $_SERVER['HTTP_ORIGIN'];


if ($origin === "http://localhost:3000") {
    $allowed_origin = "http://localhost:3000";
} else {
    $allowed_origin = "https://nilurl.ru";
}

header("Access-Control-Allow-Origin: $allowed_origin"); 
header("Access-Control-Allow-Methods: GET, POST, OPTIONS"); 
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Access-Control-Allow-Credentials: true'); 


if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    header("Content-Length: 0");
    header("Content-Type: text/plain");
    exit;
}
?>