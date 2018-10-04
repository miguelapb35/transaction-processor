<?php

namespace App\Tests;

use App\Account;
use PHPUnit\Framework\TestCase;

class AccountTest extends TestCase
{
    /** @var Account */
    private $account;

    protected function setUp()
    {
        parent::setUp();

        $this->account = new Account(1, 'legal');
    }

    public function testInvalidConstructorArg()
    {
        $this->expectException(\InvalidArgumentException::class);
        new Account(1, 'public'); // account of type "public" does not exist
    }

    public function testValidateTypeException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->account->validateType('public'); // "public" is not valid type
    }
}
