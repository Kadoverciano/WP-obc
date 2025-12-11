<?php

function create_exchange_rates_table() {
    global $wpdb;
    
    // Название таблицы с префиксом WP (например, wp_exchange_rates_cache)
    $table_name = $wpdb->prefix . 'exchange_rates_cache';
    
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        coin_id mediumint(9) NOT NULL,       -- ID монеты (из Post ID)
        exchange_name varchar(50) NOT NULL,  -- Название биржи (ChangeNOW, SimpleSwap и т.д.)
        price decimal(20, 10) DEFAULT 0,     -- Курс с высокой точностью
        status varchar(10) DEFAULT 'OK',     -- Статус: OK, FAIL, OLD
        updated_at datetime DEFAULT CURRENT_TIMESTAMP, -- Время последнего обновления
        PRIMARY KEY  (id),
        UNIQUE KEY coin_exchange (coin_id, exchange_name) -- Защита от дублей (одна биржа - одна монета)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}

// Запускаем создание таблицы при инициализации (можно удалить этот хук после одного запуска)
add_action( 'init', 'create_exchange_rates_table' );