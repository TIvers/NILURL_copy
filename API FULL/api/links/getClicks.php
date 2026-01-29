<?php
include '../cors.php';
require_once '../db.php';
require_once '../vendor/autoload.php';

$query = "
    SELECT code_url, COUNT(*) as clicks
    FROM redirects
    WHERE code_url IN ('n-faq', 'n-login', 'n-registration', 'n-price')
    GROUP BY code_url
";

$result = pg_query($conn, $query);

if ($result) {
    $clickCounts = [];
    while ($row = pg_fetch_assoc($result)) {
        $clickCounts[$row['code_url']] = (int) $row['clicks'];
    }
    echo json_encode($clickCounts);
} else {
    echo json_encode(array("success" => false, "message" => "Ошибка при получении данных."));
}

pg_close($conn);
?>
