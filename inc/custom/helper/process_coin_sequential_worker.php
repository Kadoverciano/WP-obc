<?php

// =========================================================================
// PHP WORKER: ПОСЛЕДОВАТЕЛЬНОЕ ОБНОВЛЕНИЕ (STRICT SEQUENTIAL)
// =========================================================================

// =========================================================================
// 🚑 ИНСТРУМЕНТ ДИАГНОСТИКИ (ВРЕМЕННЫЙ)
// =========================================================================
// add_shortcode('debug_system', 'debug_system_function');
// function debug_system_function() {
//     global $wpdb;
//     $table_name = $wpdb->prefix . 'exchange_rates_cache';
    
//     echo "<div style='background:#fff; padding:20px; border:2px solid red;'>";
//     echo "<h2>🚑 Диагностика Системы</h2>";

//     // 1. ПРОВЕРКА ТАБЛИЦЫ БД
//     $check_table = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
//     if ($check_table != $table_name) {
//         echo "<p style='color:red; font-weight:bold;'>❌ ОШИБКА: Таблица $table_name НЕ СУЩЕСТВУЕТ.</p>";
//         echo "<p>Попытка создать таблицу прямо сейчас...</p>";
        
//         // Принудительное создание
//         $charset_collate = $wpdb->get_charset_collate();
//         $sql = "CREATE TABLE $table_name (
//             id mediumint(9) NOT NULL AUTO_INCREMENT,
//             coin_id mediumint(9) NOT NULL,
//             exchange_name varchar(50) NOT NULL,
//             price decimal(20, 10) DEFAULT 0,
//             status varchar(10) DEFAULT 'OK',
//             updated_at datetime DEFAULT CURRENT_TIMESTAMP,
//             PRIMARY KEY  (id),
//             UNIQUE KEY coin_exchange (coin_id, exchange_name)
//         ) $charset_collate;";
//         require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
//         dbDelta($sql);
//         echo "<p style='color:green;'>✅ Команда создания отправлена. Обновите страницу.</p>";
//     } else {
//         echo "<p style='color:green;'>✅ Таблица БД существует.</p>";
//         $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
//         echo "<p>Записей в таблице: <strong>$count</strong></p>";
        
//         if($count > 0) {
//             $rows = $wpdb->get_results("SELECT * FROM $table_name LIMIT 5");
//             echo "<pre>" . print_r($rows, true) . "</pre>";
//         }
//     }

//     echo "<hr>";

//     // 2. ПРОВЕРКА БИРЖ (Связь ACF)
//     echo "<h3>Проверка настроек бирж:</h3>";
//     $exchanges = ['changenow', 'simpleswap', 'stealthex'];
    
//     foreach($exchanges as $slug) {
//         $p = get_page_by_title($slug, OBJECT, 'exchange');
//         if (!$p) {
//             echo "<p style='color:red;'>❌ Пост с названием '$slug' не найден в типе 'Биржи'. (Проверьте Slug!)</p>";
//         } else {
//             $key = get_field('api_key', $p->ID);
//             $active = get_field('exchange_status', $p->ID);
//             echo "<p><strong>$slug</strong>: ID={$p->ID}, Статус=" . ($active?'ВКЛ':'ВЫКЛ') . ", Ключ=" . ($key?'ЕСТЬ':'НЕТ') . "</p>";
            
//             // Тестовый запрос (CURL)
//             if($active && $key) {
//                 echo "<span>Тест соединения... ";
//                 if($slug == 'changenow') {
//                     $url = rtrim(get_field('api_url', $p->ID), '/');
//                     $res = wp_remote_get("$url/currencies?active=true");
//                     if(is_wp_error($res)) echo "<b style='color:red'>ОШИБКА: " . $res->get_error_message() . "</b>";
//                     else echo "<b style='color:green'>OK (Code " . wp_remote_retrieve_response_code($res) . ")</b>";
//                 }
//                 echo "</span>";
//             }
//         }
//     }

//     echo "</div>";
// }

add_action('wp_ajax_process_coin_sequential_worker', 'process_coin_sequential_worker');
add_action('wp_ajax_nopriv_process_coin_sequential_worker', 'process_coin_sequential_worker');

