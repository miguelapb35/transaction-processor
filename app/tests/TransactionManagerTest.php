<?php

namespace App\Tests;

use App\CommissionCalculator;
use App\TransactionManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TransactionManagerTest extends TestCase
{
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
                ]
            ],
            'precision' => [
                'EUR' => 2,
                'USD' => 2,
                'JPY' => 0,
            ]
        ],
    ];

    protected function setUp()
    {
        parent::setUp();

        $this->calculator = $this->createMock(CommissionCalculator::class);
        $this->manager = new TransactionManager($this->calculator, $this->configFixture);
    }

    public function testInvalidArgument()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->manager->runTransaction([]);
    }

    public function testRunTransactionCallsCashInMethod()
    {
        $this->calculator->expects($this->once())->method('cashIn')
            ->with(12, 'legal');

        $this->manager->runTransaction([
            'acc_id' => 1, 'acc_type' => 'legal', 'amount' => 12, 'trans_type' => 'cash_in']);
    }

    /**
     * Test non-public methods createAccount and addAccount indirectly
     */
    public function testAddAccount()
    {
        // assert initially empty
        $this->assertEquals(0, count($this->manager->getAccounts()));

        $this->manager->runTransaction([
            'acc_id' => 1, 'acc_type' => 'legal', 'amount' => 12, 'trans_type' => 'cash_in']);

        $this->assertEquals(1, count($this->manager->getAccounts()));

        return $this->manager;
    }

    /**
     * Test the private method getOrCreateAccount indirectly - test get account
     * @param TransactionManager $manager
     * @depends testAddAccount
     */
    public function testGetOrCreateAccount(TransactionManager $manager)
    {
        $manager->runTransaction([
            'acc_id' => 1, 'acc_type' => 'legal', 'amount' => 12, 'trans_type' => 'cash_in']);

        // account with an ID = 1 already exists, so do not add new one
        $this->assertEquals(1, count($manager->getAccounts()));
    }

    /**
     * Test the private method getOrCreateAccount indirectly - test create account
     * @param TransactionManager $manager
     * @depends testAddAccount
     */
    public function testGetOrCreateAccount2(TransactionManager $manager)
    {
        $manager->runTransaction([
            'acc_id' => 2, 'acc_type' => 'legal', 'amount' => 12, 'trans_type' => 'cash_in']);

        // account with an ID = 2 does not exists, so we should add new one
        $this->assertEquals(2, count($manager->getAccounts()));
    }

    // Method run() related

    public function testRoundEur()
    {
        /** @var TransactionManager|MockObject $manager */
        $manager = $this->getMockBuilder(TransactionManager::class)
            ->setConstructorArgs([$this->calculator, $this->configFixture])
            ->setMethods(['runTransaction'])
            ->getMock();
        $manager->method('runTransaction')->willReturn(121.232);
        $commission = $manager->run([
            'acc_id' => 2, 'acc_type' => 'legal', 'amount' => 12, 'trans_type' => 'cash_in', 'cur' => 'EUR']);

        $this->assertSame(121.24, $commission);

        return $manager;
    }

    /**
     * @depends testRoundEur
     * @param TransactionManager|MockObject $manager
     */
    public function testRoundJpy(MockObject $manager)
    {
        $manager->method('runTransaction')->willReturn(121.232);
        $commission = $manager->run([
            'acc_id' => 2, 'acc_type' => 'legal', 'amount' => 12, 'trans_type' => 'cash_in', 'cur' => 'JPY']);

        $this->assertSame(122.0, $commission);
    }
}
