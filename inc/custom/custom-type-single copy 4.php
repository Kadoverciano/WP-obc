<?php

// === Регистрируем кастомный тип "Монеты" ===
function register_monety_cpt() {
    $labels = array(
        'name'                  => 'Монеты',
        'singular_name'         => 'Монета',
        'menu_name'             => 'Монеты',
        'name_admin_bar'        => 'Монета',
        'add_new'               => 'Добавить монету',
        'add_new_item'          => 'Добавить новую монету',
        'new_item'              => 'Новая монета',
        'edit_item'             => 'Редактировать монету',
        'view_item'             => 'Просмотреть монету',
        'all_items'             => 'Все монеты',
        'search_items'          => 'Поиск монет',
        'not_found'             => 'Монеты не найдены',
        'not_found_in_trash'    => 'В корзине монет не найдено',
    );

    $args = array(
        'labels'                => $labels,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'show_in_rest'          => true,
        'supports'              => array( 'title', 'thumbnail', 'custom-fields' ),
        'has_archive'           => false,
        'rewrite'               => false,
        'publicly_queryable'    => true,
        'exclude_from_search'   => false, // важно для ACF Post Object
        'capability_type'       => 'post',
        'map_meta_cap'          => true,
        'menu_position'         => 5,
        'menu_icon'             => 'dashicons-coins',
    );

    register_post_type( 'monety', $args );
}
add_action( 'init', 'register_monety_cpt' );

// === Блокируем single страницы CPT "Монеты" ===
function monety_force_no_single_redirect() {
    if ( is_singular( 'monety' ) ) {
        wp_safe_redirect( home_url(), 302 );
        exit;
    }
}
add_action( 'template_redirect', 'monety_force_no_single_redirect' );



// === Регистрируем кастомный тип записи "Карты платежей" ===
function register_post_type_payment_cards() {
    register_post_type( 'payment_cards', array(
        'labels' => array(
            'name'                  => 'Карты платежей',
            'singular_name'         => 'Карта платежей',
            'add_new'               => 'Добавить карту',
            'add_new_item'          => 'Добавить новую карту',
            'edit_item'             => 'Редактировать карту',
            'new_item'              => 'Новая карта',
            'view_item'             => 'Просмотр карты',
            'search_items'          => 'Искать карты',
            'not_found'             => 'Карты не найдены',
            'not_found_in_trash'    => 'В корзине карт не найдено',
            'menu_name'             => 'Карты платежей',
        ),
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_icon'             => 'dashicons-credit-card',
        'supports'              => array( 'title',  'thumbnail', 'custom-fields' ),
        'has_archive'           => false,
        'publicly_queryable'    => false,
        'rewrite'               => false,
        'menu_position'         => 6,
        'taxonomies'            => array(),
    ) );
}
add_action( 'init', 'register_post_type_payment_cards' );


// === Блокируем single страницы CPT "Карты платежей" ===
function payment_cards_force_no_single_redirect() {
    if ( is_singular( 'payment_cards' ) ) {
        wp_safe_redirect( home_url(), 302 );
        exit;
    }
}
add_action( 'template_redirect', 'payment_cards_force_no_single_redirect' );

// Регистрируем кастомный тип записи "Биржи"
function register_exchanges_cpt() {
    $labels = array(
        'name'                  => 'Биржи',
        'singular_name'         => 'Биржа',
        'menu_name'             => 'Биржи',
        'name_admin_bar'        => 'Биржа',
        'add_new'               => 'Добавить биржу',
        'add_new_item'          => 'Добавить новую биржу',
        'edit_item'             => 'Редактировать биржу',
        'new_item'              => 'Новая биржа',
        'view_item'             => 'Посмотреть биржу',
        'all_items'             => 'Все биржи',
        'search_items'          => 'Искать биржу',
        'not_found'             => 'Биржи не найдены',
        'not_found_in_trash'    => 'Биржи не найдены в корзине',
    );

    $args = array(
        'labels'                => $labels,
        'public'                => true,
        'has_archive'           => true,
        'show_in_rest'          => true,
        'menu_position'         => 7,
        'menu_icon'             => 'dashicons-chart-line',
        'supports'              => array('title', 'thumbnail', 'custom-fields'),
    );

    register_post_type('exchange', $args);
}
add_action('init', 'register_exchanges_cpt');


