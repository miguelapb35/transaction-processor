<?php

namespace App\Tests;

use App\Application;
use App\Factories\TransactionManagerFactory;
use App\Promotion\TransactionManager;
use App\Utils;
use PHPUnit\Framework\TestCase;

class AppIntegrationTest extends TestCase
{
    /** @var TransactionManager */
    private $transactionManager;
    /** @var Application */
    private $application;
    private $config;

    protected function setUp()
    {
        parent::setUp();

        $testDir = __DIR__.'/test-files/';
        $config = $this->config = require_once($testDir.'config.php');

        // initialize objects
        $this->transactionManager = TransactionManagerFactory::create($config, 'promotion');
        $this->application = Application::newInstance($testDir.'input.csv', $config);
    }

    public function testApplication()
    {
        $rows = [];
        $this->application->run(function ($row) use (&$rows) {
            $rows[] = Utils::formatOutput($this->transactionManager->run($row), $row, $this->config);
        });

        // assert empty row disregarded
        $this->assertEquals(13, count($rows));

        $this->assertSame('0.60', $rows[0]);
        $this->assertSame('3.00', $rows[1]);
        $this->assertSame('0.00', $rows[2]);
        $this->assertSame('0.06', $rows[3]);
        $this->assertSame('0.90', $rows[4]);
        $this->assertSame('0', $rows[5]);
        $this->assertSame('0.70', $rows[6]);
        $this->assertSame('0.30', $rows[7]);
        $this->assertSame('0.30', $rows[8]);
        $this->assertSame('5.00', $rows[9]);
        $this->assertSame('0.00', $rows[10]);
        $this->assertSame('0.00', $rows[11]);
        $this->assertSame('8612', $rows[12]);
    }
}
