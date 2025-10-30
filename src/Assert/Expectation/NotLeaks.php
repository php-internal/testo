<?php

declare(strict_types=1);

namespace Testo\Assert\Expectation;

use Testo\Assert\State\AssertException;
use Testo\Assert\State\Success;
use Testo\Assert\TestState;
use Testo\Test\Dto\Status;
use Testo\Test\Dto\TestResult;

/**
 * Asserts that no memory leaks occurred for the given objects.
 *
 * @see Assert::leaks()
 */
final class NotLeaks
{
    private readonly \WeakMap $map;

    public function __construct(
        private readonly string $message = '',
        object ...$objects,
    ) {
        $this->map = new \WeakMap();
        foreach ($objects as $key => $object) {
            $name = \is_string($key) ? $key : true;
            $this->map->offsetSet($object, $name);
        }
    }

    public function __invoke(TestResult $result, TestState $state): TestResult
    {
        if ($this->map->count() === 0) {
            $state->history[] = new Success('No objects leaked', $this->message);
            return $result;
        }

        $e = AssertException::leaks($this->map, $this->message);
        $state->history[] = $e;

        return $result->with(status: Status::Failed)->withFailure($e);
    }
}
