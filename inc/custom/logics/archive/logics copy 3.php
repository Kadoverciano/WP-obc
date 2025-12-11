<?php 

// =========================================================================
// 2. HELPER FUNCTIONS & ACF FILTERS
// =========================================================================

// Сделать поля Сделок read-only
add_filter('acf/load_field', function($field){
    $readonly_fields = ['user', 'coin-from-deal', 'coin-do-deal', 'coin-do-deal_cart', 'sending_amount', 'amount_received', 'exchange-deal', 'status-deal', 'date-create', 'date-finish'];
    if(in_array($field['name'], $readonly_fields)){
        $field['readonly'] = 1;
        $field['disabled'] = 1;
    }
    return $field;
});

// Блокировка полей курса в админке
add_filter('acf/load_field', 'make_rate_fields_readonly');
function make_rate_fields_readonly( $field ) {
    if ( ! is_admin() || empty( $GLOBALS['pagenow'] ) || ! in_array( $GLOBALS['pagenow'], ['post.php', 'post-new.php'] ) ) { return $field; }
    $post_type = get_post_type(get_the_ID());
    
    if ( ($post_type === 'monety' || $post_type === 'payment_cards') && $field['name'] === 'curs_moneti' ) {
        $field['readonly'] = 1;
        $field['instructions'] = '<strong>🔒 АВТО-КУРС.</strong> Это поле обновляется автоматически системой мониторинга.';
    }
    if ( $post_type === 'rate' && ($field['name'] === 'well' || $field['name'] === 'well_2') ) {
        $field['readonly'] = 1;
    }
    return $field;
}

/**
 * Вспомогательная: Очищает число от пробелов и запятых
 */
function sanitize_crypto_number( $number_string ) {
    if ( is_numeric($number_string) ) return floatval($number_string);
    $number_string = str_replace(' ', '', $number_string);
    $number_string = str_replace(',', '.', $number_string);
    return floatval($number_string);
}

// =========================================================================
// 3. API ДРАЙВЕРЫ (MOTORS)
// =========================================================================

get_template_part('inc/custom/exchange/changenow');
get_template_part('inc/custom/exchange/simpleswap');
get_template_part('inc/custom/exchange/stealthex');


// =========================================================================
// 4. ФУНКЦИЯ ОБНОВЛЕНИЯ БАЗЫ (ИЩЕТ ЛУЧШИЙ USDT КУРС)
// =========================================================================

function update_coin_price_in_db($coin_id) {
    $ticker = get_field('code_coins', $coin_id);
    if (!$ticker) return false;

    // Опрашиваем биржи: Цена 1 монеты в USDT
    // Пробуем usdt и usdttrc20 чтобы избежать нулей
    $targets = ['usdt', 'usdttrc20'];
    $best_usd_price = 0;

    foreach ($targets as $to_curr) {
        $p1 = 0; $p2 = 0; $p3 = 0;
        
        if(function_exists('api_driver_changenow_get_amount')) 
            $p1 = api_driver_changenow_get_amount($ticker, $to_curr, 1);
        
        if(function_exists('api_driver_simpleswap_get_amount')) 
            $p2 = api_driver_simpleswap_get_amount($ticker, $to_curr, 1);
        
        if(function_exists('api_driver_stealthex_get_amount')) 
            $p3 = api_driver_stealthex_get_amount($ticker, $to_curr, 1);

        $current_max = max($p1, $p2, $p3);
        if ($current_max > 0) {
            $best_usd_price = $current_max;
            break; // Нашли цену, выходим
        }
    }

    // Записываем в базу
    if ($best_usd_price > 0) {
        update_field('curs_moneti', $best_usd_price, $coin_id);
    }
    return $best_usd_price;
}


// =========================================================================
// 5. ГЛАВНАЯ ЛОГИКА РАСЧЕТА (ЧИТАЕТ ИЗ БАЗЫ)
// =========================================================================

