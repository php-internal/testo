<?php

declare(strict_types=1);

namespace Testo\Test\Dto;

final class TestResult
{
    public function __construct(
        public readonly TestInfo $info,
        public readonly mixed $result,
        public readonly Status $status,
    ) {}

    public function with(
        ?Status $status = null,
    ): self {
        return new self(
            info: $this->info,
            result: $this->result,
            status: $status ?? $this->status,
        );
    }

    public function withResult(mixed $result): self
    {
        return new self(
            info: $this->info,
            result: $result,
            status: $this->status,
        );
    }
}
