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



// /**
//  * ГЛАВНАЯ ФУНКЦИЯ РАСЧЕТА КУРСА (с учетом комиссии)
//  *
//  * Рассчитывает, сколько единиц валюты "TO" получит пользователь за 1 единицу валюты "FROM",
//  * уже включая в этот курс процент комиссии.
//  *
//  * @param int $from_id ID поста "Монеты" или "Карты", откуда отправляем.
//  * @param int $to_id ID поста "Монеты" или "Карты", куда получаем.
//  * @return float Итоговый курс (1 -> X) с учетом комиссии. 0 в случае ошибки.
//  */
// function get_true_exchange_rate( $from_id, $to_id ) {
    
//     // --- 1. Получаем глобальные настройки ---
//     $usdt_to_rub_rate = get_field( 'stoimost_1_usdt_v_rublyah', 'option' );
//     $commission_percent = get_field( 'proczent_komissii', 'option' );
    
//     // Преобразуем процент в множитель (например, 5% -> 0.95)
//     $commission_multiplier = 1 - ( floatval( $commission_percent ) / 100 );

//     // --- 2. Получаем данные по валютам ---
//     $from_is_card = get_post_type( $from_id ) === 'payment_cards';
//     $to_is_card   = get_post_type( $to_id ) === 'payment_cards';
    
//     $from_value_usdt = get_field( 'curs_moneti', $from_id );
//     $to_value_usdt   = get_field( 'curs_moneti', $to_id );

//     $base_rate = 0; // "Грязный" курс до комиссии

//     // --- 3. Рассчитываем "грязный" курс по 3 сценариям ---
    
//     if ( ! $from_is_card && ! $to_is_card ) { // Крипто -> Крипто
//         if ( is_numeric( $from_value_usdt ) && $from_value_usdt > 0 && is_numeric( $to_value_usdt ) && $to_value_usdt > 0 ) {
//             $base_rate = $from_value_usdt / $to_value_usdt;
//         }

//     } elseif ( ! $from_is_card && $to_is_card ) { // Крипто -> Рубли (Карта)
//         if ( is_numeric( $from_value_usdt ) && $from_value_usdt > 0 && is_numeric( $usdt_to_rub_rate ) && $usdt_to_rub_rate > 0 ) {
//             $base_rate = $from_value_usdt * $usdt_to_rub_rate;
//         }

//     } elseif ( $from_is_card && ! $to_is_card ) { // Рубли (Карта) -> Крипто
//         if ( is_numeric( $to_value_usdt ) && $to_value_usdt > 0 && is_numeric( $usdt_to_rub_rate ) && $usdt_to_rub_rate > 0 ) {
//             // Убедимся, что $usdt_to_rub_rate не ноль, чтобы избежать деления на ноль
//             $base_rate = ( 1 / $usdt_to_rub_rate ) / $to_value_usdt;
//         }
//     }
    
//     // --- 4. Применяем комиссию к "грязному" курсу ---
//     $final_rate = $base_rate * $commission_multiplier;

//     return $final_rate;
// }

/**
 * ГЛАВНАЯ ФУНКЦИЯ РАСЧЕТА (ИСПРАВЛЕНА)
 *
 * Рассчитывает "чистый" базовый курс между двумя активами,
 * БЕЗ каких-либо комиссий или наценок.
 *
 * @param int $from_id ID поста "Монеты" или "Карты".
 * @param int $to_id ID поста "Монеты" или "Карты".
 * @return float "Чистый" курс (1 -> X). 0 в случае ошибки.
 */