function get_true_exchange_rate( $from_id, $to_id ) {
    
    $usdt_to_rub_rate = floatval(get_field( 'stoimost_1_usdt_v_rublyah', 'option' ));
    if ( $usdt_to_rub_rate <= 0 ) return 0; 

    $from_is_card = get_post_type( $from_id ) === 'payment_cards';
    $to_is_card   = get_post_type( $to_id ) === 'payment_cards';

    $from_value_usdt = sanitize_crypto_number(get_field( 'curs_moneti', $from_id ));
    if ( $from_value_usdt <= 0 ) return 0;

    $base_rate = 0;

    if ( !$from_is_card && !$to_is_card ) { // Крипто -> Крипто
        $to_value_usdt = sanitize_crypto_number(get_field( 'curs_moneti', $to_id ));
        if ( $to_value_usdt <= 0 ) return 0;
        $base_rate = $from_value_usdt / $to_value_usdt;

    } elseif ( !$from_is_card && $to_is_card ) { // Крипто -> Рубли
        // Для карты curs_moneti должно быть 1
        $to_value_usdt = sanitize_crypto_number(get_field( 'curs_moneti', $to_id ));
        if ( $to_value_usdt <= 0 ) return 0;
        $base_rate = $from_value_usdt * $usdt_to_rub_rate;
    } 
    
    return $base_rate;
}

// =========================================================================
// 6. AJAX ОБРАБОТЧИК: ОБНОВЛЕНИЕ И МОНИТОРИНГ (ВСЕ В ОДНОМ)
// =========================================================================

add_action('wp_ajax_get_monitor_tables', 'ajax_get_monitor_tables');
add_action('wp_ajax_nopriv_get_monitor_tables', 'ajax_get_monitor_tables');

