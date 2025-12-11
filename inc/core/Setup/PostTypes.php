<?php
namespace WpOBC\Setup;

class PostTypes {
    public static function init() {
        add_action('init', [self::class, 'registerCoins']);
        add_action('init', [self::class, 'registerCards']);
        add_action('init', [self::class, 'registerExchanges']);
        add_action('init', [self::class, 'registerRates']);
        add_action('init', [self::class, 'registerOrders']);
        add_action('init', [self::class, 'registerNetworks']);
        add_action('init', [self::class, 'registerWallets']);
        
        // Редиректы со страниц одиночных записей (чтобы не открывались пустые страницы)
        add_action('template_redirect', [self::class, 'disableSingleViews']);
    }

    public static function registerCoins() {
        register_post_type('monety', [
            'labels' => ['name' => 'Монеты', 'singular_name' => 'Монета', 'menu_name' => 'Монеты'],
            'public' => true,
            'show_in_rest' => true,
            'supports' => ['title', 'thumbnail', 'custom-fields'],
            'menu_icon' => 'dashicons-coins',
            'menu_position' => 5,
            'has_archive' => false
        ]);
    }

    public static function registerCards() {
        register_post_type('payment_cards', [
            'labels' => ['name' => 'Карты платежей', 'singular_name' => 'Карта', 'menu_name' => 'Карты'],
            'public' => true,
            'supports' => ['title', 'thumbnail', 'custom-fields'],
            'menu_icon' => 'dashicons-credit-card',
            'menu_position' => 6,
            'has_archive' => false
        ]);
    }

    public static function registerExchanges() {
        register_post_type('exchange', [
            'labels' => ['name' => 'Биржи', 'singular_name' => 'Биржа', 'menu_name' => 'Биржи API'],
            'public' => true, // Нужно для получения настроек
            'show_ui' => true,
            'supports' => ['title', 'custom-fields'],
            'menu_icon' => 'dashicons-chart-line',
            'menu_position' => 7,
        ]);
    }

    public static function registerRates() {
        register_post_type('rate', [
            'labels' => ['name' => 'Курсы (Пары)', 'singular_name' => 'Курс', 'menu_name' => 'Направления обмена'],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'supports' => ['title', 'custom-fields'],
            'menu_icon' => 'dashicons-money',
            'menu_position' => 8,
        ]);
    }

    public static function registerOrders() {
        register_post_type('trade', [
            'labels' => ['name' => 'Заявки', 'singular_name' => 'Заявка', 'menu_name' => 'Заявки'],
            'public' => true,
            'publicly_queryable' => true,
            'rewrite' => ['slug' => 'order'],
            'show_ui' => true,
            'supports' => ['title', 'custom-fields'],
            'menu_icon' => 'dashicons-admin-network',
            'menu_position' => 9,
        ]);
    }
    
    public static function registerNetworks() {
        register_post_type('network', [
            'labels' => ['name' => 'Сети', 'singular_name' => 'Сеть'],
            'public' => false, 'show_ui' => true, 'show_in_menu' => true,
            'menu_icon' => 'dashicons-networking', 'menu_position' => 10
        ]);
    }

    public static function registerWallets() {
        register_post_type('wallet', [
            'labels' => ['name' => 'Наши Кошельки', 'singular_name' => 'Кошелек'],
            'public' => false, 'show_ui' => true, 'show_in_menu' => true,
            'menu_icon' => 'dashicons-wallet', 'menu_position' => 11
        ]);
    }

    public static function disableSingleViews() {
        if (is_singular(['monety', 'payment_cards', 'exchange', 'rate', 'network', 'wallet'])) {
            wp_safe_redirect(home_url(), 302);
            exit;
        }
    }
}