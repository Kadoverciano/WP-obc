<?php
/* Template Name: Шаг Обмена */
get_header();

// 1. Получаем ID пары из URL
$from_id = isset($_GET['from']) ? intval($_GET['from']) : 0;
$to_id   = isset($_GET['to']) ? intval($_GET['to']) : 0;

if (!$from_id || !$to_id) {
    echo '<div class="container"><p>Ошибка: Пара обмена не выбрана.</p></div>';
    get_footer();
    exit;
}

$from_coin = get_post($from_id);
$to_coin   = get_post($to_id);
$to_is_card = (get_post_type($to_id) === 'payment_cards');

// Получаем regex для валидации кошелька (для UI)
$address_regex = '';
$address_example = '';
if (!$to_is_card) {
    $to_network = get_field('native_network', $to_id);
    if ($to_network && is_object($to_network)) {
        $address_regex = get_field('address_regex', $to_network->ID);
        $address_example = get_field('address_example', $to_network->ID);
    }
}
?>

<div class="content-page">
    <div class="container">
        <h1 class="title-h1">Обмен <?php echo esc_html($from_coin->post_title); ?> на
            <?php echo esc_html($to_coin->post_title); ?></h1>

        <form id="exchange-pair-form" class="exchange-form-step2">

            <div class="form-group">
                <label for="amount-send">Сумма (Отдаете):</label>
                <input type="number" id="amount-send" name="amount_send" step="0.00000001" placeholder="0.00" required>
            </div>

            <div class="form-group">
                <label for="amount-receive">Сумма (Получаете):</label>
                <div class="input-wrapper">
                    <input type="text" id="amount-receive" name="amount_receive" readonly placeholder="Wait...">
                    <span id="calc-loader"
                        style="display:none; position:absolute; right:10px; top:35%; font-size:12px; color:#666;">⏳</span>
                </div>
            </div>

            <div class="form-group">
                <label for="contact-info">Контакт для связи (Email или Telegram):</label>
                <input type="text" id="contact-info" name="contact_info" required>
            </div>

            <div class="form-group">
                <label for="receiver-address">
                    <?php echo $to_is_card ? 'На счет (Номер вашей карты):' : 'На счет (Ваш ' . esc_html($to_coin->post_title) . ' кошелек):'; ?>
                </label>
                <input type="text" id="receiver-address" name="receiver_address" required>
                <div id="wallet-error" style="color:red; font-size: 12px; margin-top: 5px;"></div>
                <?php if ($address_example): ?>
                <small style="color:#888;">Пример: <?php echo esc_html($address_example); ?></small>
                <?php endif; ?>
            </div>

            <input type="hidden" name="from_id" id="input-from-id" value="<?php echo $from_id; ?>">
            <input type="hidden" name="to_id" id="input-to-id" value="<?php echo $to_id; ?>">
            <input type="hidden" name="action" value="create_order_ajax">
            <?php wp_nonce_field('create_order_nonce', 'order_nonce'); ?>

            <div id="form-error" style="color:red; margin-bottom: 15px;"></div>
            <button type="submit" id="submit-order-btn">Создать заявку</button>
        </form>

    </div>
</div>

<script>
jQuery(document).ready(function($) {
    const fromId = $('#input-from-id').val();
    const amountSendInput = $('#amount-send');
    const amountReceiveInput = $('#amount-receive');
    const loader = $('#calc-loader');

    // Валидация кошелька
    const validationRegex = <?php echo json_encode($address_regex); ?>;
    const walletErrorEl = $('#wallet-error');

    // Функция задержки (Debounce)
    function debounce(func, wait) {
        let timeout;
        return function() {
            const context = this,
                args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(context, args), wait);
        };
    }

    // Функция запроса к API
    const fetchRate = debounce(function() {
        let amount = parseFloat(amountSendInput.val());

        if (!amount || amount <= 0) {
            amountReceiveInput.val('');
            return;
        }

        // Показываем лоадер
        loader.show();
        amountReceiveInput.css('opacity', '0.5');

        // ЗАПРОС К НАШЕМУ НОВОМУ API
        $.ajax({
            url: '/wp-json/obc/v1/rate',
            method: 'GET',
            data: {
                from_id: fromId,
                amount: amount
            },
            success: function(response) {
                loader.hide();
                amountReceiveInput.css('opacity', '1');

                if (response.success) {
                    // Вставляем полученное значение
                    amountReceiveInput.val(response.amount_out);
                }
            },
            error: function(err) {
                loader.hide();
                amountReceiveInput.css('opacity', '1');
                console.error('API Error:', err);
                amountReceiveInput.val('Ошибка курса');
            }
        });

    }, 500); // Ждем 500мс после ввода

    // Слушаем ввод
    amountSendInput.on('input', fetchRate);

    // Валидация кошелька (UI)
    $('#receiver-address').on('blur', function() {
        if (!validationRegex) return;
        let val = $(this).val();
        // Убираем слеши из начала и конца строки regex, если они пришли из PHP
        let cleanRegex = validationRegex.replace(/^\/|\/$/g, '');
        let re = new RegExp(cleanRegex);

        if (!re.test(val)) {
            walletErrorEl.text('Неверный формат адреса');
        } else {
            walletErrorEl.text('');
        }
    });

    // Обработка отправки формы (пока заглушка, сделаем на след. шаге)
    $('#exchange-pair-form').on('submit', function(e) {
        // e.preventDefault();
        // Логику создания ордера добавим позже, пока пусть просто работает калькулятор
    });
});
</script>

<?php get_footer(); ?>