function ajax_get_monitor_tables() {
    
    // ---------------------------------------------------------
    // ШАГ 1: ОБНОВЛЯЕМ ЦЕНЫ В БАЗЕ (ФОНОВЫЙ ПРОЦЕСС)
    // ---------------------------------------------------------
    $all_monety = get_posts(['post_type' => 'monety', 'numberposts' => -1]);
    foreach ($all_monety as $coin) {
        update_coin_price_in_db($coin->ID);
    }
    
    // ---------------------------------------------------------
    // ШАГ 2: ГЕНЕРИРУЕМ ТАБЛИЦЫ ДЛЯ МОНИТОРА (ДЛЯ АДМИНА)
    // ---------------------------------------------------------
    $active_rates = get_posts([
        'post_type' => 'rate', 'posts_per_page' => -1, 'post_status' => 'publish',
        'meta_query' => [['key' => 'status', 'value' => '1', 'compare' => '=']]
    ]);

    $pairs_data = [];

    foreach ($active_rates as $rate_post) {
        $from_coin = get_field('coin_from', $rate_post->ID);
        $to_coin = null;
        $is_card = false;
        
        if (get_field('i-cart', $rate_post->ID) == 1) {
            $to_coin = get_field('coin_do_cart', $rate_post->ID);
            $is_card = true;
        } else {
            $to_coin = get_field('coin_do', $rate_post->ID);
        }

        if ($from_coin && $to_coin) {
            $pair_name = $from_coin->post_title . ' -> ' . $to_coin->post_title;
            $from_ticker = get_field('code_coins', $from_coin->ID);
            $to_ticker   = $is_card ? 'RUB' : get_field('code_coins', $to_coin->ID);

            // Получаем прямые курсы (для наглядности в таблице)
            $market_rates = ['ChangeNOW' => 0, 'SimpleSwap' => 0, 'StealthEX' => 0];
            if (!$is_card && $from_ticker && $to_ticker) {
                $amount = 1;
                if(function_exists('api_driver_changenow_get_amount')) $market_rates['ChangeNOW'] = api_driver_changenow_get_amount($from_ticker, $to_ticker, $amount);
                if(function_exists('api_driver_simpleswap_get_amount')) $market_rates['SimpleSwap'] = api_driver_simpleswap_get_amount($from_ticker, $to_ticker, $amount);
                if(function_exists('api_driver_stealthex_get_amount')) $market_rates['StealthEX'] = api_driver_stealthex_get_amount($from_ticker, $to_ticker, $amount);
            }

            $best_market_rate = max($market_rates);
            
            // Курс из базы (который мы только что обновили в Шаге 1)
            $base_rate_db = get_true_exchange_rate($from_coin->ID, $to_coin->ID);
            
            $percent_markup = floatval(get_field('proczent_komissii', 'option'));
            $client_rate = $base_rate_db * (1 - $percent_markup / 100);

            $pairs_data[] = [
                'pair' => $pair_name,
                'rates' => $market_rates,
                'best' => $best_market_rate,
                'client' => $client_rate,
                'is_card' => $is_card
            ];
        }
    }

    ob_start();
    ?>
<div style="display: flex; gap: 20px; flex-wrap: wrap;">
    <div style="flex: 1; min-width: 250px;">
        <h4>🟣 ChangeNOW (Чистый)</h4>
        <table class="live-rates-table">
            <?php foreach ($pairs_data as $p): if($p['is_card']) continue; ?>
            <tr>
                <td><?php echo $p['pair']; ?></td>
                <td><?php echo $p['rates']['ChangeNOW'] ?: '-'; ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <div style="flex: 1; min-width: 250px;">
        <h4>🔵 SimpleSwap (Чистый)</h4>
        <table class="live-rates-table">
            <?php foreach ($pairs_data as $p): if($p['is_card']) continue; ?>
            <tr>
                <td><?php echo $p['pair']; ?></td>
                <td><?php echo $p['rates']['SimpleSwap'] ?: '-'; ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <div style="flex: 1; min-width: 250px;">
        <h4>🟡 StealthEX (Чистый)</h4>
        <table class="live-rates-table">
            <?php foreach ($pairs_data as $p): if($p['is_card']) continue; ?>
            <tr>
                <td><?php echo $p['pair']; ?></td>
                <td><?php echo $p['rates']['StealthEX'] ?: '-'; ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>

<div style="margin-top: 30px; border-top: 2px solid #ddd; padding-top: 20px;">
    <h3>✅ ИТОГ: Что записано в базу (С наценкой)</h3>
    <p>Внимание: Курс клиента рассчитывается как
        <code>Цена_Монеты_USDT / Цена_Получения_USDT * (1 - <?php echo $percent_markup; ?>%)</code>. Цены USDT обновлены
        только что.</p>

    <table class="live-rates-table" style="width: 100%;">
        <thead>
            <tr style="background: #e0f7fa;">
                <th>Пара</th>
                <th>Курс Клиента (Актуальный из базы)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pairs_data as $p): ?>
            <tr>
                <td><strong><?php echo $p['pair']; ?></strong></td>
                <td style="font-weight: bold; color: #007cba;">
                    <?php echo $p['client'] > 0 ? $p['client'] : '-'; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php
    $html = ob_get_clean();
    wp_send_json_success($html);
}


// =========================================================================
// 7. AJAX ОБРАБОТЧИК СОЗДАНИЯ СДЕЛКИ
// =========================================================================

