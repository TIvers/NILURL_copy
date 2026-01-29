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
        if (isset($data['inputText'], $data['shortUrl'], $data['pathS'], $data['tagValue'], $data['tagColors'])) {
            $inputText = filter_var($data['inputText'], FILTER_SANITIZE_URL);
            $shortUrl = str_replace('https://nilurl.ru/', '', filter_var($data['shortUrl'], FILTER_SANITIZE_URL));
            $pathS = str_replace('nilurl.ru/', '', filter_var($data['pathS'], FILTER_SANITIZE_URL));
            $tagValue = filter_var($data['tagValue'], FILTER_SANITIZE_STRING);
            $android = filter_var($data['toggles']['android'] ?? false, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false';
            $ios = filter_var($data['toggles']['ios'] ?? false, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false';
            $comment = filter_var($data['comment'] ?? '', FILTER_SANITIZE_STRING);
            $dateLast = !empty($data['toggles']['date']) ? date('Y-m-d', strtotime(filter_var($data['toggles']['date'], FILTER_SANITIZE_STRING))) : null;
            $utm = filter_var($data['toggles']['utm'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $tagBackgrounds = filter_var($data['tagColors']['color'], FILTER_SANITIZE_STRING);
            $tagSvgColor = filter_var($data['tagColors']['svgColor'], FILTER_SANITIZE_STRING);
            $favicon = getFavicon($inputText);
            if ($pathS === $shortUrl) {
                $findQuery = "SELECT * FROM links WHERE code_url = $1 AND user_id = $2";
                $findResult = pg_prepare($conn, "find_link", $findQuery);
                $findResult = pg_execute($conn, "find_link", array($pathS, $user_id));
                if (pg_num_rows($findResult) > 0) {
                    $updateQuery = "UPDATE links SET base_url = $1, tag = $2, commentary = $3, date_last = $4, ios = $5, android = $6, utm = $7, tag_backgrounds = $8, tag_svgcolor = $9, svgpath = $10 WHERE code_url = $11 AND user_id = $12";
                    $updateResult = pg_prepare($conn, "update_link", $updateQuery);
                    $updateResult = pg_execute($conn, "update_link", array($inputText, $tagValue, $comment, $dateLast, $ios, $android, $utm ? 'true' : 'false', $tagBackgrounds, $tagSvgColor, $favicon, $pathS, $user_id));
                    if ($updateResult) {
                        if ($utm) {
                            $checkUtmQuery = "SELECT * FROM utm WHERE code_url = $1";
                            $checkUtmResult = pg_prepare($conn, "check_utm", $checkUtmQuery);
                            $checkUtmResult = pg_execute($conn, "check_utm", array($pathS));
                            if (pg_num_rows($checkUtmResult) > 0) {
                                $utmQuery = "UPDATE utm SET utm_source = $1, utm_medium = $2, utm_campaign = $3, utm_term = $4, utm_content = $5, utm_referral = $6, utm_ioc = $7, utm_android = $8 WHERE code_url = $9";
                                $utmResult = pg_prepare($conn, "update_utm", $utmQuery);
                                $utmResult = pg_execute($conn, "update_utm", array(
                                    $utm['UTM Source'] ?? 'false',
                                    $utm['UTM Medium'] ?? 'false',
                                    $utm['UTM Campaign'] ?? 'false',
                                    $utm['UTM Term'] ?? 'false',
                                    $utm['UTM Content'] ?? 'false',
                                    $utm['UTM Referral'] ?? 'false',
                                    $utm['UTM iOC'] ?? 'false',
                                    $utm['UTM Android'] ?? 'false',
                                    $pathS,
                                ));
                            } else {
                                $maxIdQuery_utm = "SELECT MAX(id_utm) as max_id FROM utm";
                                $maxIdResult_utm = pg_prepare($conn, "max_id_utm", $maxIdQuery_utm);
                                $maxIdResult_utm = pg_execute($conn, "max_id_utm", array());
                                $maxIdRow_utm = pg_fetch_assoc($maxIdResult_utm);
                                $newIdLink_utm = $maxIdRow_utm['max_id'] + 1;
                                $utmQuery = "INSERT INTO utm (id_utm, code_url, utm_source, utm_medium, utm_campaign, utm_term, utm_content, utm_referral, utm_ioc, utm_android) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10)";
                                $utmResult = pg_prepare($conn, "insert_utm", $utmQuery);
                                $utmResult = pg_execute($conn, "insert_utm", array(
                                    $newIdLink_utm,
                                    $pathS,
                                    $utm['UTM Source'] ?? 'false',
                                    $utm['UTM Medium'] ?? 'false',
                                    $utm['UTM Campaign'] ?? 'false',
                                    $utm['UTM Term'] ?? 'false',
                                    $utm['UTM Content'] ?? 'false',
                                    $utm['UTM Referral'] ?? 'false',
                                    $utm['UTM iOC'] ?? 'false',
                                    $utm['UTM Android'] ?? 'false',
                                ));
                            }
                            if (!$utmResult) {
                                $response = array('status' => 'error', 'message' => 'Заполните значения UTM');
                                echo json_encode($response);
                                pg_close($conn);
                                exit;
                            }
                        } else {
                            $checkUtmQuery = "SELECT * FROM utm WHERE code_url = $1";
                            $checkUtmResult = pg_prepare($conn, "check_utm_delete", $checkUtmQuery);
                            $checkUtmResult = pg_execute($conn, "check_utm_delete", array($pathS));
                            if (pg_num_rows($checkUtmResult) > 0) {
                                $deleteUtmQuery = "DELETE FROM utm WHERE code_url = $1";
                                $deleteUtmResult = pg_prepare($conn, "delete_utm", $deleteUtmQuery);
                                $deleteUtmResult = pg_execute($conn, "delete_utm", array($pathS));
                                if (!$deleteUtmResult) {
                                    $response = array('status' => 'error', 'message' => 'Ошибка удаления UTM данных из базы');
                                    echo json_encode($response);
                                    pg_close($conn);
                                    exit;
                                }
                            }
                        }
                        $response = array('status' => 'success', 'message' => 'Данные успешно обновлены');
                        echo json_encode($response);
                    } else {
                        $response = array('status' => 'error', 'message' => 'Ошибка при обновлении данных в базе');
                        echo json_encode($response);
                    }
                } else {
                    $response = array('status' => 'error', 'message' => 'Запись не найдена');
                    echo json_encode($response);
                }
            } else {
                $response = array('status' => 'error', 'message' => 'pathS и shortUrl не совпадают');
                echo json_encode($response);
            }
        } else {
            $response = array('status' => 'error', 'message' => 'Неполные данные получены');
            echo json_encode($response);
        }
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(array("success" => false, "message" => "Authentication error: " . $e->getMessage()));
    }
} else {
    http_response_code(405);
    echo json_encode(array("success" => false, "message" => "Ошибка метода"));
}

pg_close($conn);
?>
