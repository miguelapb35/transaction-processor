<?php
declare(strict_types=1);
namespace App;

class CurrencyConverter
{
    private $exchangeRates;

    public function __construct(array $config)
    {
        $this->exchangeRates = $config['currency']['exchange_rates'];
    }

    public function convert(string $from, string $to, float $amount): float
    {
        if ($from === $to) {
            return $amount;
        }

        return $amount / $this->exchangeRates[$to][$from];
    }

    public function revert(string $from, string $to, float $amount): float
    {
        if ($from === $to) {
            return $amount;
        }

        return $amount * $this->exchangeRates[$from][$to];
    }
}
