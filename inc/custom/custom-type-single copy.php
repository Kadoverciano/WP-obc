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
        'public'                => false,
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
// === БЛОК ЛОГИКИ ОБМЕНА (AJAX, РАСЧЕТЫ) ==================================
// =========================================================================

// Эта функция больше не используется для расчетов, но оставлена для совместимости.
function get_best_rate($from_coin_id, $to_coin_id) {
    $args = [
        'post_type'      => 'rate', 'posts_per_page' => 1,
        'meta_query'     => [
            'relation' => 'AND',
            ['key' => 'coin_from', 'value' => $from_coin_id, 'compare' => '='],
            ['relation' => 'OR', ['key' => 'coin_do', 'value' => $to_coin_id, 'compare' => '='], ['key' => 'coin_do_cart', 'value' => $to_coin_id, 'compare' => '=']],
            ['key' => 'status', 'value' => '1', 'compare' => '=']
        ],
        'orderby'  => 'meta_value_num', 'meta_key' => 'well', 'order'    => 'DESC',
    ];
    $rates = get_posts($args);
    if (empty($rates)) { return null; }
    $best_rate_post = $rates[0];
    return ['kurs' => get_field('well', $best_rate_post->ID), 'birzha' => get_field('stock_exchange', $best_rate_post->ID), 'rezerv' => get_field('reserve', $best_rate_post->ID)];
}

/**
 * AJAX-обработчик для получения списка доступных монет.
 * Рассчитывает и показывает реальный курс для каждой пары.
 */
add_action('wp_ajax_get_to_coins', 'get_to_coins');
add_action('wp_ajax_nopriv_get_to_coins', 'get_to_coins');
function get_to_coins() {
    if (!isset($_POST['from_coin'])) { wp_die('Ошибка'); }
    $from_coin_id = intval($_POST['from_coin']);

    $from_value_usdt = get_field('curs_moneti', $from_coin_id);
    $usdt_to_rub_rate = get_field('stoimost_1_usdt_v_rublyah', 'option');
    $from_is_card = get_post_type($from_coin_id) === 'payment_cards';

    if (!$from_is_card && (!is_numeric($from_value_usdt) || $from_value_usdt <= 0)) {
        echo '<div>Не удалось рассчитать курсы (нет курса у исходной валюты).</div>'; wp_die();
    }
    
    $html = '';
    $displayed_ids = [];
    $args = [
        'post_type'      => 'rate', 'posts_per_page' => -1,
        'meta_query'     => [['key' => 'coin_from', 'value' => $from_coin_id, 'compare' => '='], ['key' => 'status', 'value' => '1', 'compare' => '=']],
    ];

    $rates = get_posts($args);
    if ($rates) {
        foreach ($rates as $rate) {
            $item_to_display = get_field('i-cart', $rate->ID) == 1 ? get_field('coin_do_cart', $rate->ID) : get_field('coin_do', $rate->ID);

            if ($item_to_display && is_object($item_to_display) && !in_array($item_to_display->ID, $displayed_ids)) {
                
                $to_value_usdt = get_field('curs_moneti', $item_to_display->ID);
                $to_is_card = get_post_type($item_to_display->ID) === 'payment_cards';
                $calculated_rate = 0;

                if (!$from_is_card && $to_is_card) { // Крипто -> Рубли
                    if (is_numeric($usdt_to_rub_rate) && $usdt_to_rub_rate > 0) {
                        $calculated_rate = $from_value_usdt * $usdt_to_rub_rate;
                    }
                } elseif ($from_is_card && !$to_is_card) { // Рубли -> Крипто
                    if (is_numeric($usdt_to_rub_rate) && $usdt_to_rub_rate > 0 && is_numeric($to_value_usdt) && $to_value_usdt > 0) {
                         $calculated_rate = (1 / $usdt_to_rub_rate) / $to_value_usdt;
                    }
                } else { // Крипто -> Крипто
                    if (is_numeric($to_value_usdt) && $to_value_usdt > 0) {
                        $calculated_rate = $from_value_usdt / $to_value_usdt;
                    }
                }
                
                $reserve = get_field('reserve', $rate->ID);
                $html .= '<div class="coin-item-right" data-id="' . esc_attr($item_to_display->ID) . '">';
                $html .= '<span class="coin-name">' . esc_html($item_to_display->post_title) . '</span>';
                $html .= '<div class="coin-details">';
                $html .= '<span class="coin-rate">Курс: 1 -> ' . esc_html(round($calculated_rate, 6)) . '</span>';
                $html .= '<span class="coin-reserve">Резерв: ' . esc_html($reserve) . '</span>';
                $html .= '</div></div>';
                $displayed_ids[] = $item_to_display->ID;
            }
        }
    }
    echo $html ?: '<div>Нет доступных направлений для обмена</div>';
    wp_die();
}

