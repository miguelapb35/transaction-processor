<?php

namespace App\Tests;

use App\Account;
use App\CommissionCalculator;
use App\TransactionManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CommissionCalculatorTest extends TestCase
{
    /** @var \ReflectionMethod */
    private $calculateCommissionFunc;
    /** @var CommissionCalculator */
    private $calculator;
    /** @var array Configuration fixture */
    private $config = [
        'commission' => [
            'cash_in' => [
                'natural' => [ // private person
                    'amount' => 3.4, // % - commission rate
                    'max' => 5 // in EUR
                ],
                'legal' => [ // business entity
                    'amount' => '5.78', // % - commission rate
                    'max' => 5 // in EUR
                ],
            ],
            'cash_out' => [
                'natural' => [ // private person
                    'amount' => '2.12', // % - commission rate
                    'min' => 0 // in EUR - minimum charge
                ],
                'legal' => [ // business entity
                    'amount' => 3, // % - commission rate
                    'min' => 0.5 // in EUR - minimum charge
                ],
            ]
        ]
    ];

    protected function setUp()
    {
        parent::setUp();
        $this->calculator = new CommissionCalculator($this->config);
        $ref = new \ReflectionObject($this->calculator);

        // make method calculateCommission() public as it's important to unit test
        $this->calculateCommissionFunc = $ref->getMethod('calculateCommission');
        $this->calculateCommissionFunc->setAccessible(true);
    }

    /**
     * Test private method CommissionCalculator::calculatePercent() indirectly
     */
    public function testCalculatePercent()
    {
        $calculated = $this->calculateCommissionFunc->invoke(
            $this->calculator,
            23,
            TransactionManager::CASH_IN,
            Account::PRIVATE
        );
        $this->assertSame(0.78, round($calculated, 2));
    }

    // Method "calculate" related tests for "cash in"

    public function testCalculateCashInPrivate()
    {
        $commission = $this->calculateCommissionFunc->invoke($this->calculator, 100.23, 'cash_in', 'natural');
        $this->assertEquals(3.41, round($commission, 2));
    }

    public function testCalculateCashInBusiness()
    {
        $commission = $this->calculateCommissionFunc->invoke(
            $this->calculator,
            121.236,
            TransactionManager::CASH_IN,
            Account::BUSINESS
        );
        $this->assertEquals(7.01, round($commission, 2));
    }

    // Method "calculate" related tests for "cash out"

    public function testCalculateCashOutPrivate()
    {
        $commission = $this->calculateCommissionFunc->invoke($this->calculator, 50.298, 'cash_out', 'natural');
        $this->assertEquals(1.07, round($commission, 2));
    }

    public function testCalculateCashOutBusiness()
    {
        $commission = $this->calculateCommissionFunc->invoke($this->calculator, 23, 'cash_out', 'legal');
        $this->assertEquals(0.69, $commission);
    }

    // Method "cashIn" related tests

    /**
     * Test that calculateCommission() is called within cashIn()
     */
    public function testCashInCalculateCommissionCalled()
    {
        /** @var CommissionCalculator|MockObject $calculator */
        $calculator = $this->getMockBuilder(CommissionCalculator::class)
            ->disableOriginalConstructor()
            ->setMethods(['calculateCommission'])
            ->getMock();

        $calculator->expects($this->once())->method('calculateCommission');
        $calculator->cashOut(23, 'natural');
    }

    public function testCashInPrivate()
    {
        // let the amount be big enough so that the commission surpasses the maximum defined in config
        $commission = $this->calculator->cashIn(20000, 'natural');
        $this->assertEquals(5, $commission); // commission is supposed to be greater than 5, but returned the max = 5
    }

    public function testCashInBusiness()
    {
        // let the amount be big enough so that the commission surpasses the maximum defined in config
        $commission = $this->calculator->cashIn(20000, 'legal');
        $this->assertEquals(5, $commission); // commission is supposed to be greater than 5, but returned the max = 5
    }

    // Method "cashOut" related tests

    public function testCashOutPrivate()
    {
        // let the amount be big enough so that the commission surpasses the maximum defined in config (above)
        $commission = $this->calculator->cashOut(10, 'natural');

        // commission is as is because in config we have min = 0 for natural persons
        $this->assertEquals(0.21, round($commission, 2));
    }

    public function testCashOutBusiness()
    {
        // let the amount be big enough so that the commission surpasses the maximum defined in config
        $commission = $this->calculator->cashOut(10, 'legal');

        // commission should the minimum of 0.5 (see config above)
        $this->assertEquals(0.5, $commission);
    }
}
