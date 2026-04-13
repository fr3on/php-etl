<?php

declare(strict_types=1);

namespace Fr3on\Etl;

use Fr3on\Etl\Error\ErrorCollection;

/**
 * @template TRow
 */
readonly class PipelineResult
{
    /**
     * @param ErrorCollection<TRow> $errors
     */
    public function __construct(
        public int $rowsProcessed,
        public int $rowsSkipped,
        public ErrorCollection $errors,
        public float $durationSeconds
    ) {}

    public function isSuccessful(): bool
    {
        return count($this->errors) === 0;
    }
}
