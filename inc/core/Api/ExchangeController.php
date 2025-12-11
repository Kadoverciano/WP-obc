<?php
namespace WpOBC\Api;

use WpOBC\Services\CalculationService;

class ExchangeController {
    
    public static function init() {
        add_action('rest_api_init', [self::class, 'registerRoutes']);
    }

    public static function registerRoutes() {
        register_rest_route('obc/v1', '/rate', [
            'methods' => 'GET',
            'callback' => [self::class, 'getRate'],
            'permission_callback' => '__return_true', // Публичный API
        ]);
    }

    /**
     * Endpoint: GET /wp-json/obc/v1/rate?from_id=123&amount=1
     */
    public static function getRate(\WP_REST_Request $request) {
        $fromId = (int) $request->get_param('from_id');
        $amount = (float) $request->get_param('amount');

        if (!$fromId) {
            return new \WP_Error('no_coin', 'Coin ID is required', ['status' => 400]);
        }

        $calculator = new CalculationService();
        $rate = $calculator->getRateForCoin($fromId);

        if ($rate <= 0) {
            return new \WP_Error('no_rate', 'Rate not available', ['status' => 404]);
        }

        $result = $amount * $rate;

        return rest_ensure_response([
            'success' => true,
            'coin_id' => $fromId,
            'rate' => $rate, // Курс уже с наценкой
            'amount_in' => $amount,
            'amount_out' => round($result, 8), // Округляем до 8 знаков
            'timestamp' => time()
        ]);
    }
}