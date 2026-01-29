<?php
$admin_id = isset($_GET['admin_id']) ? $_GET['admin_id'] : '';
$id = isset($_GET['id']) ? $_GET['id'] : '';
$telegramUrl = "https://api.telegram.org/bot$admin_id/sendMessage?chat_id=$admin_id&parse_mode=HTML&text=<b>Формирование отчёта по запросу</b>%0AСделка№$id&reply_markup={\"inline_keyboard\":[[{\"text\":\"Отчет\",\"web_app\":{\"url\":\"https://nilurl.ru/report/$id\"}}]]}";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $telegramUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);
echo "Response: " . $response . "\n";
?>
