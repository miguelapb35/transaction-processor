<?php

namespace App\Tests\Promotion;

use App\CurrencyConverter;
use App\Promotion\Account;
use App\Promotion\CommissionCalculator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CommissionCalculatorTest extends TestCase
{
    /** @var CurrencyConverter|MockObject */
    private $converter;
    /** @var CommissionCalculator|MockObject */
    private $calculatorMock;
    /** @var CommissionCalculator */
    private $calculator;
    /** @var Account|MockObject */
    private $account;
    /** @var array Configuration fixture */
    private $config = [
        'commission' => [
            'cash_in' => [
                'private' => 3.4,
                'business' => '5.78'
            ],
            'cash_out' => [
                'private' => '2.12',
                'business' => 3
            ],
        ],
        'discounts' => [
            'free_weekly_limit' => [
                'max_transactions' => 3,
                'max_amount' => 1000,
            ]
        ],
        'currency' => [
            'exchange_rates' => [
                'default_cur' => 'EUR',
                'EUR' => [
                    'USD' => 1.1497,
                    'JPY' => 129.53,
                ],
                'precision' => [
                    'EUR' => 2,
                    'USD' => 2,
                    'JPY' => 0,
                ]
            ],
        ],
    ];

    protected function setUp()
    {
        parent::setUp();

        $this->account = $this->createMock(Account::class);
        $this->converter = $this->createMock(CurrencyConverter::class);
        $this->calculator = new CommissionCalculator($this->config, $this->converter);
        $this->calculatorMock = $this->getMockBuilder(CommissionCalculator::class)
            ->setConstructorArgs([$this->config, $this->converter])
            ->setMethods(['isQualifiedForPromotion', 'cashOut', 'chargeableAmount'])
            ->getMock();
    }

    // Method chargeableAmount() related

    /**
     * Test transactions are below the 1000 limit
     */
    public function testChargeableAmountZero()
    {
        $this->converter->method('convert')->willReturn(800);
        $chargeable = $this->calculator->chargeableAmount($this->account, 'eur');
        $this->assertEquals(0, $chargeable);
    }

    /**
     * Test transactions are below equal to the 1000 limit
     */
    public function testChargeableAmountEvenZero()
    {
        $this->converter->method('convert')->willReturn(1000);
        $chargeable = $this->calculator->chargeableAmount($this->account, 'eur');
        $this->assertEquals(0, $chargeable);
    }

    /**
     * Test transactions are below or equal to the 1000 limit
     */
    public function testChargeableAmountPositive()
    {
        $this->converter->method('revert')->willReturn(100);
        $this->account->method('getWeeklyTransactionsAmount')->willReturn(1100);
        $chargeable = $this->calculator->chargeableAmount($this->account, 'eur');
        $this->assertEquals(100, $chargeable);
    }

    /**
     * Test that method CurrencyConverter::revert() is called with the chargeable amount
     */
    public function testCurrencyConverterRevertCalled()
    {
        $this->account->method('getWeeklyTransactionsAmount')->willReturn(1100);
        $this->converter->expects($this->once())->method('revert')->with('EUR', 'eur', 100);
        $this->calculator->chargeableAmount($this->account, 'eur');
    }

    /**
     * Test that method Account::setPromotionUsed() is called with argument "true"
     */
    public function testAccountSetPromotionUsedCalled()
    {
        $this->account->method('getWeeklyTransactionsAmount')->willReturn(1100);
        $this->account->expects($this->once())->method('setPromotionUsed')->with(true);
        $this->calculator->chargeableAmount($this->account, 'eur');
    }

    // Method isQualifiedForPromotion() related

    /**
     * Test account type is not adequate
     */
    public function testIsQualifiedForPromotionFalse()
    {
        $this->account->method('getType')->willReturn('legal');
        $isQualified = $this->calculator->isQualifiedForPromotion($this->account, 'cash_out');
        $this->assertFalse($isQualified);
    }

    /**
     * Test transaction type is not adequate
     */
    public function testIsQualifiedForPromotionFalse2()
    {
        $this->account->method('getType')->willReturn('natural');
        $isQualified = $this->calculator->isQualifiedForPromotion($this->account, 'cash_in');
        $this->assertFalse($isQualified);
    }

    /**
     * Test transaction count is too height (>3)
     */
    public function testIsQualifiedForPromotionFalse3()
    {
        $this->account->method('getType')->willReturn('natural');
        $this->account->method('getWeeklyTransactionsCount')->willReturn(4);
        $isQualified = $this->calculator->isQualifiedForPromotion($this->account, 'cash_out');
        $this->assertFalse($isQualified);
    }

    /**
     * Test that the promotion has already been used
     */
    public function testPromotionAlreadyUsed()
    {
        $this->account->method('getType')->willReturn('natural');
        $this->account->method('isPromotionUsed')->willReturn(true);

        $isQualified = $this->calculator->isQualifiedForPromotion($this->account, 'cash_out');
        $this->assertFalse($isQualified);
    }

    /**
     * Test transaction amount == max allowed
     */
    public function testIsQualifiedForPromotion()
    {
        $this->account->method('getType')->willReturn('natural');
        $this->account->method('getWeeklyTransactionsCount')->willReturn(3);
        $isQualified = $this->calculator->isQualifiedForPromotion($this->account, 'cash_out');
        $this->assertTrue($isQualified);
    }

    /**
     * Test transaction amount < max allowed
     */
    public function testIsQualifiedForPromotion2()
    {
        $this->account->method('getType')->willReturn('natural');
        $this->account->method('getWeeklyTransactionsCount')->willReturn(2);
        $isQualified = $this->calculator->isQualifiedForPromotion($this->account, 'cash_out');
        $this->assertTrue($isQualified);
    }

    // Method cashOutWithPromo() related

    public function testIsQualifiedForPromotionCalled()
    {
        $this->calculatorMock->expects($this->once())->method('isQualifiedForPromotion')
            ->with($this->account, 'cash_out')->willReturn(false);

        $this->calculatorMock->cashOutWithPromo($this->account, [
            'trans_type' => 'cash_out',
            'amount' => 1
        ]);
    }

    public function testChargeableAmountCalled()
    {
        $this->calculatorMock->expects($this->once())->method('isQualifiedForPromotion')
            ->with($this->account, 'cash_out')->willReturn(true);

        $this->calculatorMock->expects($this->once())->method('chargeableAmount')
            ->with($this->account);

        $this->calculatorMock->cashOutWithPromo($this->account, [
            'trans_type' => 'cash_out',
            'amount' => 1,
            'cur' => 'eur'
        ]);
    }

    public function testCashOutCalled()
    {
        $this->calculatorMock->expects($this->once())->method('cashOut');

        $this->calculatorMock->cashOutWithPromo($this->account, [
            'trans_type' => 'cash_out',
            'amount' => 1
        ]);
    }
}
