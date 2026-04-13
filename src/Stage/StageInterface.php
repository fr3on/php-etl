<?php

declare(strict_types=1);

namespace Fr3on\Etl\Stage;

/**
 * @template TIn
 * @template TOut
 */
interface StageInterface
{
    /**
     * @param TIn $row
     * @return TOut
     */
    public function process(mixed $row): mixed;
}