// Регистрируем кастомный тип записи "Курсы"
function register_rates_cpt() {
    $labels = array(
        'name'                  => 'Курсы',
        'singular_name'         => 'Курс',
        'menu_name'             => 'Курсы',
        'name_admin_bar'        => 'Курс',
        'add_new'               => 'Добавить курс',
        'add_new_item'          => 'Добавить новый курс',
        'edit_item'             => 'Редактировать курс',
        'new_item'              => 'Новый курс',
        'view_item'             => 'Посмотреть курс',
        'all_items'             => 'Все курсы',
        'search_items'          => 'Искать курс',
        'not_found'             => 'Курсы не найдены',
        'not_found_in_trash'    => 'Курсы не найдены в корзине',
    );

    $args = array(
        'labels'                => $labels,
        'public'                => false,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_icon'             => 'dashicons-money',
        'menu_position'         => 8,
        'supports'              => array('title', 'thumbnail', 'custom-fields'),
    );

    register_post_type('rate', $args);
}
add_action('init', 'register_rates_cpt');

// Регистрируем кастомный тип записи "Сделки"
function register_trades_cpt() {
    $labels = array(
        'name'                  => 'Сделки',
        'singular_name'         => 'Сделка',
        'menu_name'             => 'Сделки',
        'name_admin_bar'        => 'Сделка',
        'add_new'               => 'Добавить сделку',
        'add_new_item'          => 'Добавить новую сделку',
        'edit_item'             => 'Редактировать сделку',
        'new_item'              => 'Новая сделка',
        'view_item'             => 'Посмотреть сделку',
        'all_items'             => 'Все сделки',
        'search_items'          => 'Искать сделку',
        'not_found'             => 'Сделки не найдены',
        'not_found_in_trash'    => 'Сделки не найдены в корзине',
    );

    $args = array(
        'labels'                => $labels,
        'public'                => true, 
        'publicly_queryable'    => true, 
        'has_archive'           => false,
        'rewrite'               => array('slug' => 'order'),
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_icon'             => 'dashicons-admin-network',
        'menu_position'         => 9,
        'supports'              => array('title', 'custom-fields'),
    );

    register_post_type('trade', $args);
}
add_action('init', 'register_trades_cpt');



// Сделать ACF-поля read-only для сделок
add_filter('acf/load_field', function($field){
    // Список полей, которые должны быть только для чтения
    $readonly_fields = [
        'user',
        'coin-from-deal',
        'coin-do-deal',
        'coin-do-deal_cart',
        'sending_amount',
        'amount_received',
        'exchange-deal',
        'status-deal',
        'date-create',
        'date-finish',
    ];

    if(in_array($field['name'], $readonly_fields)){
        $field['readonly'] = 1; // делаем поле read-only
        $field['disabled'] = 1; // дополнительно отключаем редактирование
    }

    return $field;
});



// === Регистрируем CPT "Сети" ===
function register_network_cpt() {
    register_post_type('network', array(
        'labels' => array('name' => 'Сети', 'singular_name' => 'Сеть'),
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_icon' => 'dashicons-networking',
        'supports' => array('title', 'custom-fields'),
        'menu_position' => 10,
    ));
}
add_action('init', 'register_network_cpt');

// === Регистрируем CPT "Наши Кошельки" ===
function register_wallet_cpt() {
    register_post_type('wallet', array(
        'labels' => array('name' => 'Наши Кошельки', 'singular_name' => 'Кошелек'),
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_icon' => 'dashicons-wallet',
        'supports' => array('title', 'custom-fields'),
        'menu_position' => 11,
    ));
}
add_action('init', 'register_wallet_cpt');


// -----------------------------------------------------------------------------
// -----------------------------------------------------------------------------
// -----------------------------------------------------------------------------
// -----------------------------------------------------------------------------
// -----------------------------------------------------------------------------
// -----------------------------------------------------------------------------
// -----------------------------------------------------------------------------
// -----------------------------------------------------------------------------
// -----------------------------------------------------------------------------
// -----------------------------------------------------------------------------
// -----------------------------------------------------------------------------
// -----------------------------------------------------------------------------





