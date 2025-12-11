<?php
/**
 * Intentionally Blank Theme functions
 *
 * @package WordPress
 * @subpackage exchange
 */

if ( ! function_exists( 'blank_setup' ) ) :
	/**
	 * Sets up theme defaults and registers the various WordPress features that
	 * this theme supports.
	 */
	function blank_setup() {
		load_theme_textdomain( 'exchange' );
		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );

		// This theme allows users to set a custom background.
		add_theme_support(
			'custom-background',
			array(
				'default-color' => 'f5f5f5',
			)
		);

		add_theme_support( 'custom-logo' );
		add_theme_support(
			'custom-logo',
			array(
				'height'      => 256,
				'width'       => 256,
				'flex-height' => true,
				'flex-width'  => true,
				'header-text' => array( 'site-title', 'site-description' ),
			)
		);
	}
endif; // end function_exists blank_setup.

add_action( 'after_setup_theme', 'blank_setup' );

remove_action( 'wp_head', '_custom_logo_header_styles' );

if ( ! is_admin() ) {
	add_action(
		'wp_enqueue_scripts',
		function() {
			wp_dequeue_style( 'global-styles' );
			wp_dequeue_style( 'classic-theme-styles' );
			wp_dequeue_style( 'wp-block-library' );
		}
	);
}
/**
 * Sets up theme defaults and registers the various WordPress features that
 * this theme supports.

 * @param class $wp_customize Customizer object.
 */
function blank_customize_register( $wp_customize ) {
	$wp_customize->remove_section( 'static_front_page' );

	$wp_customize->add_section(
		'blank_footer',
		array(
			'title'      => __( 'Footer', 'exchange' ),
			'priority'   => 120,
			'capability' => 'edit_theme_options',
			'panel'      => '',
		)
	);
	$wp_customize->add_setting(
		'blank_copyright',
		array(
			'type'              => 'theme_mod',
			'default'           => __( 'Intentionally Blank - Proudly powered by WordPress', 'exchange' ),
			'sanitize_callback' => 'wp_kses_post',
		)
	);

	/**
	 * Checkbox sanitization function

	 * @param bool $checked Whether the checkbox is checked.
	 * @return bool Whether the checkbox is checked.
	 */
	function blank_sanitize_checkbox( $checked ) {
		// Returns true if checkbox is checked.
		return ( ( isset( $checked ) && true === $checked ) ? true : false );
	}
	$wp_customize->add_setting(
		'blank_show_copyright',
		array(
			'default'           => true,
			'sanitize_callback' => 'blank_sanitize_checkbox',
		)
	);
	$wp_customize->add_control(
		'blank_copyright',
		array(
			'type'     => 'textarea',
			'label'    => __( 'Copyright Text', 'exchange' ),
			'section'  => 'blank_footer',
			'settings' => 'blank_copyright',
			'priority' => '10',
		)
	);
	$wp_customize->add_control(
		'blank_footer_copyright_hide',
		array(
			'type'     => 'checkbox',
			'label'    => __( 'Show footer with copyright Text', 'exchange' ),
			'section'  => 'blank_footer',
			'settings' => 'blank_show_copyright',
			'priority' => '20',
		)
	);
}
add_action( 'customize_register', 'blank_customize_register', 100 );



// Подключение стилей и скриптов
function exchange_enqueue_assets() {

    // Стили
    wp_enqueue_style('reset-css', get_template_directory_uri() . '/assets/css/reset.css', array(), '1.0.0');

    wp_enqueue_style('monserat', get_template_directory_uri() . '/assets/font/Montserrat/stylesheet.css', array(), '1.0.0');	

    wp_enqueue_style('main-style', get_template_directory_uri() . '/assets/css/style.css', array('reset-css'), '1.0.0');
    wp_enqueue_style('main-mobile', get_template_directory_uri() . '/assets/css/mobile.css', array('reset-css'), '1.0.0');

    // Скрипты
    wp_enqueue_script('app-js', get_template_directory_uri() . '/assets/js/app.js', array('jquery'), '1.0.0', true);

    wp_enqueue_script('modal-js', get_template_directory_uri() . '/assets/js/modal.js', array('jquery', 'app-js'), '1.0.0', true);
}
add_action('wp_enqueue_scripts', 'exchange_enqueue_assets');


// Регистрация меню
function exchange_register_menus() {
    register_nav_menus( array(
        'header_menu' => __( 'Header Menu', 'exchange' ),
        'footer_menu' => __( 'Footer Menu', 'exchange' ),
    ) );
}
add_action( 'after_setup_theme', 'exchange_register_menus' );

// Разрешаем загрузку SVG
function allow_svg_uploads( $mimes ) {
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
}
add_filter( 'upload_mimes', 'allow_svg_uploads' );

// Исправляем отображение SVG в медиа-библиотеке
function fix_svg_display() {
    echo '<style>
        .attachment-266x266, .thumbnail img {
            width: 100% !important;
            height: auto !important;
        }
    </style>';
}
add_action( 'admin_head', 'fix_svg_display' );




// =========================================================================
// WpOBC CORE: ПОДКЛЮЧЕНИЕ ЯДРА
// =========================================================================

// 1. Подключаем файлы Setup
require_once get_template_directory() . '/inc/core/Setup/PostTypes.php';
require_once get_template_directory() . '/inc/core/Setup/Database.php';

// 2. Подключаем Интерфейсы и Сервисы (То, что мы писали в прошлом шаге)
require_once get_template_directory() . '/inc/core/Interfaces/ExchangeProviderInterface.php';
require_once get_template_directory() . '/inc/core/Providers/ChangeNowProvider.php';
require_once get_template_directory() . '/inc/core/Providers/SimpleSwapProvider.php'; // Раскомментируй, когда создашь файл
require_once get_template_directory() . '/inc/core/Services/RateService.php';

// 3. Инициализация
\WpOBC\Setup\PostTypes::init();
\WpOBC\Setup\Database::init();

// 4. Настройка Cron (из предыдущего ответа)
add_action('init', function() {
    if (!wp_next_scheduled('wp_obc_cron_update_rates')) {
        wp_schedule_event(time(), 'every_minute', 'wp_obc_cron_update_rates');
    }
});

add_action('wp_obc_cron_update_rates', function() {
    $service = new \WpOBC\Services\RateService();
    $service->addProvider(new \WpOBC\Providers\ChangeNowProvider());
    // $service->addProvider(new \WpOBC\Providers\SimpleSwapProvider());
    $service->updateStaleRates(5);
});

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