<?php

declare(strict_types=1);

namespace Tests\Testo;

use Testo\Assert;
use Testo\Attribute\ExpectException;
use Testo\Attribute\Fail;
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
    #[Fail]
    public function failWithNullMessage(): void
    {
        Assert::fail(null);
    }

    #[Test]
    #[Fail]
    public function failWithNoParameters(): void
    {
        Assert::fail();
    }

    #[Test]
    #[Fail]
    public function failWithAnyMessage(): void
    {
        Assert::fail('Any message works here');
    }

    #[Test]
    #[Fail('Database connection failed')]
    public function failWithExactMessage(): void
    {
        Assert::fail('Database connection failed');
    }

    #[Test]
    #[Fail('Expected specific failure')]
    public function failWithWrongMessageShouldFail(): void
    {
        Assert::fail('Different message than expected');
    }

    #[Test]
    #[Fail]
    public function failButDoesNotFailShouldFail(): void
    {
        // This test doesn't call Assert::fail(), so it should fail
        Assert::true(true);
    }
}