// =========================================================================
// === БЛОК ЛОГИКИ ОБМЕНА (AJAX, РАСЧЕТЫ) - ФИНАЛЬНАЯ ВЕРСИЯ ===
// =========================================================================

/**
 * ГЛАВНАЯ ФУНКЦИЯ РАСЧЕТА (ФИНАЛЬНАЯ v3)
 * Исправлена логика проверки для карт.
 */
function get_true_exchange_rate( $from_id, $to_id ) {
    
    $usdt_to_rub_rate = floatval(get_field( 'stoimost_1_usdt_v_rublyah', 'option' ));
    if ( $usdt_to_rub_rate <= 0 ) {
        return 0; // Ошибка: не настроен курс RUB
    }

    $from_is_card = get_post_type( $from_id ) === 'payment_cards';
    $to_is_card   = get_post_type( $to_id ) === 'payment_cards';

    // 1. Проверяем монету "ОТ"
    $from_value_usdt = floatval(get_field( 'curs_moneti', $from_id ));
    if ( $from_value_usdt <= 0 ) {
        return 0; // Ошибка: у монеты "От" не указан курс
    }

    $base_rate = 0;

    if ( !$from_is_card && !$to_is_card ) { // Крипто -> Крипто
        // 2. Проверяем монету "КУДА" (только если это крипта)
        $to_value_usdt = floatval(get_field( 'curs_moneti', $to_id ));
        if ( $to_value_usdt <= 0 ) {
            return 0; // Ошибка: у монеты "Куда" (крипто) не указан курс
        }
        $base_rate = $from_value_usdt / $to_value_usdt;

    } elseif ( !$from_is_card && $to_is_card ) { // Крипто -> Рубли
        // 2. Проверяем карту "КУДА" (должно быть 1)
        $to_value_usdt = floatval(get_field( 'curs_moneti', $to_id ));
        if ( $to_value_usdt <= 0 ) {
             // ЭТО ТВОЯ ОШИБКА: 'curs_moneti' для "Сбербанк" = 0 или поле называется 'kurs'
            return 0;
        }
        $base_rate = $from_value_usdt * $usdt_to_rub_rate;
    } 
    
    return $base_rate;
}

/**
 * AJAX-обработчик для СОЗДАНИЯ ЗАЯВКИ (Сделки)
 * Срабатывает со страницы page-exchange-step.php
 */
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
    $receiver_address = sanitize_text_field($_POST['receiver_address']); // Кошелек клиента

    if (empty($from_id) || empty($to_id) || empty($amount_send) || empty($contact_info) || empty($receiver_address)) {
        wp_send_json_error(['message' => 'Все поля обязательны для заполнения.']); return;
    }

    // 3. Пересчитываем курс НА СЕРВЕРЕ
    $base_rate = get_true_exchange_rate($from_id, $to_id);
    if ($base_rate <= 0) { wp_send_json_error(['message' => 'Не удалось рассчитать курс.']); return; }

    $percent_markup = get_field('proczent_komissii', 'option');
    $markup_multiplier = 1 - (floatval($percent_markup) / 100);
    $rate_with_markup = $base_rate * $markup_multiplier;

    $amount_receive_gross = $amount_send * $rate_with_markup;
    $final_amount_receive = $amount_receive_gross;

    $to_is_card = (get_post_type($to_id) === 'payment_cards');
    if (!$to_is_card) { // Мы отправляем КРИПТУ, вычитаем НАШУ комиссию
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
        wp_send_json_error(['message' => 'Сумма слишком мала для покрытия комиссии сети.']); return;
    }

    // 4. Находим НАШ кошелек/карту для приема
    $our_receiver_details = '';
    $from_is_card = (get_post_type($from_id) === 'payment_cards'); // Всегда будет false

    if ($from_is_card) {
        $our_receiver_details = get_field('our_card_details', $from_id);
    } else { // Клиент шлет нам КРИПТУ
        $network = get_field('native_network', $from_id); // Сеть монеты "От"
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
        wp_send_json_error(['message' => 'Ошибка: Не настроены реквизиты для приема.']); return;
    }

    // 5. Создаем пост "Сделка" (trade)
    $trade_title = 'Заявка: ' . get_the_title($from_id) . ' на ' . get_the_title($to_id);
    $trade_id = wp_insert_post(['post_type' => 'trade', 'post_title' => $trade_title, 'post_status' => 'publish']);

    if (is_wp_error($trade_id)) {
        wp_send_json_error(['message' => 'Не удалось создать заявку в базе.']); return;
    }

    // 6. Заполняем ACF-поля для сделки
    update_field('user', get_current_user_id(), $trade_id);
    update_field('coin-from-deal', $from_id, $trade_id);

    if ($to_is_card) { update_field('coin-do-deal_cart', $to_id, $trade_id); }
    else { update_field('coin-do-deal', $to_id, $trade_id); }

    update_field('sending_amount', $amount_send, $trade_id);
    update_field('amount_received', $final_amount_receive, $trade_id);

    update_field('contact_info', $contact_info, $trade_id);
    update_field('receiver_address', $receiver_address, $trade_id);
    update_field('assigned_wallet_address', $our_receiver_details, $trade_id); // НАШ кошелек

    update_field('status-deal', 'Ожидание оплаты', $trade_id);
    update_field('date-create', current_time('d/m/Y g:i a'), $trade_id);

    $timer_end_timestamp = time() + (30 * 60); // 30 минут
    update_field('order_timer_end', $timer_end_timestamp, $trade_id);

    // 7. Отправляем УСПЕХ
    wp_send_json_success(['order_url' => get_permalink($trade_id)]);
}

