<?php
include '../cors.php';
require_once '../db.php';

$data = json_decode(file_get_contents('php://input'), true);
$email = $data['email'];
$username = $data['username'];

$query = "SELECT * FROM users WHERE email = $1 OR username = $2";
$result = pg_query_params($conn, $query, array($email, $username));

if (!$result) {
    die("Ошибка выполнения запроса: " . pg_last_error());
}

if (pg_num_rows($result) > 0) {
    echo json_encode(array("success" => false));
} else {
    echo json_encode(array("success" => true));
}

pg_close($conn);
?>