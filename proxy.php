<?php

$url = isset($_GET['url']) ? $_GET['url'] : '';

if (filter_var($url, FILTER_VALIDATE_URL) === FALSE) {
    header("HTTP/1.1 400 Bad Request");
    echo "Invalid URL";
    exit();
}

// Получаем содержимое URL
$context = stream_context_create(array(
    'http' => array(
        'method'  => 'GET',
        'header'  => 'Accept-language: en\r\n' .
                     'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8\r\n' .
                     'Connection: keep-alive\r\n'
    )
));

$response = @file_get_contents($url, false, $context);

if ($response === FALSE) {
    header("HTTP/1.1 500 Internal Server Error");
    echo "Error fetching URL";
    exit();
}


$contentType = isset($http_response_header[0]) ? $http_response_header[0] : 'text/html';
header("Content-Type: $contentType");


echo $response;
?>