<?php
/* Template Name: Шаг Обмена */
get_header();

// 1. Получаем ID пары из URL
$from_id = isset($_GET['from']) ? intval($_GET['from']) : 0;
$to_id   = isset($_GET['to']) ? intval($_GET['to']) : 0;

if (!$from_id || !$to_id || !get_post($from_id) || !get_post($to_id)) {
    echo '<div class="container"><p>Ошибка: Пара обмена не найдена.</p></div>';
    get_footer();
    exit;
}

$from_coin = get_post($from_id);
$to_coin   = get_post($to_id);
$to_is_card = (get_post_type($to_id) === 'payment_cards');

// 2. Получаем "ЧИСТЫЙ" курс
$base_rate = get_true_exchange_rate($from_id, $to_id);
if ($base_rate <= 0) {
    echo '<div class="container"><p>Ошибка: Курс для этой пары не настроен.</p></div>';
    get_footer();
    exit;
}

// 3. Получаем ТВОЮ НАЦЕНКУ (e.g., 5%)
$percent_markup = get_field('proczent_komissii', 'option'); // Твоя наценка %
$markup_multiplier = 1 - (floatval($percent_markup) / 100); // (1 + 5 / 100) = 1.05

// 4. Применяем наценку к курсу
$rate_with_markup = $base_rate * $markup_multiplier;

// 5. Получаем ФИКСИРОВАННУЮ КОМИССИЮ СЕТИ (если МЫ платим)
$network_fee_usd = 0;   // e.g., 1.5 (для TRC20)
$to_coin_usd_value = 0; // e.g., 1 (для USDT)
$address_regex = '';
$address_example = '';

if (!$to_is_card) { // Если МЫ отправляем крипту
    $to_network = get_field('native_network', $to_id);
    if ($to_network && is_object($to_network)) {
        $network_fee_usd = floatval(get_field('network_fee_usd', $to_network->ID));
        $to_coin_usd_value = floatval(get_field('curs_moneti', $to_id));
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
                <input type="number" id="amount-send" name="amount_send" step="0.000001" required>
            </div>

            <div class="form-group">
                <label for="amount-receive">Сумма (Получаете):</label>
                <input type="text" id="amount-receive" name="amount_receive" readonly>
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
                <div id="wallet-error" style="color:red; font-size: 12px;"></div>
                <?php if ($address_example): ?>
                <small>Пример: <?php echo $address_example; ?></small>
                <?php endif; ?>
            </div>

            <input type="hidden" name="from_id" value="<?php echo $from_id; ?>">
            <input type="hidden" name="to_id" value="<?php echo $to_id; ?>">
            <input type="hidden" name="action" value="create_order_ajax">
            <?php wp_nonce_field('create_order_nonce', 'order_nonce'); ?>

            <div id="form-error" style="color:red;"></div>
            <button type="submit" id="submit-order-btn">Продолжить</button>
        </form>

    </div>
</div>

<script>
jQuery(document).ready(function($) {

    // Передаем PHP переменные в JS
    var rateWithMarkup = <?php echo $rate_with_markup; ?>;
    var networkFeeUSD = <?php echo $network_fee_usd; ?>; // e.g., 1.5
    var toCoinUSDValue = <?php echo $to_coin_usd_value; ?>; // e.g., 1
    var validationRegex = <?php echo json_encode($address_regex); ?>;

    var walletErrorEl = $('#wallet-error');
    var formErrorEl = $('#form-error');
    var submitBtn = $('#submit-order-btn');

    // 1. Калькулятор (САМАЯ ВАЖНАЯ ЧАСТЬ)
    $('#amount-send').on('input keyup', function() {
        var sendAmount = parseFloat($(this).val());
        if (sendAmount > 0) {

            // 1. Считаем "грязную" сумму с твоей наценкой
            var receiveAmountGross = sendAmount * rateWithMarkup;

            var finalAmountNet = receiveAmountGross;

            // 2. Вычитаем ФИКСИРОВАННУЮ комиссию (если МЫ платим)
            if (networkFeeUSD > 0 && toCoinUSDValue > 0) {
                // Считаем комиссию в целевой валюте
                // (1.5$ / 1$ за USDT) = 1.5 USDT
                // (1.5$ / 60000$ за BTC) = 0.000025 BTC
                var feeInCrypto = networkFeeUSD / toCoinUSDValue;

                finalAmountNet = receiveAmountGross - feeInCrypto;
            }

            // 3. Показываем чистую сумму
            if (finalAmountNet < 0) finalAmountNet = 0;
            $('#amount-receive').val(finalAmountNet.toFixed(8));

        } else {
            $('#amount-receive').val(0);
        }
    });

    // 2. Валидатор кошелька (остается без изменений)
    function validateWallet() {
        if (!validationRegex) return true;
        var walletAddress = $('#receiver-address').val();
        var regex = new RegExp(validationRegex);
        if (regex.test(walletAddress)) {
            walletErrorEl.text('');
            return true;
        } else {
            walletErrorEl.text('Неверный формат адреса. Проверьте правильность ввода.');
            return false;
        }
    }
    $('#receiver-address').on('blur', validateWallet);

    // 3. Отправка формы (AJAX) (остается почти без изменений)
    $('#exchange-pair-form').on('submit', function(e) {
        e.preventDefault();
        formErrorEl.text('');

        if (!validateWallet()) {
            formErrorEl.text('Пожалуйста, введите корректный адрес кошелька.');
            return;
        }

        // Проверка, что сумма получения не 0
        if (parseFloat($('#amount-receive').val()) <= 0) {
            formErrorEl.text('Сумма слишком мала для покрытия комиссии сети.');
            return;
        }

        submitBtn.prop('disabled', true).text('Создание заявки...');

        $.ajax({
            type: 'POST',
            url: '<?php echo admin_url("admin-ajax.php"); ?>',
            data: $(this).serialize(), // Отправляем данные формы
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    window.location.href = response.data.order_url;
                } else {
                    formErrorEl.text('Ошибка: ' + response.data.message);
                    submitBtn.prop('disabled', false).text('Продолжить');
                }
            },
            error: function() {
                formErrorEl.text('Произошла ошибка сети. Попробуйте еще раз.');
                submitBtn.prop('disabled', false).text('Продолжить');
            }
        });
    });
});
</script>

<?php get_footer(); ?>