add_action('wp_ajax_create_order_ajax', 'ajax_create_order_handler');
add_action('wp_ajax_nopriv_create_order_ajax', 'ajax_create_order_handler');
function ajax_create_order_handler() {

    if (!isset($_POST['order_nonce']) || !wp_verify_nonce($_POST['order_nonce'], 'create_order_nonce')) {
        wp_send_json_error(['message' => 'Ошибка безопасности.']); return;
    }

    $from_id = intval($_POST['from_id']);
    $to_id   = intval($_POST['to_id']);
    $amount_send = floatval($_POST['amount_send']);
    $contact_info = sanitize_text_field($_POST['contact_info']);
    $receiver_address = sanitize_text_field($_POST['receiver_address']);

    if (empty($from_id) || empty($to_id) || empty($amount_send) || empty($contact_info) || empty($receiver_address)) {
        wp_send_json_error(['message' => 'Все поля обязательны.']); return;
    }
    
    // Обновляем курс перед сделкой, чтобы он был максимально точным
    update_coin_price_in_db($from_id);
    if (get_post_type($to_id) == 'monety') update_coin_price_in_db($to_id);

    $base_rate = get_true_exchange_rate($from_id, $to_id);
    if ($base_rate <= 0) { wp_send_json_error(['message' => 'Не удалось рассчитать курс.']); return; }
    
    $percent_markup = floatval(get_field('proczent_komissii', 'option'));
    $markup_multiplier = 1 - ($percent_markup / 100); 
    $rate_with_markup = $base_rate * $markup_multiplier;
    
    $amount_receive_gross = $amount_send * $rate_with_markup;
    $final_amount_receive = $amount_receive_gross;
    
    $to_is_card = (get_post_type($to_id) === 'payment_cards');
    
    // Вычитаем фиксированную комиссию сети
    if (!$to_is_card) { 
        $network = get_field('native_network', $to_id);
        if ($network && is_object($network)) {
            $network_fee_usd = floatval(get_field('network_fee_usd', $network->ID));
            $to_coin_usd_value = floatval(get_field('curs_moneti', $to_id));
            if ($network_fee_usd > 0 && $to_coin_usd_value > 0) {
                $fee_in_crypto = $network_fee_usd / $to_coin_usd_value;
                $final_amount_receive = $amount_receive_gross - $fee_in_crypto;
            }
        }
    }
    
    if ($final_amount_receive <= 0) {
        wp_send_json_error(['message' => 'Сумма слишком мала.']); return;
    }

    $our_receiver_details = '';
    $from_is_card = (get_post_type($from_id) === 'payment_cards');
    
    if ($from_is_card) {
        $our_receiver_details = get_field('our_card_details', $from_id);
    } else {
        $network = get_field('native_network', $from_id);
        if ($network && is_object($network)) {
            $wallets = get_posts([
                'post_type' => 'wallet', 'posts_per_page' => 1,
                'meta_query' => [['key' => 'assigned_network', 'value' => $network->ID]]
            ]);
            if (!empty($wallets)) {
                $our_receiver_details = get_field('wallet_address', $wallets[0]->ID);
            }
        }
    }

    if (empty($our_receiver_details)) {
        wp_send_json_error(['message' => 'Ошибка: Нет реквизитов для приема.']); return;
    }

    $trade_title = 'Заявка: ' . get_the_title($from_id) . ' на ' . get_the_title($to_id);
    $trade_id = wp_insert_post(['post_type' => 'trade', 'post_title' => $trade_title, 'post_status' => 'publish']);

    if (is_wp_error($trade_id)) {
        wp_send_json_error(['message' => 'Ошибка БД.']); return;
    }

    update_field('user', get_current_user_id(), $trade_id);
    update_field('coin-from-deal', $from_id, $trade_id);
    
    if ($to_is_card) { update_field('coin-do-deal_cart', $to_id, $trade_id); }
    else { update_field('coin-do-deal', $to_id, $trade_id); }
    
    update_field('sending_amount', $amount_send, $trade_id);
    update_field('amount_received', $final_amount_receive, $trade_id);
    update_field('contact_info', $contact_info, $trade_id);
    update_field('receiver_address', $receiver_address, $trade_id);
    update_field('assigned_wallet_address', $our_receiver_details, $trade_id);
    update_field('status-deal', 'Ожидание оплаты', $trade_id);
    update_field('date-create', current_time('d/m/Y g:i a'), $trade_id);
    
    $timer_end_timestamp = time() + (30 * 60);
    update_field('order_timer_end', $timer_end_timestamp, $trade_id);

    wp_send_json_success(['order_url' => get_permalink($trade_id)]);
}

// =========================================================================
// 8. ШОРТКОД ГЛАВНОЙ СТРАНИЦЫ (С АВТО-МОНИТОРОМ)
// =========================================================================

