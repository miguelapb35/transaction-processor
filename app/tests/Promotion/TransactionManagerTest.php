<?php

namespace App\Tests\Promotion;

use App\CurrencyConverter;
use App\Promotion\CommissionCalculator;
use App\Promotion\Account;
use App\Promotion\TransactionManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TransactionManagerTest extends TestCase
{
    /** @var CurrencyConverter|MockObject */
    private $converter;
    /** @var CommissionCalculator|MockObject */
    private $calculator;
    /** @var TransactionManager */
    private $manager;
    private $configFixture = [
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
    private $rowFixture = [];

    protected function setUp()
    {
        parent::setUp();

        $this->rowFixture = ['amount' => 1, 'date' => new \DateTime(), 'cur' => 'eur'];
        $this->calculator = $this->createMock(CommissionCalculator::class);
        $this->converter = $this->createMock(CurrencyConverter::class);
        $this->manager = new TransactionManager($this->calculator, $this->converter, $this->configFixture);
    }

    /**
     * Test testCreateAccount() indirectly as method is protected
     */
    public function testCreateAccount()
    {
        /** @var TransactionManager|MockObject $manager */
        $manager = $this->getMockBuilder(TransactionManager::class)
            ->setConstructorArgs([$this->calculator, $this->converter, $this->configFixture])
            ->setMethods(['addTransactionToAccount'])
            ->getMock();

        $manager->runTransaction([
            'acc_id' => 1, 'acc_type' => 'legal', 'amount' => 12, 'trans_type' => 'cash_in',
            'date' => new \DateTime()]);

        // make sure it adds an Account of the promo type
        $this->assertInstanceOf(Account::class, $manager->getAccounts()['1_legal']);
    }

     // Method runTransaction() related

    /**
     * Test that method addTransactionToAccount is not called for cash in transactions
     */
    public function testRunTransaction()
    {
        /** @var TransactionManager|MockObject $manager */
        $manager = $this->getMockBuilder(TransactionManager::class)
            ->setConstructorArgs([$this->calculator, $this->converter, $this->configFixture])
            ->setMethods(['addTransactionToAccount'])
            ->getMock();

        $manager->expects($this->exactly(0))->method('addTransactionToAccount');

        $manager->runTransaction([
            'acc_id' => 1, 'acc_type' => 'legal', 'amount' => 12, 'trans_type' => 'cash_in',
            'date' => new \DateTime()]);
    }

    /**
     * Test that method addTransactionToAccount is called for cash out transactions
     */
    public function testRunTransactionCashOut()
    {
        /** @var TransactionManager|MockObject $manager */
        $manager = $this->getMockBuilder(TransactionManager::class)
            ->setConstructorArgs([$this->calculator, $this->converter, $this->configFixture])
            ->setMethods(['addTransactionToAccount'])
            ->getMock();

        $manager->expects($this->once())->method('addTransactionToAccount');

        $manager->runTransaction([
            'acc_id' => 1, 'acc_type' => 'legal', 'amount' => 12, 'trans_type' => 'cash_out',
            'date' => new \DateTime()]);
    }

    /**
     * Test that method CommissionCalculator::cashOutWithPromo() is called
     */
    public function testRunTransaction2()
    {
        /** @var TransactionManager|MockObject $manager */
        $manager = $this->getMockBuilder(TransactionManager::class)
            ->setConstructorArgs([$this->calculator, $this->converter, $this->configFixture])
            ->setMethods(['addTransactionToAccount'])
            ->getMock();

        $this->calculator->expects($this->once())->method('cashOutWithPromo');

        $manager->runTransaction([
            'acc_id' => 1, 'acc_type' => 'legal', 'amount' => 12, 'trans_type' => 'cash_out',
            'date' => new \DateTime()]);
    }

    // Method addTransactionToAccount() related

    /**
     * Test that method Account::setWeekId() gets called
     */
    public function testStartNewWeek()
    {
        /** @var Account|MockObject $account */
        $account = $this->createMock(Account::class);
        $account->expects($this->once())->method('setWeekToken');

        $this->manager->addTransactionToAccount($account, $this->rowFixture);
    }

    /**
     * Test that Account::addTransactionAmount() is called and Account::getWeekToken() is not called
     */
    public function testCallAddTransactionAmount()
    {
        $date = new \DateTime();
        $weekToken =  $this->manager->calculateWeekToken($date);

        /** @var Account|MockObject $account */
        $account = $this->createMock(Account::class);
        $account->method('getWeekToken')->willReturn($weekToken);
        $account->expects($this->exactly(0))->method('setWeekToken');
        $account->expects($this->once())->method('addTransactionAmount');

        $this->manager->addTransactionToAccount($account, $this->rowFixture);
    }

    // Method calculateWeekToken() related

    /**
     * Test that if the week number refers to the next year, the token will be based on the next year.
     * eg: For date 2018-12-31 the week is partial and is marked as the first week of the next year.
     * So instead of '2018 01' the token will be '2019 01'
     */
    public function testCalculateWeekIdYearUp()
    {
        $date = new \DateTime('2018-12-31');
        $token = $this->manager->calculateWeekToken($date);

        $this->assertSame('2019 01', $token);
    }

    /**
     * Test that if the week number refers to the previous year, the token will be based on the previous year.
     * eg: For date 2017-01-01 the week is partial and is marked as the last week of the previous year.
     * So instead of '2017 52' the token will be '2016 52'
     */
    public function testCalculateWeekIdYearDown()
    {
        $date = new \DateTime('2017-01-01');
        $token = $this->manager->calculateWeekToken($date);

        $this->assertSame('2016 52', $token);
    }

    /**
     * Test normal week token
     */
    public function testCalculateWeekNormal()
    {
        $date = new \DateTime('2018-10-02');
        $token = $this->manager->calculateWeekToken($date);

        $this->assertSame('2018 40', $token);
    }
}
