<?php

declare(strict_types=1);

namespace Tests\Testo\Assert;

use Testo\Assert;
use Testo\Attribute\Test;

/**
 * Assertion examples.
 */
final class AssertLessThanOrEqual
{
    #[Test]
    public function numbers(): void
    {
        Assert::lessThanOrEqual(2, 1);
        Assert::lessThanOrEqual(1.1, 1);
        Assert::lessThanOrEqual('2', 1); //'1' coerces to 1
        Assert::lessThanOrEqual('1', 1);
    }

    #[Test]
    public function strings(): void
    {
        Assert::lessThanOrEqual("42", "42"); // numeric strings are cast to numbers
        Assert::lessThanOrEqual("43", "42");
        Assert::lessThanOrEqual("aaaa", "aaa"); // shorter string is smaller
        Assert::lessThanOrEqual("aaa", "aaa");
        Assert::lessThanOrEqual("beerz", "beers"); // byte-wise comparison (first different letters are compared as byte values)
    }

    #[Test]
    public function arrays(): void
    {
        Assert::lessThanOrEqual([1, 2], [1, 2]); // same arrays considered equal
        Assert::lessThanOrEqual([1, 2, 3], [1, 2]); // prefix rule: shorter wins
        Assert::lessThanOrEqual([1, 4], [1, 3]); // first differing element decides: 3 < 4
        Assert::lessThanOrEqual(['a' => 1, 'b' => 2, 'c' => 0], ['a' => 1, 'b' => 2]); // associative, same key order: shorter wins
        Assert::lessThanOrEqual(['b' => 2, 'a' => 1], ['a' => 1, 'b' => 2]); // associative arrays, same elements, different order - considered equal

        // comparison of associative arrays with different keys won't work and throws AssertException:
        // Assert::lessThanOrEqual(['a' => 1, 'x' => 999], ['a' => 1, 'b' => 1]);
    }

    #[Test]
    public function datetimes(): void
    {
        $max  = new \DateTimeImmutable('2025-01-02 00:00:00+00:00');
        $actual1 = new \DateTimeImmutable('2025-01-01 00:00:00+00:00');
        $actual2 = new \DateTimeImmutable('2025-01-02 00:00:00+00:00');
        Assert::lessThanOrEqual($max, $actual1); // earlier time is smaller
        Assert::lessThanOrEqual($max, $actual2); // same times are equal
    }
}
