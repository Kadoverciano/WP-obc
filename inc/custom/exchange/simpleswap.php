<?php

/**
 * Драйвер SimpleSwap (V3)
 */
function api_driver_simpleswap_get_amount($from_ticker, $to_ticker, $amount_send) {
    $exchange_post = get_page_by_title('simpleswap', OBJECT, 'exchange');
    if (!$exchange_post) return 0;
    
    $api_url = rtrim(get_field('api_url', $exchange_post->ID), '/');
    $api_key = get_field('api_key', $exchange_post->ID);
    $is_active = get_field('exchange_status', $exchange_post->ID);

    if (!$api_url || !$api_key || !$is_active) return 0;

    $from = strtolower(trim($from_ticker));
    $to   = strtolower(trim($to_ticker));
    
    $query_params = [
        'api_key' => $api_key,
        'amount' => $amount_send,
        'tickerFrom' => $from,
        'networkFrom' => $from,
        'tickerTo' => $to,
        'networkTo' => $to,
        'fixed' => 'false'
    ];
    
    $request_url = "$api_url/v3/estimates?" . http_build_query($query_params);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $request_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5); 
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code !== 200 || !$response) return 0; 
    $data = json_decode($response, true);
    
    if (isset($data['result']['estimatedAmount'])) return floatval($data['result']['estimatedAmount']);
    if (isset($data['estimatedAmount'])) return floatval($data['estimatedAmount']);
    return 0;
}

// === ДИАГНОСТИКА SIMPLESWAP V3 (Удали после проверки) ===
add_action('init', function() {
    if (isset($_GET['test_simpleswap'])) {
        
        echo '<h2>🕵️ Тест связи с SimpleSwap V3 (через админку)</h2>';
        
        // 1. Ищем пост настроек
        $exchange_post = get_page_by_title('simpleswap', OBJECT, 'exchange');
        
        if (!$exchange_post) {
            echo "<p style='color:red; font-weight:bold;'>❌ ОШИБКА: Пост с заголовком 'simpleswap' не найден в разделе 'Биржи'.</p>";
            echo "<p>Убедитесь, что заголовок написан маленькими буквами: <code>simpleswap</code></p>";
            exit;
        } else {
            echo "<p style='color:green;'>✅ Пост настроек найден (ID: {$exchange_post->ID})</p>";
        }

        // 2. Получаем поля
        $api_url = get_field('api_url', $exchange_post->ID);
        $api_key = get_field('api_key', $exchange_post->ID);
        $is_active = get_field('exchange_status', $exchange_post->ID);

        // Проверка URL
        $api_url = rtrim($api_url, '/'); // Убираем слеш на конце
        echo "<strong>API URL:</strong> " . ($api_url ? $api_url : "<span style='color:red'>ПУСТО</span>") . "<br>";
        
        // Проверка Ключа
        echo "<strong>API Key:</strong> " . ($api_key ? substr($api_key, 0, 5) . "..." : "<span style='color:red'>ПУСТО</span>") . "<br>";
        
        // Проверка Статуса
        echo "<strong>Статус:</strong> " . ($is_active ? "<span style='color:green'>Активна</span>" : "<span style='color:red'>Выключена (Галочка не стоит)</span>") . "<br>";

        if (!$api_url || !$api_key) {
            echo "<h3>⛔ Тест остановлен: заполните поля в админке.</h3>";
            exit;
        }

        // 3. Пробуем реальный запрос (V3)
        echo "<hr><h3>🔄 Пробуем получить курс 1 BTC -> XMR...</h3>";
        
        $amount = 1;
        $from = 'btc'; // ticker
        $to = 'xmr';   // ticker
        
        // Формируем параметры для V3
        $query_params = [
            'api_key' => $api_key,
            'amount' => $amount,
            'tickerFrom' => $from,
            'networkFrom' => $from, // Для BTC сеть тоже btc
            'tickerTo' => $to,
            'networkTo' => $to,     // Для XMR сеть тоже xmr
            'fixed' => 'false'
        ];
        
        $request_url = "$api_url/v3/estimates?" . http_build_query($query_params);
        
        // Скрываем ключ для вывода на экран
        $display_url = str_replace($api_key, 'СКРЫТ', $request_url);
        echo "<small>Запрос: $display_url</small><br><br>";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $request_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        // SimpleSwap иногда требует User-Agent
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        echo "<strong>HTTP Code:</strong> $http_code<br>";

        if ($http_code == 200) {
            $data = json_decode($response, true);
            echo "<h2 style='color:green;'>🎉 УСПЕХ! API работает.</h2>";
            echo "<pre style='background:#eee; padding:10px;'>" . print_r($data, true) . "</pre>";
            
            // Пытаемся найти сумму в ответе V3
            $result = 0;
            if (isset($data['result']) && is_array($data['result'])) {
                $result = $data['result']['estimatedAmount'] ?? 0;
            } elseif (isset($data['estimatedAmount'])) {
                $result = $data['estimatedAmount'];
            }

            if ($result > 0) {
                echo "<p style='font-size:18px;'>Результат: за <strong>1 BTC</strong> дают <strong>$result XMR</strong></p>";
            }
        } else {
            echo "<h2 style='color:red;'>⛔ Ошибка запроса</h2>";
            if ($curl_error) echo "Curl Error: $curl_error<br>";
            echo "Ответ сервера: <pre>" . htmlspecialchars($response) . "</pre>";
        }

        exit;
    }
});