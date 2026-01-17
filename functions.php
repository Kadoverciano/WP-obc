<?php
/**
 * Theme functions and definitions
 *
 * @package WordPress
 * @subpackage Exchange
 */

if ( ! defined( '_S_VERSION' ) ) {
    // Version
    define( '_S_VERSION', '1.0.0' );
}

/**
 * 1. Настройка темы (Стандартный WP)
 */
function exchange_setup() {
    load_theme_textdomain( 'exchange' );
    add_theme_support( 'automatic-feed-links' );
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'custom-logo', [
        'height'      => 256,
        'width'       => 256,
        'flex-height' => true,
        'flex-width'  => true,
    ]);
    
    // Регистрация меню
    register_nav_menus([
        'header_menu' => __( 'Header Menu', 'exchange' ),
        'footer_menu' => __( 'Footer Menu', 'exchange' ),
    ]);
}
add_action( 'after_setup_theme', 'exchange_setup' );

/**
 * 2. Подключение стилей и скриптов
 */
function exchange_scripts() {
    // Стили
    wp_enqueue_style('reset-css', get_template_directory_uri() . '/assets/css/reset.css', [], '1.0.0');
    wp_enqueue_style('monserat', get_template_directory_uri() . '/assets/font/Montserrat/stylesheet.css', [], '1.0.0');    
    wp_enqueue_style('main-style', get_template_directory_uri() . '/assets/css/style.css', ['reset-css'], '1.0.0');
    wp_enqueue_style('main-mobile', get_template_directory_uri() . '/assets/css/mobile.css', ['reset-css'], '1.0.0');

    // Скрипты
    wp_enqueue_script('app-js', get_template_directory_uri() . '/assets/js/app.js', ['jquery'], '1.0.0', true);
    wp_enqueue_script('modal-js', get_template_directory_uri() . '/assets/js/modal.js', ['jquery', 'app-js'], '1.0.0', true);
}
add_action( 'wp_enqueue_scripts', 'exchange_scripts' );

/**
 * 3. SVG Support
 */
add_filter( 'upload_mimes', function( $mimes ) {
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
});

add_action( 'admin_head', function() {
    echo '<style>.attachment-266x266, .thumbnail img { width: 100% !important; height: auto !important; }</style>';
});

// =========================================================================
// WpOBC CORE: ПОДКЛЮЧЕНИЕ ЯДРА (НОВАЯ АРХИТЕКТУРА)
// =========================================================================

// Проверка существования файлов перед подключением, чтобы избежать Fatal Error
$core_path = get_template_directory() . '/inc/core';

// 1. Setup классы (Регистрация типов постов и БД)
if (file_exists($core_path . '/Setup/PostTypes.php')) {
    require_once $core_path . '/Setup/PostTypes.php';
}
if (file_exists($core_path . '/Setup/Database.php')) {
    require_once $core_path . '/Setup/Database.php';
}

// 2. Интерфейсы и Сервисы
if (file_exists($core_path . '/Interfaces/ExchangeProviderInterface.php')) {
    require_once $core_path . '/Interfaces/ExchangeProviderInterface.php';
}
if (file_exists($core_path . '/Providers/ChangeNowProvider.php')) {
    require_once $core_path . '/Providers/ChangeNowProvider.php';
}
if (file_exists($core_path . '/Services/RateService.php')) {
    require_once $core_path . '/Services/RateService.php';
}

if (file_exists($core_path . '/Services/CalculationService.php')) {
    require_once $core_path . '/Services/CalculationService.php';
}

if (file_exists($core_path . '/Api/ExchangeController.php')) {
    require_once $core_path . '/Api/ExchangeController.php';
}

// ПОДКЛЮЧЕНИЕ ШОРТКОДА
if (file_exists(get_template_directory() . '/inc/core/Shortcodes/ExchangeForm.php')) {
    require_once get_template_directory() . '/inc/core/Shortcodes/ExchangeForm.php';
    \WpOBC\Shortcodes\ExchangeForm::init();
}

// 3. Инициализация (Запуск)
// Проверяем существование классов перед вызовом
if (class_exists('\WpOBC\Setup\PostTypes')) {
    \WpOBC\Setup\PostTypes::init();
}
if (class_exists('\WpOBC\Setup\Database')) {
    \WpOBC\Setup\Database::init();
}

// if (class_exists('\WpOBC\Api\ExchangeController')) {
//     \WpOBC\Api\ExchangeController::init();
// }
\WpOBC\Api\ExchangeController::init();

// 4. Настройка Cron (Фоновое обновление курсов)
add_action('init', function() {
    if (!wp_next_scheduled('wp_obc_cron_update_rates')) {
        wp_schedule_event(time(), 'every_minute', 'wp_obc_cron_update_rates');
    }
});

// Обработчик задачи Cron
add_action('wp_obc_cron_update_rates', function() {
    if (class_exists('\WpOBC\Services\RateService') && class_exists('\WpOBC\Providers\ChangeNowProvider')) {
        $service = new \WpOBC\Services\RateService();
        $service->addProvider(new \WpOBC\Providers\ChangeNowProvider());
        // Обновляем 5 устаревших монет
        $service->updateStaleRates(5);
    }
});

// Добавляем интервал "каждую минуту"
add_filter('cron_schedules', function ($schedules) {
    if (!isset($schedules['every_minute'])) {
        $schedules['every_minute'] = ['interval' => 60, 'display'  => __('Каждую минуту')];
    }
    return $schedules;
});

// 5. Блокировка полей ACF (Read-only для курсов)
add_filter('acf/load_field/name=curs_moneti', function( $field ) {
    if (is_admin()) {
        $field['readonly'] = 1;
        $field['instructions'] = 'Обновляется автоматически через Cron (RateService).';
    }
    return $field;
});