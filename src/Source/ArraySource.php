<?php

declare(strict_types=1);

namespace Fr3on\Etl\Source;

use Generator;

/**
 * @template TRow
 * @implements SourceInterface<TRow>
 */
class ArraySource implements SourceInterface
{
    /**
     * @param iterable<int, TRow> $data
     */
    public function __construct(private readonly iterable $data) {}

    /**
     * @return Generator<int, TRow>
     */
    public function getIterator(): Generator
    {
        foreach ($this->data as $row) {
            yield $row;
        }
    }
}