function get_true_exchange_rate( $from_id, $to_id ) {
    
    // --- 1. Получаем глобальный курс USDT к рублю ---
    // Убедись, что 'stoimost_1_usdt_v_rublyah' - это цена ПОКУПКИ (мы покупаем USDT за RUB)
    // А 'curs_moneti' для карт RUB - это цена ПРОДАЖИ (мы продаем USDT за RUB)
    // Для простоты, пока будем использовать один курс.
    $usdt_to_rub_rate = get_field( 'stoimost_1_usdt_v_rublyah', 'option' );
    if ( !is_numeric($usdt_to_rub_rate) || $usdt_to_rub_rate <= 0 ) return 0;

    // --- 2. Получаем данные по валютам ---
    $from_is_card = get_post_type( $from_id ) === 'payment_cards';
    $to_is_card   = get_post_type( $to_id ) === 'payment_cards';
    
    // 'curs_moneti' - это всегда ЦЕНА АКТИВА В USD
    // Для "Bitcoin" = 60000
    // Для "Tether TRC20" = 1
    // Для "Tether ERC20" = 1
    // Для "Сбербанк RUB" = 1 (мы будем использовать $usdt_to_rub_rate для конверсии)
    $from_value_usdt = get_field( 'curs_moneti', $from_id );
    $to_value_usdt   = get_field( 'curs_moneti', $to_id );
    
    if ( !is_numeric($from_value_usdt) || $from_value_usdt <= 0 ) return 0;
    if ( !is_numeric($to_value_usdt) || $to_value_usdt <= 0 ) return 0;

    $base_rate = 0;

    // --- 3. Рассчитываем "чистый" курс по 3 сценариям ---
    
    if ( !$from_is_card && !$to_is_card ) { // Крипто -> Крипто
        // 1 BTC (60000$) -> X USDT (1$)
        $base_rate = $from_value_usdt / $to_value_usdt;

    } elseif ( !$from_is_card && $to_is_card ) { // Крипто -> Рубли
        // 1 BTC (60000$) -> X RUB
        // (curs_moneti для RUB = 1, поэтому $to_value_usdt можно игнорировать)
        $base_rate = $from_value_usdt * $usdt_to_rub_rate;

    } elseif ( $from_is_card && !$to_is_card ) { // Рубли -> Крипто
        // 1 RUB -> X BTC (60000$)
        $base_rate = (1 / $usdt_to_rub_rate) / $to_value_usdt;
    }
    
    return $base_rate;
}


/**
 * AJAX-обработчик для получения списка доступных монет.
 * Рассчитывает и показывает РЕАЛЬНЫЙ курс (с комиссией) для каждой пары.
 */
add_action('wp_ajax_get_to_coins', 'get_to_coins');
add_action('wp_ajax_nopriv_get_to_coins', 'get_to_coins');
function get_to_coins() {
    if ( ! isset( $_POST['from_coin'] ) ) { wp_die('Ошибка'); }
    $from_coin_id = intval( $_POST['from_coin'] );

    // Простая проверка, что исходная монета существует
    if ( ! get_post( $from_coin_id ) ) {
        echo '<div>Не удалось рассчитать курсы (ошибка исходной валюты).</div>';
        wp_die();
    }
    
    $html = '';
    $displayed_ids = [];
    $args = [
        'post_type'      => 'rate',
        'posts_per_page' => -1,
        'meta_query'     => [
            ['key' => 'coin_from', 'value' => $from_coin_id, 'compare' => '='],
            ['key' => 'status', 'value' => '1', 'compare' => '='],
        ],
    ];

    $rates = get_posts( $args );
    if ( $rates ) {
        foreach ( $rates as $rate ) {
            $item_to_display = get_field( 'i-cart', $rate->ID ) == 1 ? get_field( 'coin_do_cart', $rate->ID ) : get_field( 'coin_do', $rate->ID );

            if ( $item_to_display && is_object( $item_to_display ) && ! in_array( $item_to_display->ID, $displayed_ids ) ) {
                
                // *** ГЛАВНОЕ ИЗМЕНЕНИЕ: Получаем курс из нашей новой функции ***
                $calculated_rate = get_true_exchange_rate( $from_coin_id, $item_to_display->ID );

                // Если курс 0 (ошибка или 0), не показываем это направление
                if ( $calculated_rate <= 0 ) {
                    continue;
                }
                
                $reserve = get_field( 'reserve', $rate->ID );
                $html .= '<div class="coin-item-right" data-id="' . esc_attr( $item_to_display->ID ) . '">';
                $html .= '<span class="coin-name">' . esc_html( $item_to_display->post_title ) . '</span>';
                $html .= '<div class="coin-details">';
                
                // Показываем курс, уже включающий комиссию
                $html .= '<span class="coin-rate">Курс: 1 -> ' . esc_html( round( $calculated_rate, 6 ) ) . '</span>';
                
                $html .= '<span class="coin-reserve">Резерв: ' . esc_html( $reserve ) . '</span>';
                $html .= '</div></div>';
                
                $displayed_ids[] = $item_to_display->ID;
            }
        }
    }
    echo $html ?: '<div>Нет доступных направлений для обмена</div>';
    wp_die();
}

