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
    public function failWithNullMessage(): void
    {
        Assert::fail(null);
    }

    #[Test]
    public function failWithNoParameters(): void
    {
        Assert::fail();
    }

    #[Test]
    public function failWithAnyMessage(): void
    {
        Assert::fail('Any message works here');
    }

    #[Test]
    public function failWithExactMessage(): void
    {
        Assert::fail('Database connection failed');
    }

    #[Test]
    public function failWithWrongMessageShouldFail(): void
    {
        Assert::fail('Different message than expected');
    }

    #[Test]
    public function failButCaughtExceptionShouldBeRisky(): void
    {
        try {
            // Assert::fail() sets expectation and throws
            Assert::fail('This exception will be caught');
        } catch (Assert\State\AssertException $e) {
            // Catching the exception prevents the test from failing
            // But the expectation was set, so this should be marked as Risky
        }

        // Test completes successfully despite Assert::fail() being called
    }
}