add_shortcode('crypto_exchange_form', 'crypto_exchange_form_shortcode');
function crypto_exchange_form_shortcode() {
    
    $active_rates = get_posts([
        'post_type' => 'rate', 'posts_per_page' => -1, 'post_status' => 'publish',
        'meta_query' => [['key' => 'status', 'value' => '1', 'compare' => '=']]
    ]);

    if (empty($active_rates)) return '<p>Нет направлений.</p>';

    $percent_markup = floatval(get_field('proczent_komissii', 'option'));
    $markup_multiplier = 1 - ($percent_markup / 100);

    $coins_from_list = [];
    $rates_by_from_id = [];

    foreach ($active_rates as $rate_post) {
        $from_coin = get_field('coin_from', $rate_post->ID); 
        if ($from_coin && is_object($from_coin)) {
            
            $coins_from_list[$from_coin->ID] = $from_coin;
            
            $to_coin = null;
            if (get_field('i-cart', $rate_post->ID) == 1) {
                $to_coin = get_field('coin_do_cart', $rate_post->ID);
            } else {
                $to_coin = get_field('coin_do', $rate_post->ID);
            }

            if ($to_coin && is_object($to_coin)) {
                $base_rate = get_true_exchange_rate($from_coin->ID, $to_coin->ID);
                $rate_with_markup = $base_rate * $markup_multiplier;

                $rates_by_from_id[$from_coin->ID][$to_coin->ID] = [
                    'id'    => $to_coin->ID,
                    'title' => $to_coin->post_title,
                    'rate'  => $rate_with_markup,
                    'reserve' => get_field('reserve', $rate_post->ID)
                ];
            }
        }
    }
    
    uasort($coins_from_list, function($a, $b) {
        return strcmp($a->post_title, $b->post_title);
    });

    ob_start(); 
    ?>
<div class="crypto-exchange-wrapper" id="new-exchange-flow">
    <div class="coins-left">
        <h4>Отправляете:</h4>
        <?php foreach ($coins_from_list as $coin_id => $coin): ?>
        <div class="coin-item-left" data-id="<?php echo $coin_id; ?>">
            <?php echo esc_html($coin->post_title); ?>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="coins-right">
        <h4>Получаете:</h4>
        <div id="coins-to-list">
            <div>Выберите монету слева</div>
        </div>
    </div>
</div>

<?php if (current_user_can('administrator')): ?>
<div id="live-monitor-wrapper" style="margin-top: 50px; padding: 20px; background: #f9f9f9; border: 1px solid #ccc;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
        <h3 style="margin:0;">📡 Монитор Курсов (Авто-обновление в базу)</h3>
        <span id="monitor-timer" style="font-weight:bold; color:gray;">Обновление через: 30s</span>
    </div>
    <div id="monitor-content">
        Загрузка данных с бирж... <span class="spinner is-active" style="float:none;"></span>
    </div>
</div>

<style>
.live-rates-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 10px;
    background: white;
    font-size: 13px;
}

.live-rates-table th,
.live-rates-table td {
    border: 1px solid #ddd;
    padding: 6px;
    text-align: left;
}

.live-rates-table th {
    background: #f0f0f0;
}
</style>

<script>
jQuery(document).ready(function($) {
    var timer = 30;
    var interval;

    function loadMonitorData() {
        $('#monitor-timer').text('Обновление...');
        $.ajax({
            url: '<?php echo admin_url("admin-ajax.php"); ?>',
            type: 'POST',
            data: {
                action: 'get_monitor_tables'
            },
            success: function(res) {
                if (res.success) {
                    $('#monitor-content').html(res.data);
                    startTimer();
                }
            }
        });
    }

    function startTimer() {
        timer = 30;
        clearInterval(interval);
        interval = setInterval(function() {
            timer--;
            $('#monitor-timer').text('Обновление через: ' + timer + 's');
            if (timer <= 0) {
                loadMonitorData();
            }
        }, 1000);
    }

    loadMonitorData();
});
</script>
<?php endif; ?>

