<?php

declare(strict_types=1);

namespace Tests\Testo\Assert;

use Testo\Assert;
use Testo\Attribute\Test;

/**
 * Assertion examples.
 */
final class AssertLessThan
{
    #[Test]
    public function numbers(): void
    {
        Assert::lessThan(2, 1);
        Assert::lessThan(1.1, 1);
        Assert::lessThan('2', 1); //'2' coerces to 2
    }

    #[Test]
    public function strings(): void
    {
        Assert::lessThan("43", "42"); // numeric strings are cast to numbers
        Assert::lessThan("aaaa", "aaa"); // shorter string is smaller
        Assert::lessThan("beerz", "beers"); // byte-wise comparison (first different letters are compared as byte values)
    }

    #[Test]
    public function arrays(): void
    {
        Assert::lessThan([1, 2, 3], [1, 2]); // prefix rule: shorter wins
        Assert::lessThan([1, 4], [1, 3]); // first differing element decides: 3 < 4
        Assert::lessThan(['a' => 1, 'b' => 2, 'c' => 0], ['a' => 1, 'b' => 2]); // associative, same key order: shorter wins

        // comparison of associative arrays with different keys won't work and throws AssertException:
        // Assert::lessThan(['a' => 1, 'x' => 999], ['a' => 1, 'b' => 1]);
    }

    #[Test]
    public function datetimes(): void
    {
        $max  = new \DateTimeImmutable('2025-01-02 00:00:00+00:00');
        $actual = new \DateTimeImmutable('2025-01-01 00:00:00+00:00');
        Assert::lessThan($max, $actual); // earlier time is smaller
    }
}
