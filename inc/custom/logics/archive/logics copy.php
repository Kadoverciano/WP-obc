<?php 

// =========================================================================
// 2. HELPER FUNCTIONS & ACF FILTERS
// =========================================================================

// Сделать поля Сделок read-only для защиты
add_filter('acf/load_field', function($field){
    $readonly_fields = ['user', 'coin-from-deal', 'coin-do-deal', 'coin-do-deal_cart', 'sending_amount', 'amount_received', 'exchange-deal', 'status-deal', 'date-create', 'date-finish'];
    if(in_array($field['name'], $readonly_fields)){
        $field['readonly'] = 1;
        $field['disabled'] = 1;
    }
    return $field;
});

// Блокировка полей курса в админке (чтобы не правили руками то, что обновляет API)
add_filter('acf/load_field', 'make_rate_fields_readonly');
function make_rate_fields_readonly( $field ) {
    if ( ! is_admin() || empty( $GLOBALS['pagenow'] ) || ! in_array( $GLOBALS['pagenow'], ['post.php', 'post-new.php'] ) ) { return $field; }
    $post_type = get_post_type(get_the_ID());
    
    if ( ($post_type === 'monety' || $post_type === 'payment_cards') && $field['name'] === 'curs_moneti' ) {
        $field['instructions'] = '<strong>Это поле обновляется автоматически через Монитор Курсов.</strong>';
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

get_template_part('/inc/custom/exchange/changenow');
get_template_part('/inc/custom/exchange/simpleswap');
get_template_part('/inc/custom/exchange/stealthex');



// =========================================================================
// 4. ГЛАВНАЯ ЛОГИКА РАСЧЕТА
// =========================================================================

/**
 * Расчет курса БЕЗ обращения к API (берет данные из полей ACF, обновленных Монитором)
 */
function get_true_exchange_rate( $from_id, $to_id ) {
    
    $usdt_to_rub_rate = floatval(get_field( 'stoimost_1_usdt_v_rublyah', 'option' ));
    if ( $usdt_to_rub_rate <= 0 ) return 0; 

    $from_is_card = get_post_type( $from_id ) === 'payment_cards';
    $to_is_card   = get_post_type( $to_id ) === 'payment_cards';

    // 1. Получаем курс монеты "ОТ" из ACF
    $from_value_usdt = sanitize_crypto_number(get_field( 'curs_moneti', $from_id ));
    if ( $from_value_usdt <= 0 ) return 0;

    $base_rate = 0;

    if ( !$from_is_card && !$to_is_card ) { // Крипто -> Крипто
        // 2. Получаем курс монеты "КУДА" из ACF
        $to_value_usdt = sanitize_crypto_number(get_field( 'curs_moneti', $to_id ));
        if ( $to_value_usdt <= 0 ) return 0;
        
        $base_rate = $from_value_usdt / $to_value_usdt;

    } elseif ( !$from_is_card && $to_is_card ) { // Крипто -> Рубли
        // 2. Для карты curs_moneti должно быть 1
        $to_value_usdt = sanitize_crypto_number(get_field( 'curs_moneti', $to_id ));
        if ( $to_value_usdt <= 0 ) return 0;
        
        $base_rate = $from_value_usdt * $usdt_to_rub_rate;
    } 
    
    return $base_rate;
}

// =========================================================================
// 5. AJAX ОБРАБОТЧИК СОЗДАНИЯ СДЕЛКИ
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
    
    $base_rate = get_true_exchange_rate($from_id, $to_id);
    if ($base_rate <= 0) { wp_send_json_error(['message' => 'Не удалось рассчитать курс.']); return; }
    
    // Расчет с учетом наценки (1 - %)
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

    // Ищем реквизиты для приема
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

    // Создаем сделку
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
// 6. ШОРТКОД ГЛАВНОЙ СТРАНИЦЫ
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

<script type="application/json" id="exchange_rates_data">
<?php echo json_encode($rates_by_from_id); ?>
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ratesData = JSON.parse(document.getElementById('exchange_rates_data').textContent);
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
// 7. АДМИНКА: СТРАНИЦА МОНИТОРИНГА
// =========================================================================

add_action('admin_menu', 'register_exchange_monitor_page');
function register_exchange_monitor_page() {
    add_submenu_page('edit.php?post_type=exchange', 'Мониторинг', 'Монитор Курсов', 'manage_options', 'exchange-monitor', 'render_exchange_monitor_page');
}

function render_exchange_monitor_page() {
    ?>
<div class="wrap">
    <h1>📊 Мониторинг Курсов (Цены в USDT)</h1>
    <p>Сравнение цен на биржах партнеров.</p>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>Монета (Тикер)</th>
                <th>Твоя цена (ACF)</th>
                <th>ChangeNOW</th>
                <th>SimpleSwap</th>
                <th>StealthEX</th>
                <th>Лучшая цена</th>
                <th>Действие</th>
            </tr>
        </thead>
        <tbody>
            <?php
                $coins = get_posts(['post_type' => 'monety', 'numberposts' => -1]);
                foreach ($coins as $coin) {
                    $ticker = get_field('code_coins', $coin->ID);
                    $current_price = floatval(get_field('curs_moneti', $coin->ID));
                    
                    $price_cn = 0;
                    $price_ss = 0;
                    $price_sx = 0;
                    
                    if ($ticker) {
                        $price_cn = api_driver_changenow_get_amount($ticker, 'usdt', 1);
                        $price_ss = api_driver_simpleswap_get_amount($ticker, 'usdt', 1);
                        $price_sx = api_driver_stealthex_get_amount($ticker, 'usdt', 1);
                    }
                    $best_price = max($price_cn, $price_ss, $price_sx);
                    
                    // HTML Helpers
                    $format_price = function($price, $best) {
                        if ($price <= 0) return "-";
                        $html = "$$price";
                        if ($price >= $best && $best > 0) $html = "<strong>$html</strong> ✅";
                        return $html;
                    };
                    
                    $cn_html = $format_price($price_cn, $best_price);
                    $ss_html = $format_price($price_ss, $best_price);
                    $sx_html = $format_price($price_sx, $best_price);

                    $diff_html = '-';
                    if ($best_price > 0 && $current_price > 0) {
                        $diff = round((($best_price - $current_price) / $current_price) * 100, 2);
                        $color = abs($diff) > 2 ? 'red' : 'green';
                        $diff_html = "<span style='color:$color'>$diff%</span>";
                    }
                    
                    echo "<tr>";
                    echo "<td><strong>" . esc_html($coin->post_title) . "</strong> ($ticker)</td>";
                    echo "<td>$" . $current_price . "</td>";
                    echo "<td>$cn_html</td>";
                    echo "<td>$ss_html</td>";
                    echo "<td>$sx_html</td>";
                    echo "<td>" . ($best_price > 0 ? "<strong>$$best_price</strong>" : "-") . " <small>($diff_html)</small></td>";
                    echo "<td>";
                    if ($best_price > 0) {
                        echo "<button class='button button-primary update-coin-btn' data-id='{$coin->ID}' data-price='{$best_price}'>Принять ($$best_price)</button>";
                    } else {
                        echo "Нет данных";
                    }
                    echo "<span class='spinner' style='float:none;'></span> <span class='update-msg'></span>";
                    echo "</td>";
                    echo "</tr>";
                }
                ?>
        </tbody>
    </table>
    <div style="margin-top: 20px;">
        <button id="update-all-btn" class="button button-large">🔄 Обновить ВСЕ (по лучшим курсам)</button>
    </div>
</div>
<script>
jQuery(document).ready(function($) {
    $('.update-coin-btn').on('click', function() {
        var btn = $(this);
        var row = btn.closest('tr');
        var postId = btn.data('id');
        var newPrice = btn.data('price');

        btn.prop('disabled', true);
        row.find('.spinner').addClass('is-active');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'update_coin_price_ajax',
                post_id: postId,
                price: newPrice
            },
            success: function(response) {
                row.find('.spinner').removeClass('is-active');
                if (response.success) {
                    row.find('.update-msg').css('color', 'green').text('ОК');
                    row.find('td:eq(1)').text('$' + newPrice);
                } else {
                    row.find('.update-msg').css('color', 'red').text('Err');
                }
            }
        });
    });
    $('#update-all-btn').on('click', function() {
        $('.update-coin-btn:enabled').each(function(i) {
            var btn = $(this);
            setTimeout(function() {
                btn.trigger('click');
            }, i * 500);
        });
    });
});
</script>
<?php
}

add_action('wp_ajax_update_coin_price_ajax', function() {
    $post_id = intval($_POST['post_id']);
    $price = floatval($_POST['price']);
    if ($post_id && $price > 0) {
        update_field('curs_moneti', $price, $post_id);
        wp_send_json_success();
    } else {
        wp_send_json_error();
    }
});

// =========================================================================
// 8. AUTO-UPDATER ДЛЯ ПОЛЕЙ КУРСА (WELL)
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