/**
 * AJAX-обработчик для расчета суммы (ФИНАЛЬНАЯ ИСПРАВЛЕННАЯ ВЕРСИЯ)
 */
add_action('wp_ajax_calculate_exchange', 'calculate_exchange');
add_action('wp_ajax_nopriv_calculate_exchange', 'calculate_exchange');
function calculate_exchange(){
    $from_id = intval($_POST['from_coin']);
    $to_id   = intval($_POST['to_coin']);
    $amount  = floatval($_POST['amount']);

    if ($amount <= 0) { echo '0'; wp_die(); }

    $usdt_to_rub_rate   = get_field('stoimost_1_usdt_v_rublyah', 'option');
    $commission_percent = get_field('proczent_komissii', 'option');
    $commission_percent = is_numeric($commission_percent) ? floatval($commission_percent) : 0;
    
    $from_is_card = get_post_type($from_id) === 'payment_cards';
    $to_is_card   = get_post_type($to_id) === 'payment_cards';
    $receive_amount = 0;

    if (!$from_is_card && !$to_is_card) { // Крипто -> Крипто
        $from_value_usdt = get_field('curs_moneti', $from_id);
        $to_value_usdt   = get_field('curs_moneti', $to_id);
        if (!is_numeric($from_value_usdt) || !is_numeric($to_value_usdt) || $from_value_usdt <= 0 || $to_value_usdt <= 0) { echo 'Ошибка курса'; wp_die(); }
        $receive_amount = ($amount * $from_value_usdt) / $to_value_usdt;

    } elseif (!$from_is_card && $to_is_card) { // Крипто -> Рубли (Карта)
        $from_value_usdt = get_field('curs_moneti', $from_id);
        if (!is_numeric($from_value_usdt) || $from_value_usdt <= 0) { echo 'Ошибка курса'; wp_die(); }
        if (!is_numeric($usdt_to_rub_rate) || $usdt_to_rub_rate <= 0) { echo 'Ошибка курса RUB'; wp_die(); }
        $receive_amount = ($amount * $from_value_usdt) * $usdt_to_rub_rate;

    } elseif ($from_is_card && !$to_is_card) { // Рубли (Карта) -> Крипто
        $to_value_usdt = get_field('curs_moneti', $to_id);
        if (!is_numeric($to_value_usdt) || $to_value_usdt <= 0) { echo 'Ошибка курса'; wp_die(); }
        if (!is_numeric($usdt_to_rub_rate) || $usdt_to_rub_rate <= 0) { echo 'Ошибка курса RUB'; wp_die(); }
        $receive_amount = ($amount / $usdt_to_rub_rate) / $to_value_usdt;
    }

    $final_amount = $receive_amount * (1 - $commission_percent / 100);
    echo round($final_amount, 8);
    wp_die();
}

/**
 * AJAX-обработчик для создания сделки (ФИНАЛЬНАЯ ИСПРАВЛЕННАЯ ВЕРСИЯ)
 */
