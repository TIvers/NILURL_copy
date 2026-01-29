<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}
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

if (isset($_GET['url'])) {
    $url = filter_var($_GET['url'], FILTER_SANITIZE_URL);
    echo getFavicon($url);
} else {
    echo "Invalid URL";
}

?>