/**
 * Шорткод [crypto_exchange_form] (ФИНАЛЬНАЯ v3)
 * * Добавлен расчет НАЦЕНКИ (proczent_komissii)
 * * Исправлен баг с 'i-cart'
 */
add_shortcode('crypto_exchange_form', 'crypto_exchange_form_shortcode');
function crypto_exchange_form_shortcode() {
    
    $active_rates = get_posts([
        'post_type'      => 'rate',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'meta_query'     => [['key' => 'status', 'value' => '1', 'compare' => '=']],
    ]);

    if (empty($active_rates)) {
        return '<p>Нет доступных направлений для обмена.</p>';
    }

    // --- НАЧАЛО ИСПРАВЛЕНИЯ (Проблема Б) ---
    // Получаем наценку ОДИН РАЗ
    $percent_markup = floatval(get_field('proczent_komissii', 'option'));
    $markup_multiplier = 1 - ($percent_markup / 100); // (1 - 10 / 100) = 0.9
    // --- КОНЕЦ ИСПРАВЛЕНИЯ ---

    $coins_from_list = [];
    $rates_by_from_id = [];

    foreach ($active_rates as $rate_post) {
        $from_coin = get_field('coin_from', $rate_post->ID); 
        if ($from_coin && is_object($from_coin)) {
            
            $coins_from_list[$from_coin->ID] = $from_coin;
            
            // --- ИСПРАВЛЕНИЕ (Проблема А) ---
            $to_coin = null;
            if (get_field('i-cart', $rate_post->ID) == 1) {
                $to_coin = get_field('coin_do_cart', $rate_post->ID);
            } else {
                $to_coin = get_field('coin_do', $rate_post->ID);
            }
            // --- КОНЕЦ ИСПРАВЛЕНИЯ ---

            if ($to_coin && is_object($to_coin)) {
                
                // --- НАЧАЛО ИСПРАВЛЕНИЯ (Проблема Б) ---
                $base_rate = get_true_exchange_rate($from_coin->ID, $to_coin->ID);
                $rate_with_markup = $base_rate * $markup_multiplier;
                // --- КОНЕЦ ИСПРАВЛЕНИЯ ---

                $rates_by_from_id[$from_coin->ID][$to_coin->ID] = [
                    'id'    => $to_coin->ID,
                    'title' => $to_coin->post_title,
                    'rate'  => $rate_with_markup, // Показываем курс с наценкой
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

    // JS ОСТАЕТСЯ БЕЗ ИЗМЕНЕНИЙ, ОН УЖЕ ПРАВИЛЬНЫЙ
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
                            '?from=' + fromId +
                            '&to=' + coin.id;

                        toListHtml += '<a href="' + pairLink + '" class="coin-item-right">';
                        toListHtml += '<span class="coin-name">' + coin.title + '</span>';
                        toListHtml += '<div class="coin-details">';
                        // --- ИСПРАВЛЕНИЕ (Проблема Б) ---
                        // coin.rate теперь уже содержит наценку
                        toListHtml += '<span class="coin-rate">1 -> ' + parseFloat(coin.rate)
                            .toFixed(6) + '</span>';
                        // --- КОНЕЦ ИСПРАВЛЕНИЯ ---
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
    if (firstLeftItem) {
        firstLeftItem.click();
    }
});
</script>
<?php
    return ob_get_clean();
}

// =========================================================================
// === АВТОМАТИЧЕСКОЕ ОБНОВЛЕНИЕ КУРСОВ (УМНАЯ ВЕРСИЯ) =====================
// =========================================================================

add_action( 'acf/save_post', 'unified_rates_updater', 20 );
function unified_rates_updater( $post_id ) {
    $post_type = get_post_type( $post_id );
    if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) { return; }

    if ( $post_type === 'rate' ) {
        remove_action( 'acf/save_post', 'unified_rates_updater', 20 );

        $coin_from = get_field( 'coin_from', $post_id );
        if ( $coin_from && is_object($coin_from) ) {
            $rate_from = get_field( 'curs_moneti', $coin_from->ID );
            if ( isset( $rate_from ) && $rate_from !== '' ) { update_field( 'well', $rate_from, $post_id ); }
        }

        $coin_to = get_field( 'coin_do', $post_id ) ?: get_field( 'coin_do_cart', $post_id );

        if ( $coin_to && is_object($coin_to) && $coin_from && is_object($coin_from) ) {
            $rate_to_value = 0;
            $to_is_card = get_post_type($coin_to->ID) === 'payment_cards';
            $from_is_card = get_post_type($coin_from->ID) === 'payment_cards';

            if (!$from_is_card && $to_is_card) { // Крипто -> Рубли
                $from_value_usdt = get_field('curs_moneti', $coin_from->ID);
                $usdt_to_rub_rate = get_field('stoimost_1_usdt_v_rublyah', 'option');
                if (is_numeric($from_value_usdt) && is_numeric($usdt_to_rub_rate) && $usdt_to_rub_rate > 0) {
                    $rate_to_value = $from_value_usdt * $usdt_to_rub_rate;
                }
            } else { // Крипто -> Крипто
                $rate_to_value = get_field( 'curs_moneti', $coin_to->ID );
            }

            if ( isset( $rate_to_value ) && $rate_to_value !== '' ) { 
                update_field( 'well_2', $rate_to_value, $post_id ); 
            }
        }
        add_action( 'acf/save_post', 'unified_rates_updater', 20 );
    }
}


// =========================================================================
// === БЛОКИРОВКА ПОЛЕЙ КУРСА В АДМИН-ПАНЕЛИ ===============================
// =========================================================================
add_filter('acf/load_field', 'make_rate_fields_readonly');
function make_rate_fields_readonly( $field ) {
    if ( ! is_admin() || empty( $GLOBALS['pagenow'] ) || ! in_array( $GLOBALS['pagenow'], ['post.php', 'post-new.php'] ) ) { return $field; }

    $post_type = get_post_type(get_the_ID());

    if ( ($post_type === 'monety' || $post_type === 'payment_cards') && $field['name'] === 'curs_moneti' ) {
        $field['instructions'] = '<strong>Это поле не редактируется.</strong> Оно будет обновляться автоматически через API.';
        // Тут можно добавить $field['readonly'] = 1; когда API будет готов
    }
    if ( $post_type === 'rate' && ($field['name'] === 'well' || $field['name'] === 'well_2') ) {
        $field['readonly'] = 1;
        $field['instructions'] = '<strong>Это поле не редактируется.</strong> Оно автоматически обновляется из связанной монеты/карты.';
    }
    return $field;
}


// ----------------------------------------------

// Биржа quickex.io

/**
 * Драйвер для QuickEx API
 * Получает примерную сумму обмена (Estimated Amount)
 * * @param string $from_ticker  Тикер валюты отправки (напр. 'btc', 'eth')
 * @param string $to_ticker    Тикер валюты получения (напр. 'xmr', 'ltc')
 * @param float  $amount       Сумма отправки
 * @return float|bool          Сумма получения или false, если ошибка
 */
function api_driver_quickex_get_rate($from_ticker, $to_ticker, $amount) {
    
    // 1. Ищем пост "QuickEx" в CPT Биржи, чтобы взять API Key
    // Мы ищем по заголовку или можно хардкодом вписать ID, если он не меняется
    $exchange_post = get_page_by_title('QuickEx', OBJECT, 'exchange');
    
    if (!$exchange_post) {
        error_log('QuickEx: Биржа не найдена в админке');
        return false;
    }
    
    $api_key = get_field('api_key', $exchange_post->ID);
    $api_url = get_field('api_url', $exchange_post->ID); // Должно быть https://api.quickex.io/v1
    
    if (!$api_key) {
        error_log('QuickEx: Нет API ключа');
        return false;
    }

    // 2. Формируем запрос к QuickEx
    // Обычно формат: /exchange_amount/{from_currency}/{to_currency}/{amount}
    // Приводим тикеры к нижнему регистру (btc, xmr), т.к. API это любят
    $from = strtolower($from_ticker);
    $to   = strtolower($to_ticker);
    
    $request_url = "{$api_url}/exchange_amount/{$from}/{$to}/{$amount}";

    // 3. Отправляем запрос через cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $request_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        // Некоторые API требуют ключ в хедере, некоторые в URL. 
        // QuickEx часто не требует ключа для публичного метода оценки, 
        // но для создания транзакции потребует. Добавим на всякий случай.
        "x-api-key: {$api_key}" 
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // 4. Обрабатываем ответ
    if ($http_code !== 200 || !$response) {
        error_log("QuickEx Error: HTTP $http_code. Response: $response");
        return false;
    }

    $data = json_decode($response, true);

    // QuickEx обычно возвращает JSON вида:
    // { "amount": 0.1, "toAmount": 123.45, ... }
    // Нам нужно поле, где лежит итоговая сумма. Обычно это 'estimatedAmount' или 'value'.
    // *ВАЖНО: Структура ответа может меняться, нужно проверить документацию или ответ.*
    // Предположим стандартную структуру:
    
    if (isset($data['estimatedAmount'])) {
        return floatval($data['estimatedAmount']);
    } elseif (isset($data['value'])) {
        return floatval($data['value']); 
    } else {
        // Если структура неизвестна, запишем в лог, чтобы ты увидел
        error_log("QuickEx Response Structure Unknown: " . print_r($data, true));
        return false;
    }
}


//https://obc.fs-js.ru/?test_quickex=1
// === ДИАГНОСТИКА QUICKEX (V3 - С МАСКИРОВКОЙ) ===
add_action('init', function() {
    if (isset($_GET['test_quickex'])) {
        
        echo '<h2>🕵️ Диагностика QuickEx (V3: Маскировка под браузер)</h2>';
        
        $exchange_post = get_page_by_title('QuickEx', OBJECT, 'exchange');
        if (!$exchange_post) wp_die("Пост QuickEx не найден");
        
        $api_key = get_field('api_key', $exchange_post->ID);
        // Базовый URL берем жестко из документации v2, чтобы исключить ошибку ввода
        $base_url = 'https://quickex.io/api/v2'; 

        echo "<p>API Key: " . substr($api_key, 0, 5) . "... (проверь, нет ли пробелов)</p>";

        // Два варианта эндпоинта: с подчеркиванием и с дефисом
        $endpoints = [
            'exchange-amount' => "$base_url/exchange-amount/btc/xmr/1", // Новый стандарт
            'exchange_amount' => "$base_url/exchange_amount/btc/xmr/1", // Старый стандарт
        ];

        foreach ($endpoints as $type => $url) {
            echo "<hr><h3>Пробуем вариант: <code>$type</code></h3>";
            echo "URL: $url<br>";
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            
            // === ГЛАВНОЕ: ПРИТВОРЯЕМСЯ БРАУЗЕРОМ ===
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/110.0.0.0 Safari/537.36');
            
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                "x-api-key: {$api_key}"
            ]);
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            echo "HTTP Code: <b>$http_code</b><br>";
            
            if ($http_code == 200) {
                echo "<h2 style='color:green;'>🎉 УРА! РАБОТАЕТ!</h2>";
                echo "Ответ сервера: <pre>" . htmlspecialchars($response) . "</pre>";
                
                echo "<div style='background:#dff0d8; padding:15px; border:2px solid green; margin-top:10px;'>";
                echo "✅ <strong>Что делать:</strong><br>";
                echo "1. Зайди в админку -> Биржи -> QuickEx.<br>";
                echo "2. В поле <strong>API URL</strong> впиши ровно вот это (без лишних слешей):<br>";
                echo "<h3 style='margin:5px 0;'>$base_url</h3>";
                echo "3. Запомни, что правильный метод это: <strong>$type</strong>";
                echo "</div>";
                exit; // Прерываем, если нашли рабочий
            } elseif ($http_code == 403) {
                echo "<span style='color:red;'>⛔ Опять 403 Forbidden.</span><br>";
                echo "Возможные причины:<br>";
                echo "1. API ключ неверный (проверь пробелы в начале/конце).<br>";
                echo "2. Твой серверный IP забанен (напиши в поддержку QuickEx).<br>";
            } else {
                echo "Ответ: " . htmlspecialchars(substr($response, 0, 200));
            }
        }
        exit;
    }
});

