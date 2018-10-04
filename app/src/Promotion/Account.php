<?php
declare(strict_types=1);
namespace App\Promotion;

use App\Account as NormalAccount;

class Account extends NormalAccount
{
    /** @var string The week that the transactions occurred in */
    private $weekToken;
    /** @var float The amount of "Cash Out" transaction converted in default currency (eur) */
    private $weeklyTransactionsAmount;
    /** @var int The count of "Cash Out" transaction for the given week */
    private $weeklyTransactionsCount = 0;
    /** @var bool If "TRUE" the promotion will not be applied to the transaction as it's been already used */
    private $promotionUsed = false;

    public function __construct(string $id, string $type)
    {
        parent::__construct($id, $type);
    }

    public function addTransactionAmount(float $amount): void
    {
        $this->weeklyTransactionsAmount += $amount;
        $this->weeklyTransactionsCount++;
    }

    public function getWeeklyTransactionsAmount(): float
    {
        return $this->weeklyTransactionsAmount;
    }

    public function getWeekToken():? string
    {
        return $this->weekToken;
    }

    /**
     * Start new week
     * @param string $weekToken
     */
    public function setWeekToken(string $weekToken): void
    {
        $this->weekToken = $weekToken;
        $this->weeklyTransactionsAmount = 0;
        $this->weeklyTransactionsCount = 0;
        $this->promotionUsed = false;
    }

    public function getWeeklyTransactionsCount(): int
    {
        return $this->weeklyTransactionsCount;
    }

    public function isPromotionUsed(): bool
    {
        return $this->promotionUsed;
    }

    public function setPromotionUsed(bool $promotionUsed): void
    {
        $this->promotionUsed = $promotionUsed;
    }
}
