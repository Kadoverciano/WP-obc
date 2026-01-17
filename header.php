<?php
/**
 * The base (and only) template
 *
 * @package WordPress
 * @subpackage exchange
 */

$blank_show_footer = get_theme_mod( 'blank_show_copyright', true );
$blank_show_header = get_theme_mod( 'header_text', false );
$blank_description = get_bloginfo( 'description', 'display' );

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>><?php wp_body_open(); ?><div id="page">

        <div class="wrapper">
            <header class="header">
                <div class="container">
                    <div class="header-inner">
                        <div class="header-item">
                            <div class="logo">
                                <img src="/wp-content/uploads/2025/09/logo-not-fon.png" alt="crypto exchange">
                            </div>
                        </div>
                        <div class="header-item">
                            <?php
                            wp_nav_menu( array(
                                'theme_location' => 'header_menu',
                                'container' => 'nav', 
                                'container_class' => 'header-nav', 
                                'menu_class' => 'header-menu', 
                                'fallback_cb' => false,
                            ) );
                        ?>
                        </div>
                        <div class="header-item">
                            <button class="btn question-btn">Задать вопрос</button>
                        </div>
                    </div>
                </div>
            </header>

            <main>