// https://obc.fs-js.ru/?find_my_ip=1

add_action('init', function() {
    if (isset($_GET['find_my_ip'])) {
        // Делаем запрос к сервису, который видит наш реальный IP
        $ch = curl_init('https://ifconfig.me/ip'); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $real_ip = curl_exec($ch);
        curl_close($ch);
        
        echo "<div style='background: #ffdddd; border: 2px solid red; padding: 20px; font-size: 20px; text-align: center; margin-top: 50px;'>";
        echo "Твой IP в DNS: <strong>" . $_SERVER['SERVER_ADDR'] . "</strong> (ЭТОТ НЕ НУЖЕН)<br><br>";
        echo "Твой РЕАЛЬНЫЙ Исходящий IP: <h1 style='font-size: 40px; margin: 10px 0;'>" . $real_ip . "</h1>";
        echo "(Вот этот IP нужно дать QuickEx)";
        echo "</div>";
        exit;
    }
});


/**
 * Драйвер для ChangeNOW API (Интеграция с CPT Биржи)
 */
function api_driver_changenow_get_amount($from_ticker, $to_ticker, $amount_send) {
    
    // 1. Ищем пост биржи по заголовку. 
    // Убедись, что пост в админке называется "changenow" (или поменяй тут название)
    $exchange_post = get_page_by_title('changenow', OBJECT, 'exchange');
    
    if (!$exchange_post) {
        return 0; // Пост не найден
    }
    
    // 2. Берем данные из полей ACF этого поста
    $api_url = get_field('api_url', $exchange_post->ID);
    
    // Пробуем взять из "API ключ". Если пусто, пробуем "API секрет" (на всякий случай)
    $api_key = get_field('api_key', $exchange_post->ID);
    if (empty($api_key)) {
        $api_key = get_field('api_secret', $exchange_post->ID);
    }

    // Проверка: если поля пустые или биржа выключена - выходим
    $is_active = get_field('exchange_status', $exchange_post->ID);
    if (!$api_url || !$api_key || !$is_active) {
        return 0;
    }

    // Чистим URL от лишнего слеша в конце (если вдруг скопировал с ним)
    $api_url = rtrim($api_url, '/');
    
    // 3. Стандартная логика запроса
    $from = strtolower(trim($from_ticker));
    $to   = strtolower(trim($to_ticker));
    
    // Формируем URL динамически
    $request_url = "$api_url/exchange-amount/$amount_send/{$from}_{$to}?api_key=$api_key";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $request_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5); 
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code !== 200 || !$response) {
        return 0; 
    }

    $data = json_decode($response, true);
    
    if (isset($data['estimatedAmount'])) {
        return floatval($data['estimatedAmount']);
    }
    
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



