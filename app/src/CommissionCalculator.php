<?php
declare(strict_types=1);
namespace App;

class CommissionCalculator
{
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Commission for "Cash in" operation (no more than the maximum in config)
     * @param float $amount
     * @param string $accountType "private" or "legal"
     * @return float
     */
    public function cashIn(float $amount, string $accountType): float
    {
        $operation = TransactionManager::CASH_IN;

        $commission = $this->calculateCommission($amount, $operation, $accountType);

        $maxCommission = (float) $this->config['commission'][$operation][$accountType]['max'];
        if ($commission > $maxCommission) {
            return $maxCommission;
        }
        return $commission;
    }

    /**
     * A general "Cash out" case
     * Commission for "Cash out" operation (no less than the minimum in config)
     * @param float $amount
     * @param string $accountType "private" or "legal"
     * @return float
     */
    public function cashOut(float $amount, string $accountType): float
    {
        $operation = TransactionManager::CASH_OUT;

        $commission = $this->calculateCommission($amount, $operation, $accountType);
        $minCommission = (float) $this->config['commission'][$operation][$accountType]['min'];

        if ($commission < $minCommission) {
            return $minCommission;
        }
        return $commission;
    }

    protected function calculateCommission(float $amount, string $operation, string $type): float
    {
        $commission = (float) $this->config['commission'][$operation][$type]['amount'];
        return $this->calculatePercent($amount, $commission);
    }

    private function calculatePercent(float $amount, float $percent): float
    {
        return $amount / 100 * $percent;
    }
}
