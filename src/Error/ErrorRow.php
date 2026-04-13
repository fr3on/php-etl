<?php

declare(strict_types=1);

namespace Fr3on\Etl\Error;

use Throwable;

/**
 * @template TRow
 */
readonly class ErrorRow
{
    /**
     * @param TRow $row
     */
    public function __construct(
        public mixed $row,
        public Throwable $exception,
        public string $stageName,
        public int $rowNumber
    ) {}
}
