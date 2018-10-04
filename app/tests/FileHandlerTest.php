<?php

namespace App\Tests;

use App\FileHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FileHandlerTest extends TestCase
{
    /** @var FileHandler|MockObject */
    private $processFile;

    /** @var string File name fixture */
    private $fileName = 'some.csv';

    protected function setUp()
    {
        parent::setUp();

        $this->processFile = $this->getMockBuilder(FileHandler::class)
            ->setConstructorArgs([$this->fileName])
            ->setMethods(['validateFileName', 'checkFileReadable'])
            ->getMock();
    }

    // Test method validateFileName()

    public function testIsInputArgumentEmpty()
    {
        $this->expectException(\InvalidArgumentException::class);

        $processFile = new FileHandler('');
        $processFile->validateFileName();
    }

    public function testInputArgumentValidSymbols()
    {
        $this->expectException(\InvalidArgumentException::class);
        $processFile = new FileHandler('in;put.csv');
        $processFile->validateFileName();
    }

    public function testProcessInputArgumentMethodCalled()
    {
        $this->processFile->expects($this->once())->method('validateFileName');
        $this->processFile->check();
    }

    // Test method checkFileReadable()

    public function testCheckFileReadableCalled()
    {
        $this->processFile->expects($this->once())->method('checkFileReadable');
        $this->processFile->check();
    }

    /**
     * The file does not exists or is not readable
     */
    public function testProblemsWithFile()
    {
        $this->processFile->method('checkFileReadable')->willThrowException(new \InvalidArgumentException());
        $this->expectException(\InvalidArgumentException::class);
        $this->processFile->check();
    }
}
