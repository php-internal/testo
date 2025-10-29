<?php

declare(strict_types=1);

namespace Tests\Testo\Assert;

use Testo\Assert;
use Testo\Attribute\Test;

/**
 * Assertion examples.
 */
final class AssertGreaterThanOrEqual
{
    #[Test]
    public function numbers(): void
    {
        Assert::greaterThanOrEqual(1, 2);
        Assert::greaterThanOrEqual(1, 1.1);
        Assert::greaterThanOrEqual('1', 2); //'1' coerces to 1
        Assert::greaterThanOrEqual('1', 1);
    }

    #[Test]
    public function strings(): void
    {
        Assert::greaterThanOrEqual("42", "42"); //numeric strings are cast to numbers
        Assert::greaterThanOrEqual("42", "43");
        Assert::greaterThanOrEqual("aaa", "aaaa"); // longer string is bigger
        Assert::greaterThanOrEqual("aaa", "aaa");
        Assert::greaterThanOrEqual("beers", "beerz"); // byte-wise comparison (first different letters are compared as byte values)
    }

    #[Test]
    public function arrays(): void
    {
        Assert::greaterThanOrEqual([1, 2], [1, 2]); // same arrays considered equal
        Assert::greaterThanOrEqual([1, 2], [1, 2, 3]); // prefix rule: longer wins
        Assert::greaterThanOrEqual([1, 3], [1, 4]); // first differing element decides: 4 > 3
        Assert::greaterThanOrEqual(['a' => 1, 'b' => 2], ['a' => 1, 'b' => 2, 'c' => 0]); // associative, same key order: longer wins
        Assert::greaterThanOrEqual(['b' => 2, 'a' => 1], ['a' => 1, 'b' => 2]); // associative arrays, same elements, different order - considered equal

        // comparison of associative arrays with different keys won't work and throws AssertException:
        // Assert::greaterThanOrEqual(['a' => 1, 'x' => 1], ['a' => 1, 'b' => 999]);
    }

    #[Test]
    public function datetimes(): void
    {
        $min  = new \DateTimeImmutable('2025-01-01 00:00:00+00:00');
        $actual1 = new \DateTimeImmutable('2025-01-02 00:00:00+00:00');
        $actual2 = new \DateTimeImmutable('2025-01-01 00:00:00+00:00');
        Assert::greaterThanOrEqual($min, $actual1); // later time is bigger
        Assert::greaterThanOrEqual($min, $actual2); // same time are equal
    }
}
