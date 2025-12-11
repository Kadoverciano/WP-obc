<?php

// =========================================================================
// 1. РЕГИСТРАЦИЯ POST TYPES (CPT)
// =========================================================================

// Монеты
add_action( 'init', 'register_monety_cpt' );
function register_monety_cpt() {
    register_post_type( 'monety', array(
        'labels' => array('name' => 'Монеты', 'singular_name' => 'Монета', 'menu_name' => 'Монеты', 'add_new' => 'Добавить монету', 'add_new_item' => 'Добавить новую монету', 'edit_item' => 'Редактировать монету', 'new_item' => 'Новая монета', 'view_item' => 'Просмотреть монету', 'all_items' => 'Все монеты', 'search_items' => 'Поиск монет', 'not_found' => 'Монеты не найдены', 'not_found_in_trash' => 'В корзине монет не найдено'),
        'public' => true, 'show_ui' => true, 'show_in_menu' => true, 'show_in_rest' => true,
        'supports' => array( 'title', 'thumbnail', 'custom-fields' ),
        'has_archive' => false, 'rewrite' => false, 'publicly_queryable' => true,
        'capability_type' => 'post', 'map_meta_cap' => true, 'menu_position' => 5, 'menu_icon' => 'dashicons-coins',
    ));
}
add_action( 'template_redirect', 'monety_force_no_single_redirect' );
function monety_force_no_single_redirect() {
    if ( is_singular( 'monety' ) ) { wp_safe_redirect( home_url(), 302 ); exit; }
}

// Карты платежей
add_action( 'init', 'register_post_type_payment_cards' );
function register_post_type_payment_cards() {
    register_post_type( 'payment_cards', array(
        'labels' => array('name' => 'Карты платежей', 'singular_name' => 'Карта платежей', 'add_new' => 'Добавить карту', 'add_new_item' => 'Добавить новую карту', 'edit_item' => 'Редактировать карту', 'new_item' => 'Новая карта', 'view_item' => 'Просмотр карты', 'search_items' => 'Искать карты', 'not_found' => 'Карты не найдены', 'not_found_in_trash' => 'В корзине карт не найдено', 'menu_name' => 'Карты платежей'),
        'public' => true, 'show_ui' => true, 'show_in_menu' => true, 'menu_icon' => 'dashicons-credit-card',
        'supports' => array( 'title', 'thumbnail', 'custom-fields' ),
        'has_archive' => false, 'publicly_queryable' => false, 'rewrite' => false, 'menu_position' => 6,
    ));
}
add_action( 'template_redirect', 'payment_cards_force_no_single_redirect' );
function payment_cards_force_no_single_redirect() {
    if ( is_singular( 'payment_cards' ) ) { wp_safe_redirect( home_url(), 302 ); exit; }
}

// Биржи
add_action('init', 'register_exchanges_cpt');
function register_exchanges_cpt() {
    register_post_type('exchange', array(
        'labels' => array('name' => 'Биржи', 'singular_name' => 'Биржа', 'menu_name' => 'Биржи', 'add_new' => 'Добавить биржу', 'add_new_item' => 'Добавить новую биржу', 'edit_item' => 'Редактировать биржу', 'new_item' => 'Новая биржа', 'view_item' => 'Посмотреть биржу', 'all_items' => 'Все биржи', 'search_items' => 'Искать биржу', 'not_found' => 'Биржи не найдены'),
        'public' => true, 'has_archive' => true, 'show_in_rest' => true, 'menu_position' => 7, 'menu_icon' => 'dashicons-chart-line',
        'supports' => array('title', 'thumbnail', 'custom-fields'),
    ));
}

// Курсы
add_action('init', 'register_rates_cpt');
function register_rates_cpt() {
    register_post_type('rate', array(
        'labels' => array('name' => 'Курсы', 'singular_name' => 'Курс', 'menu_name' => 'Курсы', 'add_new' => 'Добавить курс', 'add_new_item' => 'Добавить новый курс', 'edit_item' => 'Редактировать курс', 'new_item' => 'Новый курс', 'view_item' => 'Посмотреть курс', 'all_items' => 'Все курсы', 'search_items' => 'Искать курс', 'not_found' => 'Курсы не найдены'),
        'public' => false, 'show_ui' => true, 'show_in_menu' => true, 'menu_icon' => 'dashicons-money', 'menu_position' => 8,
        'supports' => array('title', 'thumbnail', 'custom-fields'),
    ));
}

// Сделки
add_action('init', 'register_trades_cpt');
function register_trades_cpt() {
    register_post_type('trade', array(
        'labels' => array('name' => 'Сделки', 'singular_name' => 'Сделка', 'menu_name' => 'Сделки', 'add_new' => 'Добавить сделку', 'add_new_item' => 'Добавить новую сделку', 'edit_item' => 'Редактировать сделку', 'new_item' => 'Новая сделка', 'view_item' => 'Посмотреть сделку', 'all_items' => 'Все сделки', 'search_items' => 'Искать сделку', 'not_found' => 'Сделки не найдены'),
        'public' => true, 'publicly_queryable' => true, 'has_archive' => false, 'rewrite' => array('slug' => 'order'),
        'show_ui' => true, 'show_in_menu' => true, 'menu_icon' => 'dashicons-admin-network', 'menu_position' => 9,
        'supports' => array('title', 'custom-fields'),
    ));
}

// Сети
add_action('init', 'register_network_cpt');
function register_network_cpt() {
    register_post_type('network', array(
        'labels' => array('name' => 'Сети', 'singular_name' => 'Сеть'),
        'public' => false, 'show_ui' => true, 'show_in_menu' => true, 'menu_icon' => 'dashicons-networking', 'supports' => array('title', 'custom-fields'), 'menu_position' => 10,
    ));
}

// Кошельки
add_action('init', 'register_wallet_cpt');
function register_wallet_cpt() {
    register_post_type('wallet', array(
        'labels' => array('name' => 'Наши Кошельки', 'singular_name' => 'Кошелек'),
        'public' => false, 'show_ui' => true, 'show_in_menu' => true, 'menu_icon' => 'dashicons-wallet', 'supports' => array('title', 'custom-fields'), 'menu_position' => 11,
    ));
}

get_template_part('/inc/custom/logics/logics');

//get_template_part('/inc/custom/helper/process_coin_sequential_worker');