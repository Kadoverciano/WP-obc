<?php
namespace WpOBC\Interfaces;

interface ExchangeProviderInterface {
    public function getName(): string;
    public function isAvailable(): bool;
    public function getRate(string $from, string $to, float $amount): ?float;
}