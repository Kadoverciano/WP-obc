<?php
namespace WpOBC\Setup;

class Database {
    const TABLE_RATES = 'exchange_rates_cache';

    public static function init() {
        // Создаем таблицу при активации темы или плагина
        add_action('after_switch_theme', [self::class, 'createTables']);
        // Можно вызывать вручную при отладке
    }

    public static function createTables() {
        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_RATES;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            coin_id mediumint(9) NOT NULL,
            exchange_name varchar(50) NOT NULL,
            price decimal(24, 12) DEFAULT 0,
            status varchar(10) DEFAULT 'OK',
            updated_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY coin_exchange (coin_id, exchange_name),
            INDEX status_idx (status),
            INDEX updated_at_idx (updated_at)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}