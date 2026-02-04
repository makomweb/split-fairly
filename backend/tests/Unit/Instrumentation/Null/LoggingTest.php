<?php

declare(strict_types=1);

namespace App\Tests\Unit\Instrumentation\Null;

use App\Instrumentation\Null\Logging;
use PHPUnit\Framework\TestCase;

final class LoggingTest extends TestCase
{
    private Logging $logging;

    protected function setUp(): void
    {
        $this->logging = new Logging();
    }

    public function test_info_accepts_string_message(): void
    {
        $this->logging->info('Test message');
        $this->expectNotToPerformAssertions();
    }

    public function test_info_accepts_stringable_object(): void
    {
        $stringable = new class implements \Stringable {
            public function __toString(): string
            {
                return 'Stringable message';
            }
        };

        $this->logging->info($stringable);
        $this->expectNotToPerformAssertions();
    }

    public function test_exception_is_processed(): void
    {
        $exception = new \Exception('Test exception');
        $this->logging->exception($exception);
        $this->expectNotToPerformAssertions();
    }
}