/**
 * AJAX-обработчик для расчета суммы (ФИНАЛЬНАЯ ВЕРСИЯ, ИСПРАВЛЕНО)
 */
add_action('wp_ajax_calculate_exchange', 'calculate_exchange');
add_action('wp_ajax_nopriv_calculate_exchange', 'calculate_exchange');
function calculate_exchange(){
    $from_id = intval( $_POST['from_coin'] );
    $to_id   = intval( $_POST['to_coin'] );
    $amount  = floatval( $_POST['amount'] );

    if ( $amount <= 0 ) { echo '0'; wp_die(); }

    // *** ГЛАВНОЕ ИЗМЕНЕНИЕ: Получаем курс из нашей новой функции ***
    $true_rate = get_true_exchange_rate( $from_id, $to_id );

    if ( $true_rate <= 0 ) {
        echo 'Ошибка курса';
        wp_die();
    }

    // Просто умножаем сумму на ГОТОВЫЙ курс (комиссия уже в нем)
    $final_amount = $amount * $true_rate;
    
    echo round( $final_amount, 8 );
    wp_die();
}

/**
 * AJAX-обработчик для создания сделки (ФИНАЛЬНАЯ ВЕРСИЯ, ИСПРАВЛЕНО)
 */
add_action('wp_ajax_create_trade', 'create_trade');
add_action('wp_ajax_nopriv_create_trade', 'create_trade');
function create_trade(){
    $user_id = get_current_user_id();
    if( ! $user_id ){ echo 'Нужно войти на сайт.'; wp_die(); }

    $from_id = intval( $_POST['from_coin'] );
    $to_id   = intval( $_POST['to_coin'] );
    $amount  = floatval( $_POST['amount'] );

    if ( $amount <= 0 ) { echo 'Ошибка суммы'; wp_die(); }

    // *** ГЛАВНОЕ ИЗМЕНЕНИЕ: Получаем курс из нашей новой функции ***
    $true_rate = get_true_exchange_rate( $from_id, $to_id );

    if ( $true_rate <= 0 ) {
        echo 'Ошибка курса';
        wp_die();
    }

    // Рассчитываем итоговую сумму (комиссия уже в $true_rate)
    $final_amount = $amount * $true_rate;
    
    $trade_id = wp_insert_post([
        'post_type'   => 'trade',
        'post_title'  => 'Сделка от ' . current_time('d.m.Y H:i') . ' | Пользователь ' . $user_id,
        'post_status' => 'publish'
    ]);

    update_field( 'user', $user_id, $trade_id );
    update_field( 'coin-from-deal', $from_id, $trade_id );

    // Проверяем, является ли "TO" картой
    $to_is_card = get_post_type( $to_id ) === 'payment_cards';
    if ( $to_is_card ) {
        update_field( 'coin-do-deal_cart', $to_id, $trade_id );
    } else {
        update_field( 'coin-do-deal', $to_id, $trade_id );
    }
    
    update_field( 'sending_amount', $amount, $trade_id );
    update_field( 'amount_received', $final_amount, $trade_id );
    update_field( 'status-deal', 'В обработке', $trade_id );
    update_field( 'date-create', current_time('d/m/Y g:i a'), $trade_id );
    
    echo 'Сделка создана! Сумма получения: ' . round( $final_amount, 8 );
    wp_die();
}



