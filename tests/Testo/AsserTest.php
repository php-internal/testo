<?php

declare(strict_types=1);

namespace Tests\Testo;

use Testo\Assert;
use Testo\Attribute\ExpectException;
use Testo\Attribute\RetryPolicy;
use Testo\Attribute\Test;

final class AsserTest
{
    #[Test]
    public function simpleAssertions(): void
    {
        Assert::same(1, 1);
        Assert::null(null);
        Assert::notSame(42, '42');
        Assert::true(true);
        Assert::false(false);
        Assert::contains(1, [1, 2, 3]);
        Assert::contains(2, new \ArrayIterator([1, 2, 3]));
        Assert::instanceOf(\Exception::class, new \RuntimeException());
    }

    #[Test]
    public function failed(): void
    {
        Assert::same(1, 1, 'One is one btw.');
        Assert::null(null, 'Custom message on null assertion failure.');
        Assert::notSame(42, '42');
        Assert::same(0, null);
    }

    #[Test]
    #[ExpectException(Assert\State\AssertException::class)]
    public function leaks(): void
    {
        static $leak = null;
        $leak = new \stdClass();
        Assert::leaks(myStdClass: $leak);
    }

    #[Test]
    public function notLeaks(): void
    {
        $leak = new \stdClass();
        Assert::leaks(myStdClass: $leak);
    }

    #[Test]
    #[RetryPolicy(maxAttempts: 2)]
    public function flaky(): void
    {
        static $attempt = 0;
        ++$attempt;
        Assert::same($attempt, 2);
    }

    #[Test]
    public function risky(): void
    {
        // No assertions here
    }

    #[Test]
    public function expectException(): never
    {
        Assert::exception(\RuntimeException::class);

        throw new \RuntimeException('This is an expected exception.');
    }

    #[Test]
    public function expectExceptionObject(): never
    {
        $e = new \RuntimeException('This is an expected exception.');

        Assert::exception($e);

        throw $e;
    }

    #[Test]
    #[ExpectException(\RuntimeException::class)]
    public function expectExceptionAttribute(): never
    {
        throw new \RuntimeException('This is an expected exception.');
    }

    #[Test]
    public function throwIfConditionFalse(): void
    {
        Assert::throwIf(false, new \RuntimeException('Should not throw'), 'Condition is false');
    }

    #[Test]
    public function throwUnlessConditionTrue(): void
    {
        Assert::throwUnless(true, new \RuntimeException('Should not throw'), 'Condition is true');
    }

    #[Test]
    public function throwIfConditionFalseWithClass(): void
    {
        Assert::throwIf(false, \RuntimeException::class, 'Condition is false with class string');
    }

    #[Test]
    public function throwUnlessConditionTrueWithClass(): void
    {
        Assert::throwUnless(true, \RuntimeException::class, 'Condition is true with class string');
    }

    #[Test]
    #[ExpectException(\RuntimeException::class)]
    public function throwIfConditionTrue(): void
    {
        Assert::throwIf(true, new \RuntimeException('Condition is true, should throw'), 'Throw when true');
    }

    #[Test]
    #[ExpectException(\RuntimeException::class)]
    public function throwUnlessConditionFalse(): void
    {
        Assert::throwUnless(false, new \RuntimeException('Condition is false, should throw'), 'Throw when false');
    }

    #[Test]
    #[ExpectException(\InvalidArgumentException::class)]
    public function throwIfConditionTrueWithClass(): void
    {
        Assert::throwIf(true, \InvalidArgumentException::class, 'Throw when true with class string');
    }

    #[Test]
    #[ExpectException(\InvalidArgumentException::class)]
    public function throwUnlessConditionFalseWithClass(): void
    {
        Assert::throwUnless(false, \InvalidArgumentException::class, 'Throw when false with class string');
    }

    #[Test]
    #[ExpectException(\RuntimeException::class)]
    public function throwIfWithCustomExceptionMessage(): void
    {
        $exception = new \RuntimeException('Custom message from exception');
        Assert::throwIf(true, $exception, 'Custom assertion message');
    }
}
