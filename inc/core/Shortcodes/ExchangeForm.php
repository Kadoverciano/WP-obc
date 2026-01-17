<?php
namespace WpOBC\Shortcodes;

class ExchangeForm {

    public static function init() {
        add_shortcode('crypto_exchange_form', [self::class, 'render']);
    }

    public static function render() {
        // 1. Получаем все монеты
        $coins = get_posts([
            'post_type' => 'monety',
            'numberposts' => -1,
            'orderby' => 'menu_order',
            'order' => 'ASC',
            'post_status' => 'publish'
        ]);

        // 2. Если монет нет, выводим заглушку
        if (empty($coins)) {
            return '<p>Список монет пуст. Добавьте монеты в админке.</p>';
        }

        // 3. Формируем HTML
        ob_start();
        ?>
<div class="exchange-widget-wrapper">
    <div class="exchange-columns">

        <div class="exchange-col">
            <h3 class="exchange-col-title">Отдаете</h3>
            <div class="coin-list" id="list-give">
                <?php foreach ($coins as $index => $coin): 
                            $ticker = get_field('code_coins', $coin->ID); // Получаем тикер (BTC, ETH)
                            $icon = get_the_post_thumbnail_url($coin->ID, 'thumbnail');
                        ?>
                <div class="coin-item <?php echo $index === 0 ? 'active' : ''; ?>" data-id="<?php echo $coin->ID; ?>"
                    data-ticker="<?php echo esc_attr($ticker); ?>">

                    <?php if ($icon): ?>
                    <img src="<?php echo esc_url($icon); ?>" class="coin-icon" alt="<?php echo esc_attr($ticker); ?>">
                    <?php endif; ?>

                    <div class="coin-info">
                        <span class="coin-ticker"><?php echo esc_html($ticker); ?></span>
                        <span class="coin-name"><?php echo esc_html($coin->post_title); ?></span>
                    </div>
                    <div class="coin-check">✔</div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="exchange-arrow">
            ➔
        </div>

        <div class="exchange-col">
            <h3 class="exchange-col-title">Получаете</h3>
            <div class="coin-list" id="list-get">
                <?php foreach ($coins as $index => $coin): 
                             // Выбираем второй элемент активным по умолчанию, чтобы не совпадал с первым
                             $isActive = ($index === 1) ? 'active' : '';
                             $ticker = get_field('code_coins', $coin->ID);
                             $icon = get_the_post_thumbnail_url($coin->ID, 'thumbnail');
                        ?>
                <div class="coin-item <?php echo $isActive; ?>" data-id="<?php echo $coin->ID; ?>"
                    data-ticker="<?php echo esc_attr($ticker); ?>">

                    <?php if ($icon): ?>
                    <img src="<?php echo esc_url($icon); ?>" class="coin-icon" alt="<?php echo esc_attr($ticker); ?>">
                    <?php endif; ?>

                    <div class="coin-info">
                        <span class="coin-ticker"><?php echo esc_html($ticker); ?></span>
                        <span class="coin-name"><?php echo esc_html($coin->post_title); ?></span>
                    </div>
                    <div class="coin-check">✔</div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>

    <div class="exchange-submit-area">
        <button id="go-exchange-btn" class="btn-main">Обменять сейчас</button>
        <div id="exchange-error" style="color:red; margin-top:10px; display:none;"></div>
    </div>
</div>

<style>
/* Быстрые стили для виджета (можно перенести в style.css) */
.exchange-widget-wrapper {
    max-width: 900px;
    margin: 0 auto;
    background: #fff;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
}

.exchange-columns {
    display: flex;
    gap: 20px;
    align-items: center;
    justify-content: space-between;
}

.exchange-col {
    flex: 1;
    border: 1px solid #eee;
    border-radius: 10px;
    padding: 15px;
    background: #f9f9f9;
}

.exchange-col-title {
    font-size: 18px;
    margin-bottom: 15px;
    font-weight: bold;
    text-align: center;
}

.exchange-arrow {
    font-size: 30px;
    color: #0aa0d2;
}

.coin-list {
    max-height: 400px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.coin-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    cursor: pointer;
    transition: 0.2s;
}

.coin-item:hover {
    border-color: #0aa0d2;
}

.coin-item.active {
    background: #0aa0d2;
    border-color: #0aa0d2;
    color: #fff;
}

.coin-item.active .coin-name {
    color: #e0f7ff;
}

.coin-icon {
    width: 32px;
    height: 32px;
    object-fit: contain;
}

.coin-info {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.coin-ticker {
    font-weight: bold;
    text-transform: uppercase;
}

.coin-name {
    font-size: 12px;
    color: #666;
}

.coin-check {
    opacity: 0;
    font-weight: bold;
}

.coin-item.active .coin-check {
    opacity: 1;
}

.exchange-submit-area {
    text-align: center;
    margin-top: 30px;
}

.btn-main {
    background: linear-gradient(135deg, #0aa0d2 0%, #0073aa 100%);
    color: #fff;
    padding: 15px 40px;
    border: none;
    border-radius: 50px;
    font-size: 18px;
    font-weight: bold;
    cursor: pointer;
    transition: 0.3s;
}

.btn-main:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(10, 160, 210, 0.4);
}

@media (max-width: 768px) {
    .exchange-columns {
        flex-direction: column;
    }

    .exchange-arrow {
        transform: rotate(90deg);
        margin: 10px 0;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Логика выбора монет
    $('.coin-item').on('click', function() {
        // Убираем активный класс у соседей в этой же колонке
        $(this).siblings().removeClass('active');
        // Добавляем себе
        $(this).addClass('active');
    });

    // Логика кнопки "Обменять"
    $('#go-exchange-btn').on('click', function() {
        const fromId = $('#list-give .coin-item.active').data('id');
        const toId = $('#list-get .coin-item.active').data('id');
        const errorDiv = $('#exchange-error');

        errorDiv.hide();

        if (!fromId || !toId) {
            errorDiv.text('Пожалуйста, выберите валюты в обеих колонках.').show();
            return;
        }

        if (fromId === toId) {
            errorDiv.text('Нельзя обменять валюту саму на себя. Выберите разные.').show();
            return;
        }

        // !!! ВАЖНО: Укажи здесь правильный slug страницы, которую мы делали в прошлом шаге
        // Если страница называется "obmen", то путь будет /obmen/
        // Скрипт попытается найти её.
        const baseUrl = '/exchange-step/'; // <-- ПРОВЕРЬ ЭТОТ URL НА САЙТЕ

        // Редирект
        window.location.href = baseUrl + '?from=' + fromId + '&to=' + toId;
    });
});
</script>
<?php
        return ob_get_clean();
    }
}