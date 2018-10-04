<?php

namespace App\Tests;

use App\Application;
use App\FileHandler;
use App\Logger;
use App\ProcessFile;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ApplicationTest extends TestCase
{
    /** @var Application */
    private $application;
    /** @var FileHandler|MockObject */
    private $fileHandler;
    /** @var ProcessFile|MockObject */
    private $processFile;
    /** @var Logger */
    private $logger;

    protected function setUp()
    {
        parent::setUp();

        $this->fileHandler = $this->createMock(FileHandler::class);
        $this->processFile = $this->createMock(ProcessFile::class);
        $this->logger = $this->createMock(Logger::class);
        $this->application = new Application($this->fileHandler, $this->processFile, $this->logger);
    }

    public function testRunApplication()
    {
        $fp = \fopen(__DIR__.'/data-fixture.csv', 'r');

        // assert that the methods are called
        $this->fileHandler->expects($this->once())->method('check');
        $this->fileHandler->expects($this->once())->method('openFile')->willReturn($fp);
        $this->processFile->expects($this->exactly(3))->method('processFileRow');

        $called = 0;
        $this->application->run(function () use (&$called) {
            $called++;
            return false;
        });

        // assert that the callback is called
        $this->assertEquals(3, $called);
    }

    public function testRunInvalidRow()
    {
        $fp = \fopen(__DIR__.'/data-fixture.csv', 'r');
        $this->fileHandler->method('openFile')->willReturn($fp);
        // define a return value of the method stub
        $this->processFile->method('processFileRow')->willReturnMap([
            [['a'], 1, '', ['a']],
            [['b'], 2, '', null],
            [['c'], 3, '', ['b']]
        ]);

        $rows = [];
        $this->application->run(function ($row) use (&$rows) {
            $rows[] = $row;
        });

        $this->assertEquals(1, count($rows[0])); // valid row
        $this->assertNull($rows[1]); // invalid row
        $this->assertEquals(1, count($rows[2])); // valid row
    }
}
