<?php 


/**
 * Драйвер ChangeNOW
 */
function api_driver_changenow_get_amount($from_ticker, $to_ticker, $amount_send) {
    $exchange_post = get_page_by_title('changenow', OBJECT, 'exchange');
    if (!$exchange_post) return 0;
    
    $api_url = rtrim(get_field('api_url', $exchange_post->ID), '/');
    $api_key = get_field('api_key', $exchange_post->ID) ?: get_field('api_secret', $exchange_post->ID);
    $is_active = get_field('exchange_status', $exchange_post->ID);

    if (!$api_url || !$api_key || !$is_active) return 0;

    $from = strtolower(trim($from_ticker));
    $to   = strtolower(trim($to_ticker));
    $request_url = "$api_url/exchange-amount/$amount_send/{$from}_{$to}?api_key=$api_key";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $request_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5); 
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code !== 200 || !$response) return 0; 
    $data = json_decode($response, true);
    if (isset($data['estimatedAmount'])) return floatval($data['estimatedAmount']);
    return 0;
}


// === ДИАГНОСТИКА CHANGENOW (Удали после проверки) ===
add_action('init', function() {
    if (isset($_GET['test_changenow'])) {
        
        echo '<h2>🕵️ Тест связи с ChangeNOW (через админку)</h2>';
        
        // 1. Ищем пост настроек
        $exchange_post = get_page_by_title('changenow', OBJECT, 'exchange');
        
        if (!$exchange_post) {
            echo "<p style='color:red; font-weight:bold;'>❌ ОШИБКА: Пост с заголовком 'changenow' не найден в разделе 'Биржи'.</p>";
            echo "<p>Убедитесь, что заголовок написан маленькими буквами: <code>changenow</code></p>";
            exit;
        } else {
            echo "<p style='color:green;'>✅ Пост настроек найден (ID: {$exchange_post->ID})</p>";
        }

        // 2. Получаем поля
        $api_url = get_field('api_url', $exchange_post->ID);
        $api_key = get_field('api_key', $exchange_post->ID);
        $is_active = get_field('exchange_status', $exchange_post->ID);

        // Проверка URL
        $api_url = rtrim($api_url, '/');
        echo "<strong>API URL:</strong> " . ($api_url ? $api_url : "<span style='color:red'>ПУСТО</span>") . "<br>";
        
        // Проверка Ключа
        echo "<strong>API Key:</strong> " . ($api_key ? substr($api_key, 0, 5) . "..." : "<span style='color:red'>ПУСТО (Проверь, не остался ли он в поле 'Секрет'?)</span>") . "<br>";
        
        // Проверка Статуса
        echo "<strong>Статус:</strong> " . ($is_active ? "<span style='color:green'>Активна</span>" : "<span style='color:red'>Выключена (Галочка не стоит)</span>") . "<br>";

        if (!$api_url || !$api_key) {
            echo "<h3>⛔ Тест остановлен: заполните поля в админке.</h3>";
            exit;
        }

        // 3. Пробуем реальный запрос
        echo "<hr><h3>🔄 Пробуем получить курс 1 BTC -> XMR...</h3>";
        
        $amount = 1;
        $from = 'btc';
        $to = 'xmr';
        
        $request_url = "$api_url/exchange-amount/$amount/{$from}_{$to}?api_key=$api_key";
        echo "<small>Запрос: $api_url/exchange-amount/$amount/{$from}_{$to}...</small><br><br>";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $request_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        echo "<strong>HTTP Code:</strong> $http_code<br>";

        if ($http_code == 200) {
            $data = json_decode($response, true);
            echo "<h2 style='color:green;'>🎉 УСПЕХ! API работает.</h2>";
            echo "<pre style='background:#eee; padding:10px;'>" . print_r($data, true) . "</pre>";
            
            if (isset($data['estimatedAmount'])) {
                echo "<p style='font-size:18px;'>Результат: за <strong>1 BTC</strong> дают <strong>" . $data['estimatedAmount'] . " XMR</strong></p>";
            }
        } else {
            echo "<h2 style='color:red;'>⛔ Ошибка запроса</h2>";
            if ($curl_error) echo "Curl Error: $curl_error<br>";
            echo "Ответ сервера: <pre>" . htmlspecialchars($response) . "</pre>";
        }

        exit;
    }
});