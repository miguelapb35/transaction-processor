<?php
declare(strict_types=1);
namespace App\Promotion;

use App\CommissionCalculator as NormalCalculator;
use App\CurrencyConverter;

class CommissionCalculator extends NormalCalculator
{
    private $currencyConverter;
    private $exchangeRates;
    private $discounts = [];

    public function __construct(array $config, CurrencyConverter $currencyConverter)
    {
        parent::__construct($config);
        $this->discounts = $config['discounts']['free_weekly_limit'];
        $this->exchangeRates = $config['currency']['exchange_rates'];
        $this->currencyConverter = $currencyConverter;
    }

    public function cashOutWithPromo(Account $account, array $row): float
    {
        if ($this->isQualifiedForPromotion($account, $row['trans_type'])) {
            $chargeableAmount = $this->chargeableAmount($account, $row['cur']);
        } else {
            $chargeableAmount = $row['amount'];
        }
        return $this->cashOut($chargeableAmount, $account->getType());
    }

    /**
     * Checks if the transaction is qualified for the promotion - is the transactions count within the limit
     * @param Account $account
     * @param string $transactionType
     * @return boolean
     */
    public function isQualifiedForPromotion(Account $account, string $transactionType): bool
    {
        // promotion only valid for cash out for natural persons
        if ($account->getType() !== Account::PRIVATE ||
            $transactionType !== TransactionManager::CASH_OUT || $account->isPromotionUsed()) {
            return false;
        }
        // the transactions count is too high
        return ($account->getWeeklyTransactionsCount() <= $this->discounts['max_transactions']);
    }

    /**
     * Return the amount subject to charge (over 1000)
     * @param Account $account
     * @param string $currency
     * @return float
     */
    public function chargeableAmount(Account $account, string $currency): float
    {
        $accumulated = $account->getWeeklyTransactionsAmount();
        $defaultCurrency = $this->exchangeRates['default_cur'];

        $diff = $this->discounts['max_amount'] - $accumulated;
        $chargeableConverted = $diff < 0 ? abs($diff) : 0;

        // notify the account if the promotion has been consumed
        if ($diff === 0 || $chargeableConverted > 0) {
            $account->setPromotionUsed(true);
        }

        // revert back to the transaction currency
        return $chargeableConverted > 0 ?
            $this->currencyConverter->revert($defaultCurrency, $currency, $chargeableConverted) : 0;
    }
}
