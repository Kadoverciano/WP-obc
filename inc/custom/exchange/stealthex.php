<?php 

/**
 * Драйвер для StealthEX API
 * Документация: https://documenter.getpostman.com/view/12066406/TVYF7eiZ
 */
function api_driver_stealthex_get_amount($from_ticker, $to_ticker, $amount_send) {
    
    $exchange_post = get_page_by_title('stealthex', OBJECT, 'exchange');
    if (!$exchange_post) return 0;
    
    $api_url = rtrim(get_field('api_url', $exchange_post->ID), '/');
    $api_key = get_field('api_key', $exchange_post->ID);
    $is_active = get_field('exchange_status', $exchange_post->ID);

    if (!$api_url || !$api_key || !$is_active) return 0;

    $from = strtolower(trim($from_ticker));
    $to   = strtolower(trim($to_ticker));
    
    // Формат запроса StealthEX: /api/v2/estimate/{from}/{to}?amount={amount}&api_key={key}
    $request_url = "$api_url/api/v2/estimate/$from/$to?amount=$amount_send&api_key=$api_key";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $request_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5); 
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code !== 200 || !$response) return 0; 

    $data = json_decode($response, true);
    
    // StealthEX возвращает JSON: { "estimated_amount": "123.45" }
    if (isset($data['estimated_amount'])) {
        return floatval($data['estimated_amount']);
    }
    
    return 0;
}

// === ДИАГНОСТИКА STEALTHEX (Красивый вывод) ===
add_action('init', function() {
    if (isset($_GET['test_stealthex'])) {
        
        echo '<h2>🕵️ Тест связи с StealthEX (через админку)</h2>';
        
        // 1. Ищем пост настроек
        $exchange_post = get_page_by_title('stealthex', OBJECT, 'exchange');
        
        if (!$exchange_post) {
            echo "<p style='color:red; font-weight:bold;'>❌ ОШИБКА: Пост с заголовком 'stealthex' не найден в разделе 'Биржи'.</p>";
            echo "<p>Убедитесь, что заголовок написан маленькими буквами: <code>stealthex</code></p>";
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

        // 3. Пробуем реальный запрос
        echo "<hr><h3>🔄 Пробуем получить курс 1 BTC -> XMR...</h3>";
        
        $amount = 1;
        $from = 'btc';
        $to = 'xmr';
        
        // Формируем URL для StealthEX API v2
        // Формат: /api/v2/estimate/{from}/{to}?amount={amount}&api_key={key}
        $request_url = "$api_url/api/v2/estimate/$from/$to?amount=$amount&api_key=$api_key";
        
        // Скрываем ключ для вывода на экран
        $display_url = str_replace($api_key, 'СКРЫТ', $request_url);
        echo "<small>Запрос: $display_url</small><br><br>";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $request_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        // StealthEX тоже может требовать User-Agent
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        echo "<strong>HTTP Code:</strong> $http_code<br>";

        if ($http_code == 200) {
            $data = json_decode($response, true);
            echo "<h2 style='color:green;'>🎉 УСПЕХ! API работает.</h2>";
            // Красивый вывод массива как у SimpleSwap
            echo "<pre style='background:#eee; padding:10px;'>" . print_r($data, true) . "</pre>";
            
            // StealthEX возвращает JSON: { "estimated_amount": 123.45 }
            // У SimpleSwap это estimatedAmount, у StealthEX - estimated_amount (с подчеркиванием)
            if (isset($data['estimated_amount'])) {
                echo "<p style='font-size:18px;'>Результат: за <strong>1 BTC</strong> дают <strong>" . $data['estimated_amount'] . " XMR</strong></p>";
            }
        } else {
            echo "<h2 style='color:red;'>⛔ Ошибка запроса</h2>";
            if ($curl_error) echo "Curl Error: $curl_error<br>";
            echo "Ответ сервера: <pre>" . htmlspecialchars($response) . "</pre>";
        }

        exit;
    }
});