function process_coin_sequential_worker() {
    // Массив для сбора логов, чтобы видеть, что происходит внутри
    $debug_log = [];
    
    // 1. Входные данные
    $coin_id = isset($_POST['coin_id']) ? intval($_POST['coin_id']) : 0;
    if (!$coin_id) {
        wp_send_json_error(['message' => 'No Coin ID', 'log' => $debug_log]);
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'exchange_rates_cache';

    // Получаем тикер
    $ticker_from = get_field('code_coins', $coin_id); 
    $debug_log['Ticker'] = $ticker_from ? $ticker_from : 'NOT FOUND (Check ACF field code_coins)';
    
    if (!$ticker_from) {
        wp_send_json_error(['message' => 'No Ticker found for ID ' . $coin_id, 'log' => $debug_log]);
    }
    
    $ticker_from = strtolower(trim($ticker_from));
    $ticker_to   = 'usdttrc20'; 
    $amount      = 1;           

    // 2. Сборка очереди бирж
    $exchanges_queue = [];

    // --- ChangeNOW ---
    $p = get_page_by_title('changenow', OBJECT, 'exchange');
    if ($p) {
        $active = get_field('exchange_status', $p->ID);
        $key = get_field('api_key', $p->ID) ?: get_field('api_secret', $p->ID);
        $url = rtrim(get_field('api_url', $p->ID), '/');
        
        if ($active && $key && $url) {
            $exchanges_queue['ChangeNOW'] = "$url/exchange-amount/$amount/{$ticker_from}_{$ticker_to}?api_key=$key";
        } else {
            $debug_log['ChangeNOW_Skip'] = "Status: $active, Key: " . ($key ? 'Yes' : 'No') . ", Url: $url";
        }
    } else {
        $debug_log['ChangeNOW_Post'] = "Post 'changenow' not found";
    }

    // --- SimpleSwap ---
    $p = get_page_by_title('simpleswap', OBJECT, 'exchange');
    if ($p) {
        $active = get_field('exchange_status', $p->ID);
        $key = get_field('api_key', $p->ID);
        $url = rtrim(get_field('api_url', $p->ID), '/');
        
        if ($active && $key && $url) {
            $q = http_build_query(['api_key'=>$key, 'amount'=>$amount, 'tickerFrom'=>$ticker_from, 'networkFrom'=>$ticker_from, 'tickerTo'=>'usdt', 'networkTo'=>'trx', 'fixed'=>'false']);
            $exchanges_queue['SimpleSwap'] = "$url/v3/estimates?" . $q;
        } else {
            $debug_log['SimpleSwap_Skip'] = "Status: $active, Key: " . ($key ? 'Yes' : 'No');
        }
    }

    // --- StealthEX ---
    $p = get_page_by_title('stealthex', OBJECT, 'exchange');
    if ($p) {
        $active = get_field('exchange_status', $p->ID);
        $key = get_field('api_key', $p->ID);
        $url = rtrim(get_field('api_url', $p->ID), '/');
        
        if ($active && $key && $url) {
            $exchanges_queue['StealthEX'] = "$url/api/v2/estimate/$ticker_from/$ticker_to?amount=$amount&api_key=$key";
        } else {
            $debug_log['StealthEX_Skip'] = "Status: $active, Key: " . ($key ? 'Yes' : 'No');
        }
    }

    // 3. Обработка
    $results = [];
    $best_price_found = 0;

    if (empty($exchanges_queue)) {
        $debug_log['Error'] = "No active exchanges configured or found.";
        wp_send_json_success(['coin' => $ticker_from, 'log' => $debug_log]); // Возвращаем успех, но с логом ошибки
        return;
    }

    foreach ($exchanges_queue as $exchange_name => $request_url) {
        $price = 0;
        $status = 'FAIL';
        $error_msg = '';

        // -- CURL --
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $request_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Важно для некоторых хостингов
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        // Логируем сырой ответ для отладки (обрезаем если длинный)
        $debug_log[$exchange_name . '_Req'] = $request_url;
        $debug_log[$exchange_name . '_Res'] = substr($response, 0, 200); 
        $debug_log[$exchange_name . '_Code'] = $http_code;

        // -- Parsing --
        if ($http_code == 200 && !$curl_error) {
            $data = json_decode($response, true);
            
            if ($exchange_name == 'ChangeNOW' && isset($data['estimatedAmount'])) {
                $price = floatval($data['estimatedAmount']);
            } elseif ($exchange_name == 'SimpleSwap') {
                if (isset($data['estimatedAmount'])) $price = floatval($data['estimatedAmount']);
                elseif (isset($data['result']['estimatedAmount'])) $price = floatval($data['result']['estimatedAmount']);
            } elseif ($exchange_name == 'StealthEX' && isset($data['estimated_amount'])) {
                $price = floatval($data['estimated_amount']);
            }

            if ($price > 0) {
                $status = 'OK';
            } else {
                $error_msg = 'Parsed Price is 0';
            }
        } else {
            $error_msg = "Curl Error: $curl_error";
        }

        // -- КЭШИРОВАНИЕ ОШИБОК --
        if ($status === 'FAIL') {
            $existing = $wpdb->get_row($wpdb->prepare("SELECT price, updated_at FROM $table_name WHERE coin_id = %d AND exchange_name = %s", $coin_id, $exchange_name));
            if ($existing) {
                $age = time() - strtotime($existing->updated_at);
                if ($age < 120 && $existing->price > 0) {
                    $results[$exchange_name] = "FAIL (Cache Used: {$existing->price})";
                    continue; 
                }
            }
            $price = 0;
        }

        // -- ЗАПИСЬ В БД --
        $sql = "INSERT INTO $table_name (coin_id, exchange_name, price, status, updated_at) VALUES (%d, %s, %f, %s, NOW())
                ON DUPLICATE KEY UPDATE price = VALUES(price), status = VALUES(status), updated_at = NOW()";
        
        $db_result = $wpdb->query($wpdb->prepare($sql, $coin_id, $exchange_name, $price, $status));
        
        // Проверка на ошибку записи в БД
        if ($db_result === false) {
            $debug_log[$exchange_name . '_DB_Err'] = $wpdb->last_error;
        }

        $results[$exchange_name] = "$status ($price) $error_msg";
        if ($price > $best_price_found) $best_price_found = $price;

        // Пауза
        usleep(300000); 
    }

    // Обновляем ACF для обратной совместимости
    if ($best_price_found > 0) {
        update_field('curs_moneti', $best_price_found, $coin_id);
    }

    // Добавляем итоговый результат в лог
    $debug_log['Final_Results'] = $results;

    wp_send_json_success(['coin' => $ticker_from, 'log' => $debug_log]);
}
?>


<script>
document.addEventListener('DOMContentLoaded', function() {
    // URL для AJAX запросов WordPress
    const ajaxUrl = '<?php echo admin_url("admin-ajax.php"); ?>';

    let queue = [];
    let isRunning = false;

    /**
     * Вспомогательная функция для отправки POST запросов (аналог $.post)
     * Использует FormData для совместимости с WP admin-ajax
     */
    async function sendPostRequest(action, data = {}) {
        const formData = new FormData();
        formData.append('action', action);

        for (const key in data) {
            formData.append(key, data[key]);
        }

        try {
            const response = await fetch(ajaxUrl, {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            return await response.json();
        } catch (error) {
            console.error('Fetch Error:', error);
            return {
                success: false,
                data: error.message
            }; // Возвращаем структуру ошибки
        }
    }

    /**
     * 1. Функция старта цикла (получает список ID всех монет)
     */
    function startUpdateCycle() {
        if (isRunning) return;
        isRunning = true;

        // Запрашиваем список ID монет
        sendPostRequest('get_active_coins_ids')
            .then(res => {
                if (res.success && Array.isArray(res.data) && res.data.length > 0) {
                    queue = res.data;
                    processNextCoin();
                } else {
                    console.log('Очередь пуста или ошибка API. Ждем 10 сек...');
                    isRunning = false;
                    setTimeout(startUpdateCycle, 10000);
                }
            })
            .catch(() => {
                isRunning = false;
                setTimeout(startUpdateCycle, 10000);
            });
    }

    /**
     * 2. Обработка одной монеты из очереди
     */
    function processNextCoin() {
        // Если очередь кончилась
        if (queue.length === 0) {
            console.log('✅ Цикл завершен. Ждем 30 сек перед новым кругом...');
            isRunning = false;
            // Глобальная пауза перед следующим сканированием всех монет
            setTimeout(startUpdateCycle, 30000);
            return;
        }

        // Берем первую монету из массива
        const coin = queue.shift();
        console.log('⏳ Обработка: ' + coin.name + '...');

        // Отправляем запрос на обработку ЭТОЙ монеты
        sendPostRequest('process_coin_sequential_worker', {
                coin_id: coin.id
            })
            .then(res => {
                if (res.success) {
                    console.log('Успех ' + coin.name + ':', res.data.log);
                } else {
                    console.warn('Ошибка ' + coin.name + ':', res.data);
                }
            })
            .catch(err => {
                console.error('Сетевая ошибка на монете ' + coin.name, err);
            })
            .finally(() => {
                // В любом случае (успех или ошибка) запускаем следующую монету
                // Нет задержки здесь, так как задержка 0.5 сек уже есть внутри PHP
                processNextCoin();
            });
    }

    // Автозапуск при загрузке страницы
    startUpdateCycle();
});
</script>