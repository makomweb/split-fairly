<?php

declare(strict_types=1);

namespace App\Tests\Unit\SplitFairly\DTO;

use App\SplitFairly\DTO\Price;
use PHPUnit\Framework\TestCase;

final class PriceTest extends TestCase
{
    public function test_can_create_price(): void
    {
        $price = new Price(value: 10.50, currency: 'EUR');

        $this->assertSame(10.50, $price->value);
        $this->assertSame('EUR', $price->currency);
    }

    public function test_price_to_string(): void
    {
        $price = new Price(value: 10.50, currency: 'EUR');

        $this->assertSame('10.50 EUR', (string) $price);
    }

    public function test_price_formats_correctly(): void
    {
        $price = new Price(value: 10.5, currency: 'USD');

        $this->assertSame('10.50 USD', (string) $price);
    }

    public function test_price_with_zero_value(): void
    {
        $price = new Price(value: 0.0, currency: 'EUR');

        $this->assertSame('0.00 EUR', (string) $price);
    }

    public function test_rejects_empty_currency(): void
    {
        $this->expectException(\App\Invariant\InvariantException::class);

        new Price(value: 10.0, currency: '');
    }
}
