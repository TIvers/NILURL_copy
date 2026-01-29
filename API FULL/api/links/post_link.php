<?php
include '../cors.php';
require_once '../db.php';
require_once '../vendor/autoload.php';

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

function getFavicon($url) {
    $parsedUrl = parse_url($url);
    $baseUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];
    $html = @file_get_contents($url);
    if ($html === FALSE) {
        return "/NilLogo.svg";
    }
    $doc = new DOMDocument();
    @$doc->loadHTML($html);
    $iconLink = null;
    foreach (['link[rel="icon"]', 'link[rel="shortcut icon"]', 'link[rel*="icon"]', 'link[rel="apple-touch-icon"]', 'link[rel="apple-touch-icon-precomposed"]'] as $query) {
        $nodeList = (new DOMXPath($doc))->query("//" . $query);
        if ($nodeList->length > 0) {
            $iconLink = $nodeList->item(0);
            break;
        }
    }
    if ($iconLink) {
        $favicon = $iconLink->getAttribute('href');
        if (!parse_url($favicon, PHP_URL_SCHEME)) {
            $favicon = $baseUrl . '/' . ltrim($favicon, '/');
        }
    } else {
        $favicon = $baseUrl . '/favicon.ico';
        $headers = @get_headers($favicon);
        if (!$headers || strpos($headers[0], '200') === false) {
            $favicon = "/NilLogo.svg";
        }
    }
    return $favicon;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accessToken = $_COOKIE['access_token'] ?? '';
    $secretKey_access = "";
    try {
        $decoded = JWT::decode($accessToken, new Key($secretKey_access, 'HS256'));
        $user_id = $decoded->user_id;
        $data = json_decode(file_get_contents('php://input'), true);
        if (isset($data['inputText'], $data['shortUrl'], $data['tagValue'])) {
            $inputText = filter_var($data['inputText'], FILTER_SANITIZE_STRING);
            $shortUrl = filter_var($data['shortUrl'], FILTER_SANITIZE_URL);
            $tagValue = filter_var($data['tagValue'], FILTER_SANITIZE_STRING);
            $tag_backgrounds = filter_var($data['tagColors']['color'] ?? '', FILTER_SANITIZE_STRING);
            $tag_svgcolor = filter_var($data['tagColors']['svgColor'] ?? '', FILTER_SANITIZE_STRING);
            $android = isset($data['toggles']['android']) ? filter_var($data['toggles']['android'], FILTER_VALIDATE_BOOLEAN) : false;
            $ios = isset($data['toggles']['ios']) ? filter_var($data['toggles']['ios'], FILTER_VALIDATE_BOOLEAN) : false;
            $comment = isset($data['comment']) ? filter_var($data['comment'], FILTER_SANITIZE_STRING) : null;
            $dateLast = !empty($data['toggles']['date']) ? date('Y-m-d', strtotime($data['toggles']['date'])) : null;
            $dateNow = date('Y-m-d');
            $utm = $data['toggles']['utm'] ?? false;
            $parsedUrl = parse_url($shortUrl);
            $shortUrlPath = ltrim($parsedUrl['path'], '/');
            $svgpath = getFavicon($inputText);
            $checkAvailableLinksQuery = "SELECT available_links FROM users WHERE user_id = $1";
            $availableLinksResult = pg_query_params($conn, $checkAvailableLinksQuery, array($user_id));
            $availableLinksRow = pg_fetch_assoc($availableLinksResult);
            $availableLinks = (int)$availableLinksRow['available_links'];
            if ($availableLinks > 0) {
                $updateAvailableLinksQuery = "UPDATE users SET available_links = available_links - 1 WHERE user_id = $1";
                $updateResult = pg_query_params($conn, $updateAvailableLinksQuery, array($user_id));
                if ($updateResult) {
                    $checkShortUrlQuery = "SELECT EXISTS (SELECT 1 FROM links WHERE code_url = $1)";
                    $checkShortUrlResult = pg_query_params($conn, $checkShortUrlQuery, array($shortUrlPath));
                    $shortUrlExists = pg_fetch_result($checkShortUrlResult, 0, 0);
                    if ($shortUrlExists === 't') {
                        $response = array('status' => 'error', 'message' => 'Короткий URL уже существует, попробуйте другой.');
                        echo json_encode($response);
                        pg_close($conn);
                        exit;
                    }
                    $maxIdQuery_links = "SELECT COALESCE(MAX(id_link), 0) as max_id FROM links";
                    $maxIdResult_links = pg_query($conn, $maxIdQuery_links);
                    $maxIdRow_links = pg_fetch_assoc($maxIdResult_links);
                    $newIdLink_links = $maxIdRow_links['max_id'] + 1;
                    $maxIdQuery_utm = "SELECT COALESCE(MAX(id_utm), 0) as max_id FROM utm";
                    $maxIdResult_utm = pg_query($conn, $maxIdQuery_utm);
                    $maxIdRow_utm = pg_fetch_assoc($maxIdResult_utm);
                    $newIdLink_utm = $maxIdRow_utm['max_id'] + 1;
                    $query = "INSERT INTO links (id_link, base_url, code_url, tag, user_id, commentary, date_last, date_now, ios, android, tag_backgrounds, tag_svgcolor, svgpath, utm) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14)";
                    $result = pg_query_params($conn, $query, array($newIdLink_links, $inputText, $shortUrlPath, $tagValue, $user_id, $comment, $dateLast, $dateNow, $ios ? 'true' : 'false', $android ? 'true' : 'false', $tag_backgrounds, $tag_svgcolor, $svgpath, $utm ? 'true' : 'false'));
                    if ($result && $utm) {
                        $utmQuery = "INSERT INTO utm (id_utm, code_url, utm_source, utm_medium, utm_campaign, utm_term, utm_content, utm_referral, utm_ioc, utm_android) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10)";
                        $utmResult = pg_query_params($conn, $utmQuery, array(
                            $newIdLink_utm,
                            $shortUrlPath,
                            $utm['UTM Source'] ?? 'false',
                            $utm['UTM Medium'] ?? 'false',
                            $utm['UTM Campaign'] ?? 'false',
                            $utm['UTM Term'] ?? 'false',
                            $utm['UTM Content'] ?? 'false',
                            $utm['Referral'] ?? 'false',
                            $utm['iOS UTM Metrika'] ?? 'false',
                            $utm['Android UTM Metrika'] ?? 'false',
                        ));
                        if (!$utmResult) {
                            $response = array('status' => 'error', 'message' => 'Ошибка внесения utm данных в базу');
                            echo json_encode($response);
                            pg_close($conn);
                            exit;
                        }
                    }
                    if ($result) {
                        $response = array('status' => 'success', 'message' => 'Успешно внесены данные');
                        echo json_encode($response);
                    } else {
                        $response = array('status' => 'error', 'message' => 'Ошибка при внесении данных в базу');
                        echo json_encode($response);
                    }
                } else {
                    $response = array('status' => 'error', 'message' => 'Ошибка обновления available_links');
                    echo json_encode($response);
                }
            } else {
                $response = array('status' => 'error', 'message' => 'Недостаточно доступных ссылок');
                echo json_encode($response);
            }
        } else {
            $response = array('status' => 'error', 'message' => 'Неполные данные получены');
            echo json_encode($response);
        }
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(array("success" => false, "message" => "Ошибка аутентификации: " . $e->getMessage()));
    } finally {
        pg_close($conn);
    }
} else {
    http_response_code(405);
    echo json_encode(array("success" => false, "message" => "Метод не разрешен"));
}
?>