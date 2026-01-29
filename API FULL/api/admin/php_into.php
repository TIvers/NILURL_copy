<?php
require_once 'db_pdo.php';

try {
    $sql = "INSERT INTO public.redirects (code_url, redirect, device, country, city, country_code, os, browser, \"time\", id)
            VALUES ";
    
    $locations = [
        'Великобритания' => ['cities' => ['Лондон', 'Бирмингем', 'Ковентри'], 'code' => 'GB'],
        'Россия' => ['cities' => ['Москва', 'Санкт-Петербург', 'Новосибирск', 'Нижневартовск'], 'code' => 'RU'],
        'Германия' => ['cities' => ['Берлин', 'Мюнхен', 'Франкфурт-на-Майне'], 'code' => 'DE'],
        'Соединенные Штаты' => ['cities' => ['Нью-Йорк', 'Лос-Анджелес', 'Хиллсборо'], 'code' => 'US'],
        'Франция' => ['cities' => ['Париж', 'Марсель', 'Лион'], 'code' => 'FR'],
        'Испания' => ['cities' => ['Мадрид', 'Барселона', 'Валенсия'], 'code' => 'ES'],
        'Италия' => ['cities' => ['Рим', 'Милан', 'Неаполь'], 'code' => 'IT'],
        'Канада' => ['cities' => ['Торонто', 'Ванкувер', 'Монреаль'], 'code' => 'CA'],
        'Австралия' => ['cities' => ['Сидней', 'Мельбурн', 'Брисбен'], 'code' => 'AU']
    ];

    $os_list = [
        'Windows 10', 'Windows 8.1', 'Windows 8', 'Mac OS X', 'Mac OS 9', 'Linux', 'Ubuntu', 'iPhone', 'iPod', 'iPad', 'Android', 'BlackBerry'
    ];

    $browser_list = ['Edge', 'Chrome', 'Firefox', 'Safari', 'Opera'];

    for ($i = 0; $i < 2000; $i++) {
        $random_time = date('Y-m-d H:i:s', mt_rand(strtotime('2024-07-01 00:00:00'), strtotime('2024-07-27 1:00:00')));
        
        $code_url = 'cat';
        $redirect = 'false';
        $device = mt_rand(0, 1) ? 'Компьютер' : 'Телефон';
        $country = array_rand($locations);
        $city = $locations[$country]['cities'][array_rand($locations[$country]['cities'])];
        $country_code = $locations[$country]['code'];
        $os = $os_list[array_rand($os_list)];
        $browser = $browser_list[array_rand($browser_list)];
        $id = 586 + $i;

        $sql .= "('$code_url', '$redirect', '$device', '$country', '$city', '$country_code', '$os', '$browser', '$random_time', $id),";
    }

    $sql = rtrim($sql, ',') . ';';

    $pdo->exec($sql);

    echo "Данные успешно внесены.";
} catch (PDOException $e) {
    echo "Ошибка при выполнении запроса: " . $e->getMessage();
}
?>