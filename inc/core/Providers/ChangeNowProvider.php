<?php
namespace WpOBC\Providers;

use WpOBC\Interfaces\ExchangeProviderInterface;

class ChangeNowProvider implements ExchangeProviderInterface {
    private $apiUrl;
    private $apiKey;
    private $isActive;

    public function __construct() {
        // Получаем настройки через get_page_by_title (как у тебя было)
        $post = get_page_by_title('changenow', OBJECT, 'exchange');
        if ($post) {
            $this->apiUrl   = rtrim(get_field('api_url', $post->ID), '/');
            $this->apiKey   = get_field('api_key', $post->ID) ?: get_field('api_secret', $post->ID);
            $this->isActive = get_field('exchange_status', $post->ID);
        }
    }

    public function getName(): string {
        return 'ChangeNOW';
    }

    public function isAvailable(): bool {
        return $this->isActive && !empty($this->apiKey) && !empty($this->apiUrl);
    }

    public function getRate(string $from, string $to, float $amount): ?float {
        if (!$this->isAvailable()) return null;

        $from = strtolower(trim($from));
        $to   = strtolower(trim($to));
        
        // Формируем запрос
        $endpoint = "{$this->apiUrl}/exchange-amount/{$amount}/{$from}_{$to}?api_key={$this->apiKey}";

        $response = wp_remote_get($endpoint, [
            'timeout' => 15,
            'sslverify' => false // Если нужно
        ]);

        if (is_wp_error($response)) {
            error_log('ChangeNOW Error: ' . $response->get_error_message());
            return null;
        }

        $code = wp_remote_retrieve_response_code($response);
        if ($code !== 200) {
            error_log("ChangeNOW HTTP Error: $code");
            return null;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        // ChangeNOW может вернуть estimatedAmount в корне JSON
        return isset($data['estimatedAmount']) ? (float)$data['estimatedAmount'] : null;
    }
}