add_action('wp_ajax_create_trade', 'create_trade');
add_action('wp_ajax_nopriv_create_trade', 'create_trade');
function create_trade(){
    // ... Код создания сделки идентичен calculate_exchange, поэтому он остается без изменений от предыдущей версии ...
    $user_id = get_current_user_id();
    if(!$user_id){ echo 'Нужно войти на сайт.'; wp_die(); }
    $from_id = intval($_POST['from_coin']);
    $to_id   = intval($_POST['to_coin']);
    $amount  = floatval($_POST['amount']);
    $usdt_to_rub_rate   = get_field('stoimost_1_usdt_v_rublyah', 'option');
    $commission_percent = get_field('proczent_komissii', 'option');
    $commission_percent = is_numeric($commission_percent) ? floatval($commission_percent) : 0;
    $from_is_card = get_post_type($from_id) === 'payment_cards';
    $to_is_card   = get_post_type($to_id) === 'payment_cards';
    $receive_amount = 0;
    if (!$from_is_card && !$to_is_card) {
        $from_value_usdt = get_field('curs_moneti', $from_id);
        $to_value_usdt   = get_field('curs_moneti', $to_id);
        if (!is_numeric($from_value_usdt) || !is_numeric($to_value_usdt) || $from_value_usdt <= 0 || $to_value_usdt <= 0) { echo 'Ошибка курса'; wp_die(); }
        $receive_amount = ($amount * $from_value_usdt) / $to_value_usdt;
    } elseif (!$from_is_card && $to_is_card) {
        $from_value_usdt = get_field('curs_moneti', $from_id);
        if (!is_numeric($from_value_usdt) || $from_value_usdt <= 0) { echo 'Ошибка курса'; wp_die(); }
        if (!is_numeric($usdt_to_rub_rate) || $usdt_to_rub_rate <= 0) { echo 'Ошибка курса RUB'; wp_die(); }
        $receive_amount = ($amount * $from_value_usdt) * $usdt_to_rub_rate;
    } elseif ($from_is_card && !$to_is_card) {
        $to_value_usdt = get_field('curs_moneti', $to_id);
        if (!is_numeric($to_value_usdt) || $to_value_usdt <= 0) { echo 'Ошибка курса'; wp_die(); }
        if (!is_numeric($usdt_to_rub_rate) || $usdt_to_rub_rate <= 0) { echo 'Ошибка курса RUB'; wp_die(); }
        $receive_amount = ($amount / $usdt_to_rub_rate) / $to_value_usdt;
    }
    $final_amount = $receive_amount * (1 - $commission_percent / 100);
    $trade_id = wp_insert_post(['post_type' => 'trade', 'post_title' => 'Сделка от ' . current_time('d.m.Y H:i') . ' | Пользователь ' . $user_id, 'post_status' => 'publish']);
    update_field('user', $user_id, $trade_id);
    update_field('coin-from-deal', $from_id, $trade_id);
    if ($to_is_card) {
        update_field('coin-do-deal_cart', $to_id, $trade_id);
    } else {
        update_field('coin-do-deal', $to_id, $trade_id);
    }
    update_field('sending_amount', $amount, $trade_id);
    update_field('amount_received', $final_amount, $trade_id);
    update_field('status-deal', 'В обработке', $trade_id);
    update_field('date-create', current_time('d/m/Y g:i a'), $trade_id);
    echo 'Сделка создана! Сумма получения: ' . round($final_amount, 8);
    wp_die();
}

