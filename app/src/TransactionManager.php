<?php
declare(strict_types=1);
namespace App;

class TransactionManager
{
    const CASH_IN = 'cash_in';
    const CASH_OUT = 'cash_out';

    protected $accounts = [];
    protected $calculator;
    protected $config = [];

    public function __construct(CommissionCalculator $calculator, array $config)
    {
        $this->calculator = $calculator;
        $this->config = $config;
    }

    public function run(array $row): float
    {
        $commission = $this->runTransaction($row);
        return Utils::ceil($commission, $this->config['currency']['precision'][$row['cur']]);
    }

    /**
     * @param array $row
     * @return float The commission charged for the transaction
     */
    public function runTransaction(array $row): float
    {
        if (empty($row['acc_id']) || empty($row['acc_type']) || empty($row['amount']) || empty($row['trans_type'])) {
            throw new \InvalidArgumentException();
        }

        $account = $this->getOrCreateAccount($row['acc_id'], $row['acc_type']);
        if ($row['trans_type'] === self::CASH_IN) {
            return $this->calculator->cashIn($row['amount'], $account->getType());
        }
        return $this->calculator->cashOut($row['amount'], $account->getType());
    }

    protected function getOrCreateAccount(int $userId, string $accountType): Account
    {
        $account = null;
        $accountId = $userId.'_'.$accountType;
        if (!array_key_exists($accountId, $this->accounts)) {
            $account = $this->createAccount($accountId, $accountType);
        } else {
            $account = $this->accounts[$accountId];
        }
        return $account;
    }

    protected function createAccount(string $accId, string $accType): Account
    {
        $account = new Account($accId, $accType);
        $this->addAccount($account);
        return $account;
    }

    protected function addAccount(Account $account)
    {
        $this->accounts[$account->getId()] = $account;
    }

    public function getAccounts(): array
    {
        return $this->accounts;
    }
}
