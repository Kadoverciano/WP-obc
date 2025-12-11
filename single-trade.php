<?php
get_header();



// Получаем все наши поля
$from_coin_post = get_field('coin-from-deal');
$to_coin_post   = get_field('coin-do-deal') ?: get_field('coin-do-deal_cart');

$amount_send = get_field('sending_amount');
$amount_receive = get_field('amount_received');

$client_address = get_field('receiver_address'); // Кошелек клиента
$our_address    = get_field('assigned_wallet_address'); // НАШ кошелек

$order_status   = get_field('status-deal');
$timer_end_ts   = get_field('order_timer_end');

$from_is_card = (get_post_type($from_coin_post->ID) === 'payment_cards');

?>

<div class="content-page">
    <div class="container order-page">

        <?php if ($order_status === 'Ожидание оплаты'): ?>

        <h1 class="title-h1">Ожидаем оплату заявки #<?php the_ID(); ?></h1>

        <div id="order-timer" data-timestamp="<?php echo $timer_end_ts; ?>">
            Осталось времени: 59:59
        </div>

        <p>Переведите **ТОЧНУЮ сумму** одним переводом:</p>
        <h3 class="order-amount-to-send"><?php echo $amount_send; ?>
            <?php echo esc_html($from_coin_post->post_title); ?></h3>

        <p>На <?php echo $from_is_card ? 'указанные реквизиты:' : 'указанный кошелек:'; ?></p>
        <div class="our-wallet-box">
            <strong><?php echo $our_address; ?></strong>
        </div>

        <?php if (!$from_is_card): ?>
        <p style="color:red; font-weight: bold;">
            ВНИМАНИЕ! Убедитесь, что отправляете <?php echo esc_html($from_coin_post->post_title); ?>.
            Отправка другой монеты или в другой сети приведет к <strong>потере средств!</strong>
        </p>
        <?php endif; ?>

        <hr>

        <h4>Детали вашей заявки:</h4>
        <ul>
            <li><strong>Получаете:</strong> <?php echo $amount_receive; ?>
                <?php echo esc_html($to_coin_post->post_title); ?></li>
            <li><strong>На ваш счет:</strong> <?php echo esc_html($client_address); ?></li>
        </ul>

        <?php elseif ($order_status === 'Завершено'): ?>

        <h1 class="title-h1">Заявка #<?php the_ID(); ?> Завершена</h1>
        <p>Мы получили вашу оплату и отправили <?php echo $amount_receive; ?>
            <?php echo esc_html($to_coin_post->post_title); ?> на ваш счет.</p>

        <?php else: // Отменено и т.д. ?>

        <h1 class="title-h1">Заявка #<?php the_ID(); ?> <?php echo $order_status; ?></h1>
        <p>Эта заявка была отменена или ее время истекло.</p>

        <?php endif; ?>

    </div>
</div>

<?php get_footer(); ?>