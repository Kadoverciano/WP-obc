<?php get_header();?>

<div class="content-page">
    <div class="container">
        <h1 class="title-h1">Обменник криптовалют</h1>

        <div class="exchange">
            <div class="exchange-inner">
                <div class="exchange-item"><?=do_shortcode('[crypto_exchange_form]'); ?></div>
            </div>
        </div>
    </div>
</div>


<?php get_footer();?>