/**
 * Шорткод [crypto_exchange_form] (ФИНАЛЬНАЯ ВЕРСИЯ)
 * * Генерирует интерфейс выбора "Слева -> Справа".
 * При выборе пары показывает кнопку-ссылку, которая ведет
 * на страницу /exchange-step/ (Скрин 2).
 * * Логика JavaScript полностью переписана на чистый JS (Vanilla JS).
 */
add_shortcode('crypto_exchange_form', 'crypto_exchange_form_shortcode');
function crypto_exchange_form_shortcode() {
    
    // 1. Получаем все активные курсы
    $active_rates = get_posts([
        'post_type'      => 'rate',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'meta_query'     => [['key' => 'status', 'value' => '1', 'compare' => '=']],
    ]);

    if (empty($active_rates)) {
        return '<p>Нет доступных направлений для обмена.</p>';
    }

    $coins_from_list = [];
    $rates_by_from_id = [];

    // 2. Группируем курсы по ID монеты "Отдаете"
    foreach ($active_rates as $rate_post) {
        $from_coin = get_field('coin_from', $rate_post->ID); 
        if ($from_coin && is_object($from_coin)) {
            
            $coins_from_list[$from_coin->ID] = $from_coin;
            
            // Определяем монету "Куда"
            $to_coin = null;
            if (get_field('i-cart', $rate_post->ID) == 1) {
                $to_coin = get_field('coin_do_cart', $rate_post->ID);
            } else {
                $to_coin = get_field('coin_do', $rate_post->ID);
            }

            if ($to_coin && is_object($to_coin)) {
                // Сохраняем объект с данными для JS
                $rates_by_from_id[$from_coin->ID][$to_coin->ID] = [
                    'id'    => $to_coin->ID,
                    'title' => $to_coin->post_title,
                    'rate'  => get_true_exchange_rate($from_coin->ID, $to_coin->ID), // Используем твою "чистую" функцию
                    'reserve' => get_field('reserve', $rate_post->ID)
                ];
            }
        }
    }
    
    // Сортируем левый список по названию
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

<div id="exchange-continue-wrapper" style="margin-top: 20px; display: none;">
    <a href="#" id="exchange-continue-btn" class="button">Продолжить</a>
</div>

<script type="application/json" id="exchange_rates_data">
<?php echo json_encode($rates_by_from_id); ?>
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {

    // 1. Получаем наши данные и элементы DOM
    const ratesData = JSON.parse(document.getElementById('exchange_rates_data').textContent);
    let selectedFromId = null;
    let selectedToId = null;

    const continueBtn = document.getElementById('exchange-continue-btn');
    const continueWrapper = document.getElementById('exchange-continue-wrapper');
    const toListContainer = document.getElementById('coins-to-list');
    const wrapper = document.getElementById('new-exchange-flow'); // Обертка для делегирования

    // 2. Функция обновления кнопки "Продолжить"
    function updateContinueButton() {
        if (selectedFromId && selectedToId) {
            // Если выбрано ОБА - строим ссылку и показываем кнопку
            const pairLink = '<?php echo home_url("/exchange-step/"); ?>' +
                '?from=' + selectedFromId +
                '&to=' + selectedToId;

            continueBtn.href = pairLink;
            continueWrapper.style.display = 'block';
        } else {
            // Если чего-то не хватает - прячем кнопку
            continueWrapper.style.display = 'none';
        }
    }

    // 3. Используем делегирование событий на общей обертке
    if (wrapper) {
        wrapper.addEventListener('click', function(e) {

            // 4. Клик по левой колонке (.coin-item-left)
            const leftItem = e.target.closest('.coin-item-left');
            if (leftItem) {
                // Снимаем 'active' со всех левых
                wrapper.querySelectorAll('.coin-item-left').forEach(item => item.classList.remove(
                    'active'));
                // Добавляем 'active' нажатому
                leftItem.classList.add('active');

                selectedFromId = leftItem.dataset.id;
                selectedToId = null; // Сбрасываем выбор справа

                let toListHtml = '';
                const availableCoins = ratesData[selectedFromId];

                if (availableCoins && Object.keys(availableCoins).length > 0) {
                    // Перебираем объект с доступными монетами
                    Object.values(availableCoins).forEach(coin => {
                        // Это НЕ ссылка, это просто DIV для выбора
                        toListHtml += '<div class="coin-item-right" data-id="' + coin.id + '">';
                        toListHtml += '<span class="coin-name">' + coin.title + '</span>';
                        toListHtml += '<div class="coin-details">';
                        toListHtml += '<span class="coin-rate">1 -> ' + parseFloat(coin.rate)
                            .toFixed(6) + '</span>';
                        if (coin.reserve) {
                            toListHtml += '<span class="coin-reserve">Резерв: ' + coin.reserve +
                                '</span>';
                        }
                        toListHtml += '</div></div>';
                    });
                    toListContainer.innerHTML = toListHtml;
                } else {
                    toListContainer.innerHTML = '<div>Нет доступных направлений</div>';
                }

                updateContinueButton(); // Прячем кнопку (т.к. справа выбор сброшен)
            }

            // 5. Клик по правой колонке (.coin-item-right)
            const rightItem = e.target.closest('.coin-item-right');
            if (rightItem) {
                // Снимаем 'active' со всех правых
                wrapper.querySelectorAll('.coin-item-right').forEach(item => item.classList.remove(
                    'active'));
                // Добавляем 'active' нажатому
                rightItem.classList.add('active');

                selectedToId = rightItem.dataset.id;

                updateContinueButton(); // Показываем кнопку
            }
        });
    }

    // 6. Авто-клик по первой монете при загрузке
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

    // Сценарий 1: Обновляем курсы, когда сохраняется сама монета/карта (упрощенная логика)
    if ( $post_type === 'monety' || $post_type === 'payment_cards' ) {
        // Эта логика может быть расширена, чтобы пересчитать все связанные пары, но пока оставляем простой.
    }

    // Сценарий 2: Обновляем курсы, когда сохраняется сама пара "Курс"
    if ( $post_type === 'rate' ) {
        remove_action( 'acf/save_post', 'unified_rates_updater', 20 );

        // Обновляем поле 'well'
        $coin_from = get_field( 'coin_from', $post_id );
        if ( $coin_from && is_object($coin_from) ) {
            $rate_from = get_field( 'curs_moneti', $coin_from->ID );
            if ( isset( $rate_from ) && $rate_from !== '' ) { update_field( 'well', $rate_from, $post_id ); }
        }
        
        // Обновляем поле 'well_2'
        $coin_to = get_field( 'coin_do', $post_id ) ?: get_field( 'coin_do_cart', $post_id );
        
        if ( $coin_to && is_object($coin_to) && $coin_from && is_object($coin_from) ) {
            $rate_to_value = 0;
            $to_is_card = get_post_type($coin_to->ID) === 'payment_cards';
            $from_is_card = get_post_type($coin_from->ID) === 'payment_cards';

            if (!$from_is_card && $to_is_card) {
                // --- НОВЫЙ БЛОК: Рассчитываем и сохраняем реальный курс для карт ---
                $from_value_usdt = get_field('curs_moneti', $coin_from->ID);
                $usdt_to_rub_rate = get_field('stoimost_1_usdt_v_rublyah', 'option');
                if (is_numeric($from_value_usdt) && is_numeric($usdt_to_rub_rate) && $usdt_to_rub_rate > 0) {
                    $rate_to_value = $from_value_usdt * $usdt_to_rub_rate;
                }
            } else {
                // Старая логика: просто копируем курс USDT для крипты или значение по умолчанию для карты
                $rate_to_value = get_field( 'curs_moneti', $coin_to->ID );
            }

            // Обновляем поле, только если есть что обновлять
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





/**
 * AJAX-обработчик для СОЗДАНИЯ ЗАЯВКИ (Сделки) (ИСПРАВЛЕН)
 */
add_action('wp_ajax_create_order_ajax', 'ajax_create_order_handler');
add_action('wp_ajax_nopriv_create_order_ajax', 'ajax_create_order_handler');

function ajax_create_order_handler() {
    
    // 1. Безопасность: Проверяем Nonce
    if (!isset($_POST['order_nonce']) || !wp_verify_nonce($_POST['order_nonce'], 'create_order_nonce')) {
        wp_send_json_error(['message' => 'Ошибка безопасности.']);
        return;
    }

    // 2. Получаем и чистим данные
    $from_id = intval($_POST['from_id']);
    $to_id   = intval($_POST['to_id']);
    $amount_send = floatval($_POST['amount_send']);
    $contact_info = sanitize_text_field($_POST['contact_info']);
    $receiver_address = sanitize_text_field($_POST['receiver_address']); // Кошелек клиента

    if (empty($from_id) || empty($to_id) || empty($amount_send) || empty($contact_info) || empty($receiver_address)) {
        wp_send_json_error(['message' => 'Все поля обязательны для заполнения.']);
        return;
    }
    
    // 3. Пересчитываем курс НА СЕРВЕРЕ (НЕ ДОВЕРЯЕМ КЛИЕНТУ)
    
    // 3.1. Получаем "чистый" курс
    $base_rate = get_true_exchange_rate($from_id, $to_id);
    if ($base_rate <= 0) {
        wp_send_json_error(['message' => 'Не удалось рассчитать курс.']);
        return;
    }
    
    // 3.2. Получаем наценку
    $percent_markup = get_field('proczent_komissii', 'option');
    $markup_multiplier = 1 + (floatval($percent_markup) / 100);
    $rate_with_markup = $base_rate * $markup_multiplier;
    
    // 3.3. Считаем "грязную" сумму
    $amount_receive_gross = $amount_send * $rate_with_markup;
    $final_amount_receive = $amount_receive_gross;
    
    // 3.4. Вычитаем ФИКСИРОВАННУЮ комиссию (если МЫ платим)
    $to_is_card = (get_post_type($to_id) === 'payment_cards');
    if (!$to_is_card) { // Мы отправляем КРИПТУ
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
    
    // 3.5. Финальная проверка
    if ($final_amount_receive <= 0) {
        wp_send_json_error(['message' => 'Сумма слишком мала для покрытия комиссии сети.']);
        return;
    }

    // 4. Находим НАШ кошелек/карту для приема (остается без изменений)
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
        wp_send_json_error(['message' => 'Ошибка: Не настроены реквизиты для приема.']);
        return;
    }

    // 5. Создаем пост "Сделка" (trade) (остается без изменений)
    $trade_title = 'Заявка: ' . get_the_title($from_id) . ' на ' . get_the_title($to_id);
    $trade_id = wp_insert_post([
        'post_type'   => 'trade',
        'post_title'  => $trade_title,
        'post_status' => 'publish',
    ]);

    if (is_wp_error($trade_id)) {
        wp_send_json_error(['message' => 'Не удалось создать заявку в базе.']);
        return;
    }

    // 6. Заполняем ACF-поля для сделки
    update_field('user', get_current_user_id(), $trade_id);
    update_field('coin-from-deal', $from_id, $trade_id);
    
    if ($to_is_card) {
        update_field('coin-do-deal_cart', $to_id, $trade_id);
    } else {
        update_field('coin-do-deal', $to_id, $trade_id);
    }
    
    update_field('sending_amount', $amount_send, $trade_id);
    update_field('amount_received', $final_amount_receive, $trade_id); // Сохраняем ЧИСТУЮ сумму
    
    update_field('contact_info', $contact_info, $trade_id);
    update_field('receiver_address', $receiver_address, $trade_id);
    update_field('assigned_wallet_address', $our_receiver_details, $trade_id);
    
    update_field('status-deal', 'Ожидание оплаты', $trade_id);
    update_field('date-create', current_time('d/m/Y g:i a'), $trade_id);
    
    $timer_end_timestamp = time() + (30 * 60); // 30 минут
    update_field('order_timer_end', $timer_end_timestamp, $trade_id);

    // 7. Отправляем УСПЕХ
    wp_send_json_success(['order_url' => get_permalink($trade_id)]);
}