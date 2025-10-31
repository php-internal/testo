<?php

declare(strict_types=1);

namespace Tests\Testo\Assert;

use Testo\Assert;
use Testo\Attribute\Test;

/**
 * Assertion examples.
 */
final class AssertGreaterThan
{
    #[Test]
    public function numbers(): void
    {
        Assert::greaterThan(1, 2);
        Assert::greaterThan(1, 1.1);
        Assert::greaterThan('1', 2); //'1' coerces to 1
    }

    #[Test]
    public function strings(): void
    {
        Assert::greaterThan("42", "43"); //numeric strings are cast to numbers
        Assert::greaterThan("aaa", "aaaa"); // longer string is bigger
        Assert::greaterThan("beers", "beerz"); // byte-wise comparison (first different letters are compared as byte values)
    }

    #[Test]
    public function arrays(): void
    {
        Assert::greaterThan([1, 2], [1, 2, 3]); // prefix rule: longer wins
        Assert::greaterThan([1, 3], [1, 4]); // first differing element decides: 4 > 3
        Assert::greaterThan(['a' => 1, 'b' => 2], ['a' => 1, 'b' => 2, 'c' => 0]); // associative, same key order: longer wins

        // comparison of associative arrays with different keys won't work and throws AssertException:
        // Assert::greaterThan(['a' => 1, 'x' => 1], ['a' => 1, 'b' => 999]);
    }

    #[Test]
    public function datetimes(): void
    {
        $min  = new \DateTimeImmutable('2025-01-01 00:00:00+00:00');
        $actual = new \DateTimeImmutable('2025-01-02 00:00:00+00:00');
        Assert::greaterThan($min, $actual); // later time is bigger
    }
}
