<?php
declare(strict_types=1);
namespace App\Promotion;

use App\Account as NormalAccount;
use App\CurrencyConverter;

/**
 * @property CommissionCalculator  $calculator
 */
class TransactionManager extends \App\TransactionManager
{
    const WEEK_ID_FORMAT = 'Y W';
    private $currencyConverter;

    public function __construct(CommissionCalculator $calculator, CurrencyConverter $currencyConverter, array $config)
    {
        parent::__construct($calculator, $config);
        $this->currencyConverter = $currencyConverter;
    }

    /**
     * @param array $row
     * @return float The commission charged for the transaction
     */
    public function runTransaction(array $row): float
    {
        /** @var Account $account */
        $account = $this->getOrCreateAccount($row['acc_id'], $row['acc_type']);

        if ($row['trans_type'] === self::CASH_OUT) {
            // add the transaction to the weekly cash out transactions for this account
            $this->addTransactionToAccount($account, $row);

            return $this->calculator->cashOutWithPromo($account, $row);
        }
        return $this->calculator->cashIn($row['amount'], $row['acc_type']);
    }

    /**
     * Override the parent method in order to create account of type "promotion"
     * @param string $accId
     * @param string $accType
     * @return NormalAccount
     */
    protected function createAccount(string $accId, string $accType): NormalAccount
    {
        $account = new Account($accId, $accType);
        $this->addAccount($account);
        return $account;
    }

    public function addTransactionToAccount(Account $account, array $row): void
    {
        /** @var \DateTime $date */
        $date = $row['date'];

        $weekToken =  $this->calculateWeekToken($date);
        // if the week is not the current week, then start a new week
        if ($weekToken !== $account->getWeekToken()) {
            $account->setWeekToken($weekToken);
        }

        $defaultCurrency = $this->config['currency']['exchange_rates']['default_cur'];
        $amountConverted = $this->currencyConverter->convert($row['cur'], $defaultCurrency, $row['amount']);
        $account->addTransactionAmount($amountConverted);
    }

    /**
     * Creates unique Week token dealing with the incomplete weeks problem at the end and the start of the year.
     * Produces token like '2018 40'
     * @param \DateTime $date
     * @return string
     */
    public function calculateWeekToken(\DateTime $date): string
    {
        if ((int)$date->format('m') === 12 && (int)$date->format('W') === 1) {
            return ((int)$date->format('Y')+1).' '.$date->format('W');
        } elseif ((int)$date->format('m') === 1 && (int)$date->format('W') > 1) {
            return ((int)$date->format('Y')-1).' '.$date->format('W');
        }

        return $date->format(self::WEEK_ID_FORMAT);
    }
}