/**
 * Драйвер для SimpleSwap API
 */
function api_driver_simpleswap_get_amount($from_ticker, $to_ticker, $amount_send) {
    
    $exchange_post = get_page_by_title('simpleswap', OBJECT, 'exchange');
    if (!$exchange_post) return 0;
    
    $api_url = get_field('api_url', $exchange_post->ID);
    $api_key = get_field('api_key', $exchange_post->ID);
    $is_active = get_field('exchange_status', $exchange_post->ID);

    if (!$api_url || !$api_key || !$is_active) return 0;

    $api_url = rtrim($api_url, '/');
    $from = strtolower(trim($from_ticker));
    $to   = strtolower(trim($to_ticker));
    
    // Формат запроса SimpleSwap: /get_estimated_amount
    $request_url = "$api_url/get_estimated_amount?api_key=$api_key&currency_from=$from&currency_to=$to&amount=$amount_send";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $request_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5); 
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code !== 200 || !$response) {
        return 0; 
    }

    // SimpleSwap иногда возвращает просто число, иногда JSON. Обработаем оба варианта.
    $data = json_decode($response, true);
    
    // Если вернулся JSON { "result": 123.45 } (иногда бывает так)
    if (is_array($data) && isset($data['result'])) {
        return floatval($data['result']);
    }
    // Если вернулось просто число в теле ответа (обычное поведение SimpleSwap)
    if (is_numeric($response)) {
        return floatval($response);
    }
    
    return 0;
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
                    
                    // 1. Получаем курсы с ОБЕИХ бирж
                    $price_cn = 0;
                    $price_ss = 0;
                    
                    if ($ticker) {
                        $price_cn = api_driver_changenow_get_amount($ticker, 'usdt', 1);
                        $price_ss = api_driver_simpleswap_get_amount($ticker, 'usdt', 1); // Наш новый драйвер
                    }
                    
                    // 2. Выбираем ЛУЧШУЮ цену (максимальную, т.к. мы продаем монету за USDT)
                    $best_price = max($price_cn, $price_ss);
                    
                    // Формируем HTML для ChangeNOW
                    $cn_html = $price_cn > 0 ? "$$price_cn" : "<span style='color:#ccc'>-</span>";
                    if ($price_cn > 0 && $price_cn >= $price_ss) $cn_html = "<strong>$cn_html</strong> ✅";
                    
                    // Формируем HTML для SimpleSwap
                    $ss_html = $price_ss > 0 ? "$$price_ss" : "<span style='color:#ccc'>-</span>";
                    if ($price_ss > 0 && $price_ss > $price_cn) $ss_html = "<strong>$ss_html</strong> ✅";

                    // Считаем разницу с твоей текущей ценой
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
                    echo "<td>" . ($best_price > 0 ? "<strong>$$best_price</strong>" : "-") . " <small>($diff_html)</small></td>";
                    echo "<td>";
                    
                    if ($best_price > 0) {
                        // Кнопка обновит цену на ЛУЧШУЮ из найденных
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
                    
                    // 1. Получаем курсы с ОБЕИХ бирж
                    $price_cn = 0;
                    $price_ss = 0;
                    
                    if ($ticker) {
                        $price_cn = api_driver_changenow_get_amount($ticker, 'usdt', 1);
                        $price_ss = api_driver_simpleswap_get_amount($ticker, 'usdt', 1); // Наш новый драйвер
                    }
                    
                    // 2. Выбираем ЛУЧШУЮ цену (максимальную, т.к. мы продаем монету за USDT)
                    $best_price = max($price_cn, $price_ss);
                    
                    // Формируем HTML для ChangeNOW
                    $cn_html = $price_cn > 0 ? "$$price_cn" : "<span style='color:#ccc'>-</span>";
                    if ($price_cn > 0 && $price_cn >= $price_ss) $cn_html = "<strong>$cn_html</strong> ✅";
                    
                    // Формируем HTML для SimpleSwap
                    $ss_html = $price_ss > 0 ? "$$price_ss" : "<span style='color:#ccc'>-</span>";
                    if ($price_ss > 0 && $price_ss > $price_cn) $ss_html = "<strong>$ss_html</strong> ✅";

                    // Считаем разницу с твоей текущей ценой
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
                    echo "<td>" . ($best_price > 0 ? "<strong>$$best_price</strong>" : "-") . " <small>($diff_html)</small></td>";
                    echo "<td>";
                    
                    if ($best_price > 0) {
                        // Кнопка обновит цену на ЛУЧШУЮ из найденных
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