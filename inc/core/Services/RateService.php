<?php
namespace WpOBC\Services;

class RateService {
    private $wpdb;
    private $table;
    private $providers = [];

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table = $wpdb->prefix . 'exchange_rates_cache';
    }

    public function addProvider(\WpOBC\Interfaces\ExchangeProviderInterface $provider) {
        $this->providers[] = $provider;
    }

    /**
     * Главный метод: находит устаревшие монеты и обновляет их
     * @param int $limit Сколько монет обновить за один запуск крона
     */
    public function updateStaleRates(int $limit = 5) {
        // 1. Получаем список всех АКТИВНЫХ монет из ACF
        // Это нужно, чтобы знать, какие ID вообще существуют и включены
        $active_coins = $this->getActiveCoinsIds();
        
        if (empty($active_coins)) return;

        // 2. Ищем монеты, которые:
        // А) Либо вообще отсутствуют в БД (новые)
        // Б) Либо протухли (updated_at < 2-3 минут назад)
        // В) Либо имеют статус FAIL (нужно попробовать снова)
        
        $candidates = [];

        foreach ($active_coins as $coin_id => $ticker) {
            // Проверяем кэш для этой монеты
            $row = $this->wpdb->get_row($this->wpdb->prepare(
                "SELECT updated_at, status FROM {$this->table} WHERE coin_id = %d LIMIT 1", 
                $coin_id
            ));

            if (!$row) {
                // Нет в базе - срочно обновить
                $candidates[$coin_id] = $ticker;
            } else {
                // Есть в базе. Проверяем свежесть.
                $seconds_ago = time() - strtotime($row->updated_at);
                
                // Если статус FAIL - пробуем раз в 3 минуты
                // Если статус OK - обновляем раз в 2 минуты
                $threshold = ($row->status === 'FAIL') ? 180 : 120;

                if ($seconds_ago > $threshold) {
                    $candidates[$coin_id] = $ticker;
                }
            }
        }

        // 3. Обрезаем массив до лимита (чтобы не убить сервер)
        $coins_to_update = array_slice($candidates, 0, $limit, true);

        if (empty($coins_to_update)) {
            // Все свежее, отдыхаем
            return;
        }

        // 4. Проходим по списку и обновляем
        foreach ($coins_to_update as $coin_id => $ticker) {
            $this->fetchAndSave($coin_id, $ticker);
            // Пауза между запросами к API (вежливость)
            usleep(500000); // 0.5 сек
        }
    }

    private function fetchAndSave($coin_id, $ticker) {
        $best_rate = 0.0;
        $best_provider_name = 'None';
        $status = 'FAIL';

        // Перебираем провайдеров
        foreach ($this->providers as $provider) {
            if (!$provider->isAvailable()) continue;

            // Запрашиваем курс к USDTTRC20 (как у тебя в логике)
            $rate = $provider->getRate($ticker, 'usdttrc20', 1.0);

            if ($rate && $rate > 0) {
                // Если нашли курс лучше, чем предыдущий - берем его
                if ($rate > $best_rate) {
                    $best_rate = $rate;
                    $best_provider_name = $provider->getName();
                    $status = 'OK';
                }
                
                // МОЖНО сохранять каждый ответ провайдера отдельно, 
                // если мы хотим видеть статистику по каждому (как у тебя было в UNIQUE KEY).
                // Для простоты здесь мы пишем лучший результат.
                // Если нужно как у тебя (много записей на 1 монету) - скажи, переделаю цикл.
            }
        }

        // Сохраняем ЛУЧШИЙ результат в БД
        // Примечание: В твоей старой таблице ключ был (coin_id, exchange_name). 
        // Здесь я пишу "Best" или имя провайдера.
        
        // Для совместимости с твоей таблицей, давай писать конкретного провайдера, который дал лучший курс.
        if ($status === 'OK') {
             $this->saveDb($coin_id, $best_provider_name, $best_rate, 'OK');
             // Также обновляем ACF поле для легаси совместимости
             update_field('curs_moneti', $best_rate, $coin_id);
        } else {
             // Если все провайдеры отказали
             $this->saveDb($coin_id, 'ALL', 0, 'FAIL');
        }
    }

    private function saveDb($coin_id, $exchange, $price, $status) {
        $sql = "INSERT INTO {$this->table} (coin_id, exchange_name, price, status, updated_at)
                VALUES (%d, %s, %f, %s, NOW())
                ON DUPLICATE KEY UPDATE price = VALUES(price), status = VALUES(status), updated_at = NOW()";
        
        $this->wpdb->query($this->wpdb->prepare($sql, $coin_id, $exchange, $price, $status));
    }

    private function getActiveCoinsIds() {
        $args = [
            'post_type' => 'monety',
            'posts_per_page' => -1,
            'fields' => 'ids', // Берем только ID
             // Можно добавить фильтр meta_query, если есть галочка "Активность"
        ];
        $ids = get_posts($args);
        
        $result = [];
        foreach ($ids as $id) {
            $ticker = get_field('code_coins', $id);
            if ($ticker) {
                $result[$id] = $ticker;
            }
        }
        return $result;
    }
}