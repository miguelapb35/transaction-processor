<?php

namespace App\Tests;

use App\FileHandler;
use App\ProcessFile;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProcessFileTest extends TestCase
{
    /** @var ProcessFile|MockObject */
    private $processFile;
    /** @var FileHandler|MockObject */
    private $checkFile;

    protected function setUp()
    {
        parent::setUp();

        $this->checkFile = $this->createMock(FileHandler::class);
        $this->processFile = $this->getMockBuilder(ProcessFile::class)
            ->setMethods(['openFile'])
            ->getMock();
    }

    // test input file rows validation
    public function testProcessValidRow()
    {
        $processFile = new ProcessFile();
        $row = $processFile->processFileRow(['2014-12-31','4','natural','cash_out','1200.00','EUR'], 1);

        // first row is valid, check types of each fields
        $this->assertInstanceOf(\DateTime::class, $row['date']);
        $this->assertSame(4, $row['acc_id']);
        $this->assertEquals('natural', $row['acc_type']);
        $this->assertEquals('cash_out', $row['trans_type']);
        $this->assertInternalType('float', $row['amount']);
        $this->assertEquals('EUR', $row['cur']);
    }

    public function testProcessInvalidRow()
    {
        $processFile = new ProcessFile();
        $invalidRow = '';
        $row = $processFile->processFileRow(['2014-12-31','4','blah','cash_out','1200.00','EUR'], 2, $invalidRow);

        $this->assertNull($row);

        // assert error field reported
        $this->assertEquals('row #2 field #3', $invalidRow);
    }
}
