<?php

declare(strict_types=1);

namespace Fr3on\Etl\Stage;

/**
 * @template TIn
 * @implements StageInterface<array<int, TIn>, iterable<TIn>>
 */
class UnbatchStage implements StageInterface
{
    /**
     * @param array<int, TIn> $row
     * @return iterable<TIn>
     */
    public function process(mixed $row): iterable
    {
        return $row;
    }
}
