<?php

namespace App\Tests;

use App\CurrencyConverter;
use PHPUnit\Framework\TestCase;

class CurrencyConverterTest extends TestCase
{
    /** @var CurrencyConverter */
    private $converter;

    protected function setUp()
    {
        parent::setUp();

        $this->converter = new CurrencyConverter([
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
        ]);
    }

    public function testConvertFromTo()
    {
        $result = $this->converter->convert('JPY', 'EUR', 29);
        $this->assertSame(0.22, round($result, 2));
    }

    public function testRevertFromTo()
    {
        $result = $this->converter->revert('EUR', 'JPY', 0.22);
        $this->assertSame(28.5, round($result, 2));
    }

    public function testConvertSame()
    {
        $result = $this->converter->revert('EUR', 'EUR', 0.22);
        $this->assertSame(0.22, $result);
    }

    public function testRevertSame()
    {
        $result = $this->converter->revert('EUR', 'EUR', 0.22);
        $this->assertSame(0.22, $result);
    }
}
