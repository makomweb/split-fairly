<?php

declare(strict_types=1);

namespace App\Tests\Unit\Instrumentation\Null;

use App\Instrumentation\Null\Span;
use PHPUnit\Framework\TestCase;

final class SpanTest extends TestCase
{
    private Span $span;

    protected function setUp(): void
    {
        $this->span = new Span();
    }

    public function test_open_is_executed(): void
    {
        $this->span->open();
        $this->expectNotToPerformAssertions();
    }

    public function test_record_exception_processes_exception(): void
    {
        $exception = new \Exception('Test exception');
        $this->span->recordException('test', $exception);
        $this->expectNotToPerformAssertions();
    }

    public function test_close_is_executed(): void
    {
        $this->span->close();
        $this->expectNotToPerformAssertions();
    }
}
