<?php

declare(strict_types=1);

namespace Testo\Test\Dto;

use Testo\Common\AttributedTrait;

final class TestResult
{
    use AttributedTrait;

    /**
     * @param array<non-empty-string, mixed> $attributes
     */
    public function __construct(
        public readonly TestInfo $info,
        public readonly Status $status,
        public readonly mixed $result = null,
        public readonly ?\Throwable $failure = null,
        public readonly array $attributes = [],
    ) {}

    public function with(
        ?Status $status = null,
    ): self {
        return new self(
            info: $this->info,
            status: $status ?? $this->status,
            result: $this->result,
            failure: $this->failure,
            attributes: $this->attributes,
        );
    }

    public function withResult(mixed $result): self
    {
        return $this->cloneWith('result', $result);
    }

    public function withFailure(?\Throwable $failure): self
    {
        return $this->cloneWith('failure', $failure);
    }
}