// ... Шорткод остается без изменений ...
// ...
add_shortcode('crypto_exchange_form', 'crypto_exchange_form_shortcode');
function crypto_exchange_form_shortcode() {
    $active_rates_query = new WP_Query(['post_type' => 'rate', 'posts_per_page' => -1, 'post_status' => 'publish', 'meta_query' => [['key' => 'status', 'value' => '1', 'compare' => '=']], 'fields' => 'ids']);
    $active_rate_ids = $active_rates_query->posts;
    $active_coin_from_ids = [];
    if (!empty($active_rate_ids)) {
        foreach ($active_rate_ids as $rate_id) {
            $from_coin_post_object = get_field('coin_from', $rate_id);
            if ($from_coin_post_object && is_object($from_coin_post_object)) {
                $active_coin_from_ids[] = $from_coin_post_object->ID;
            }
        }
    }
    $unique_coin_ids = array_unique($active_coin_from_ids);
    $coins = [];
    if (!empty($unique_coin_ids)) {
        $coins = get_posts(['post_type' => ['monety', 'payment_cards'], 'numberposts' => -1, 'post__in' => $unique_coin_ids, 'orderby' => 'post_title', 'order' => 'ASC']);
    }
    ob_start(); 
    ?>
    <div class="crypto-exchange-wrapper">
        <div class="coins-left"><h4>Отправляете:</h4>
            <?php if (!empty($coins)) : foreach($coins as $coin): ?>
                <div class="coin-item-left" data-id="<?php echo $coin->ID; ?>"><?php echo esc_html($coin->post_title); ?></div>
            <?php endforeach; else: ?><p>Нет доступных монет для обмена.</p><?php endif; ?>
        </div>
        <div class="coins-right"><h4>Получаете:</h4><div id="coins-to-list"><div>Выберите монету слева</div></div></div>
    </div>
    <div class="coins-form">
        <label>Сумма отправки:</label>
        <input type="number" id="amount_send" step="0.0001" min="0">
        <p>Сумма получения: <span id="amount_receive">0</span></p>
        <div id="exchange-message"></div>
        <button id="create-trade">Обменять</button>
    </div>
    <script>
    jQuery(document).ready(function($){
        function loadToCoins() {
            let selectedFrom = $('.coin-item-left.active').data('id');
            if (!selectedFrom) { return; }
            $('#coins-to-list').html('<div>Загрузка...</div>');
            $.ajax({
                url: '<?php echo admin_url("admin-ajax.php"); ?>', type: 'POST', data: { action: 'get_to_coins', from_coin: selectedFrom },
                success: function(response){
                    $('#coins-to-list').html(response);
                    $('#coins-to-list .coin-item-right').each(function(i) { setTimeout(() => { $(this).addClass('show'); }, 100 * i); });
                    $('#coins-to-list .coin-item-right').first().addClass('active');
                    updateReceive();
                }
            });
        }
        function updateReceive() {
            let selectedFrom = $('.coin-item-left.active').data('id');
            let to = $('#coins-to-list .coin-item-right.active').data('id');
            let amount = $('#amount_send').val();
            if(!to || !amount || amount <= 0) { $('#amount_receive').text(0); return; }
            $('#amount_receive').text('считаем...');
            $.ajax({
                url: '<?php echo admin_url("admin-ajax.php"); ?>', type: 'POST', data: { action: 'calculate_exchange', from_coin: selectedFrom, to_coin: to, amount: amount },
                success: function(response){ $('#amount_receive').text(response); }
            });
        }
        $(document).on('click', '.coin-item-left', function(){ $('.coin-item-left').removeClass('active'); $(this).addClass('active'); loadToCoins(); });
        $(document).on('click', '.coin-item-right', function(){ $('.coin-item-right').removeClass('active'); $(this).addClass('active'); updateReceive(); });
        $('#amount_send').on('keyup change', updateReceive);
        $('#create-trade').on('click', function(){
            let selectedFrom = $('.coin-item-left.active').data('id');
            let to = $('#coins-to-list .coin-item-right.active').data('id');
            let amount = $('#amount_send').val();
            if(!to || !amount || amount <= 0) { alert('Выберите валюты и введите корректную сумму для обмена.'); return; }
            $(this).prop('disabled', true).text('Обработка...');
            $.ajax({
                url: '<?php echo admin_url("admin-ajax.php"); ?>', type: 'POST', data: { action: 'create_trade', from_coin: selectedFrom, to_coin: to, amount: amount },
                success: function(response){
                    $('#exchange-message').html(response);
                    setTimeout(function() { $('#exchange-message').html(''); }, 5000);
                    $('#amount_receive').text('0'); $('#amount_send').val('');
                },
                complete: function() { $('#create-trade').prop('disabled', false).text('Обменять'); }
            });
        });
        if ($('.coin-item-left').length) { $('.coin-item-left').first().addClass('active'); loadToCoins(); }
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

    // Сценарий 1: Обновляем курсы, когда сохраняется сама монета/карта
    if ( $post_type === 'monety' || $post_type === 'payment_cards' ) {
        $new_rate_value = get_field( 'curs_moneti', $post_id );
        if ( ! isset( $new_rate_value ) || $new_rate_value === '' ) return;
        
        // Находим все пары, где этот актив используется как "Отдаете" и обновляем 'well'
        $related_rates_from = get_posts(['post_type' => 'rate', 'posts_per_page' => -1, 'meta_query' => [['key' => 'coin_from', 'value' => $post_id]], 'fields' => 'ids']);
        if ( ! empty( $related_rates_from ) ) {
            foreach ( $related_rates_from as $rate_id ) { update_field( 'well', $new_rate_value, $rate_id ); }
        }

        // Находим все пары, где этот актив используется как "Получаете" и обновляем 'well_2'
        // ВАЖНО: Это просто копирует значение, реальный курс рассчитывается динамически
        $related_rates_to_coin = get_posts(['post_type' => 'rate', 'posts_per_page' => -1, 'meta_query' => [['key' => 'coin_do', 'value' => $post_id]], 'fields' => 'ids']);
        if ( ! empty( $related_rates_to_coin ) ) {
            foreach ( $related_rates_to_coin as $rate_id ) { update_field( 'well_2', $new_rate_value, $rate_id ); }
        }
        $related_rates_to_cart = get_posts(['post_type' => 'rate', 'posts_per_page' => -1, 'meta_query' => [['key' => 'coin_do_cart', 'value' => $post_id]], 'fields' => 'ids']);
         if ( ! empty( $related_rates_to_cart ) ) {
            foreach ( $related_rates_to_cart as $rate_id ) { update_field( 'well_2', $new_rate_value, $rate_id ); }
        }
    }

    // Сценарий 2: Обновляем курсы, когда сохраняется сама пара "Курс"
    if ( $post_type === 'rate' ) {
        remove_action( 'acf/save_post', 'unified_rates_updater', 20 );

        $coin_from = get_field( 'coin_from', $post_id );
        if ( $coin_from && is_object($coin_from) ) {
            $rate_from = get_field( 'curs_moneti', $coin_from->ID );
            if ( isset( $rate_from ) && $rate_from !== '' ) { update_field( 'well', $rate_from, $post_id ); }
        }
        
        $coin_to = get_field( 'coin_do', $post_id ) ?: get_field( 'coin_do_cart', $post_id );
        
        if ( $coin_to && is_object($coin_to) ) {
            $rate_to_value = 0;
            $to_is_card = get_post_type($coin_to->ID) === 'payment_cards';

            if ($to_is_card && $coin_from) {
                // --- НОВЫЙ БЛОК: Рассчитываем и сохраняем реальный курс для карт ---
                $from_value_usdt = get_field('curs_moneti', $coin_from->ID);
                $usdt_to_rub_rate = get_field('stoimost_1_usdt_v_rublyah', 'option');
                if (is_numeric($from_value_usdt) && is_numeric($usdt_to_rub_rate) && $usdt_to_rub_rate > 0) {
                    $rate_to_value = $from_value_usdt * $usdt_to_rub_rate;
                }
            } else {
                // Старая логика: просто копируем курс USDT для крипты
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
    $post_id = isset($_GET['post']) ? intval($_GET['post']) : (isset($_POST['post_id']) ? intval($_POST['post_id']) : null);
    if (!$post_id) { return $field; }
    $post_type = get_post_type($post_id);
    if ( ($post_type === 'monety' || $post_type === 'payment_cards') && $field['name'] === 'curs_moneti' ) {
        $field['instructions'] = '<strong>Это поле не редактируется.</strong> Оно будет обновляться автоматически через API.';
    }
    if ( $post_type === 'rate' && ($field['name'] === 'well' || $field['name'] === 'well_2') ) {
        $field['readonly'] = 1;
        $field['instructions'] = '<strong>Это поле не редактируется.</strong> Оно автоматически обновляется из связанной монеты/карты.';
    }
    return $field;
}

