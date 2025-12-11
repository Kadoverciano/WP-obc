<?php
namespace WpOBC\Services;

class CalculationService {
    private $wpdb;
    private $tableName;

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->tableName = $wpdb->prefix . 'exchange_rates_cache';
    }

    /**
     * Получить итоговый курс для клиента (с наценкой)
     * @param int $coinId ID монеты (поста)
     * @return float
     */
    public function getRateForCoin(int $coinId): float {
        // 1. Получаем чистый курс из нашей кэш-таблицы
        // Ищем курс к USDT (базовый)
        $query = $this->wpdb->prepare(
            "SELECT price FROM {$this->tableName} WHERE coin_id = %d AND status = 'OK' ORDER BY updated_at DESC LIMIT 1",
            $coinId
        );
        
        $baseRate = (float) $this->wpdb->get_var($query);

        if (!$baseRate || $baseRate <= 0) {
            // Если курса нет в кэше, пробуем взять старый из ACF (как фолбэк)
            $baseRate = (float) get_field('curs_moneti', $coinId);
        }

        if ($baseRate <= 0) return 0.0;

        // 2. Получаем наценку из настроек темы (ACF Options)
        $markupPercent = (float) get_field('proczent_komissii', 'option'); // например, 5
        
        // 3. Считаем множитель
        // Если мы ОТДАЕМ клиенту крипту, мы должны выдать МЕНЬШЕ, чем получили.
        // Формула: Rate * (1 - (Percent / 100))
        // Пример: Курс 100, Комиссия 5%. Клиент получит по курсу 95.
        $multiplier = 1 - ($markupPercent / 100);

        return $baseRate * $multiplier;
    }

    /**
     * Валидация кошелька (перенос логики с фронта на бэк)
     */
    public function validateAddress(string $address, int $networkId): bool {
        $regex = get_field('address_regex', $networkId);
        if (empty($regex)) return true; // Если регулярки нет, считаем валидным
        
        // Удаляем слеши, если они есть в начале/конце строки из ACF
        $regex = trim($regex, '/'); 
        
        return (bool) preg_match('/' . $regex . '/', $address);
    }
}