<script type="application/json" id="exchange_rates_data">
<?php echo json_encode($rates_by_from_id); ?>
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ratesDataEl = document.getElementById('exchange_rates_data');
    if (!ratesDataEl) return;

    const ratesData = JSON.parse(ratesDataEl.textContent);
    const toListContainer = document.getElementById('coins-to-list');
    const wrapper = document.getElementById('new-exchange-flow');

    if (wrapper) {
        wrapper.addEventListener('click', function(e) {
            const leftItem = e.target.closest('.coin-item-left');
            if (leftItem) {
                wrapper.querySelectorAll('.coin-item-left').forEach(item => item.classList.remove(
                    'active'));
                leftItem.classList.add('active');

                const fromId = leftItem.dataset.id;
                let toListHtml = '';
                const availableCoins = ratesData[fromId];

                if (availableCoins && Object.keys(availableCoins).length > 0) {
                    Object.values(availableCoins).forEach(coin => {
                        const pairLink = '<?php echo home_url("/exchange-step/"); ?>' +
                            '?from=' + fromId + '&to=' + coin.id;
                        toListHtml += '<a href="' + pairLink + '" class="coin-item-right">';
                        toListHtml += '<span class="coin-name">' + coin.title + '</span>';
                        toListHtml += '<div class="coin-details">';
                        toListHtml += '<span class="coin-rate">1 -> ' + parseFloat(coin.rate)
                            .toFixed(6) + '</span>';
                        if (coin.reserve) {
                            toListHtml += '<span class="coin-reserve">Резерв: ' + coin.reserve +
                                '</span>';
                        }
                        toListHtml += '</div></a>';
                    });
                    toListContainer.innerHTML = toListHtml;
                } else {
                    toListContainer.innerHTML = '<div>Нет доступных направлений</div>';
                }
            }
        });
    }
    const firstLeftItem = document.querySelector('.coin-item-left');
    if (firstLeftItem) firstLeftItem.click();
});
</script>
<?php
    return ob_get_clean();
}

// =========================================================================
// 9. АДМИНКА: СТРАНИЦА МОНИТОРИНГА
// =========================================================================

add_action('admin_menu', 'register_exchange_monitor_page');
function register_exchange_monitor_page() {
    add_submenu_page('edit.php?post_type=exchange', 'Мониторинг', 'Монитор Курсов', 'manage_options', 'exchange-monitor', 'render_exchange_monitor_page');
}

function render_exchange_monitor_page() {
    ?>
<div class="wrap">
    <h1>📊 Мониторинг (Состояние базы)</h1>
    <p>Здесь отображаются цены, которые <strong>уже записаны</strong> в базу данных.</p>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>Монета</th>
                <th>Тикер</th>
                <th>Цена в базе (USDT)</th>
            </tr>
        </thead>
        <tbody>
            <?php
                $coins = get_posts(['post_type' => 'monety', 'numberposts' => -1]);
                foreach ($coins as $coin) {
                    $ticker = get_field('code_coins', $coin->ID);
                    $price = get_field('curs_moneti', $coin->ID);
                    echo "<tr>";
                    echo "<td><strong>" . esc_html($coin->post_title) . "</strong></td>";
                    echo "<td>$ticker</td>";
                    echo "<td>$$price</td>";
                    echo "</tr>";
                }
                ?>
        </tbody>
    </table>
</div>
<?php
}

// =========================================================================
// 10. AUTO-UPDATER ДЛЯ ПОЛЕЙ КУРСА (WELL)
// =========================================================================
add_action( 'acf/save_post', 'unified_rates_updater', 20 );
function unified_rates_updater( $post_id ) {
    if ( get_post_type( $post_id ) !== 'rate' ) return;
    remove_action( 'acf/save_post', 'unified_rates_updater', 20 );

    $coin_from = get_field( 'coin_from', $post_id );
    if ( $coin_from ) {
        $val = get_field( 'curs_moneti', $coin_from->ID );
        if($val) update_field( 'well', $val, $post_id );
    }
    $coin_to = get_field('i-cart', $post_id) == 1 ? get_field('coin_do_cart', $post_id) : get_field('coin_do', $post_id);
    if ( $coin_to && $coin_from ) {
        $rate = get_true_exchange_rate($coin_from->ID, $coin_to->ID);
        if($rate) update_field( 'well_2', $rate, $post_id );
    }
    add_action( 'acf/save_post', 'unified_rates_updater', 20 );
}
?>