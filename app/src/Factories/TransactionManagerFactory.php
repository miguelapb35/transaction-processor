<?php
declare(strict_types=1);
namespace App\Factories;

use App\CommissionCalculator;
use App\CurrencyConverter;
use App\TransactionManager;
use App\Promotion\CommissionCalculator as PromoCommissionCalculator;
use App\Promotion\TransactionManager as PromoTransactionManager;

/**
 * Creates a TransactionManager class of different types
 */
class TransactionManagerFactory
{
    private static $types = ['normal', 'promotion'];

    public static function create(array $config, string $type): TransactionManager
    {
        if (!in_array($type, self::$types)) {
            throw new \RuntimeException('Invalid TransactionManager type');
        }

        if ($type === 'normal') {
            $calculator = new CommissionCalculator($config);
            return new TransactionManager($calculator, $config);
        }

        $currencyConverter = new CurrencyConverter($config);
        $calculator = new PromoCommissionCalculator($config, $currencyConverter);
        return new PromoTransactionManager($calculator, $currencyConverter, $config);
    }
}
